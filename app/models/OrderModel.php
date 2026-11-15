<?php
declare(strict_types=1);

class OrderModel
{
    public static function checkout(array $itemsQty, array $meta, int $userId): array
    {
        $cleanItems = [];
        foreach ($itemsQty as $pid => $qtyVal) {
            $id = (int)$pid;
            $qty = (float)$qtyVal;
            if ($id > 0 && $qty > 0) {
                $cleanItems[$id] = round($qty, 2);
            }
        }

        if (!$cleanItems) {
            throw new InvalidArgumentException('Pilih minimal 1 menu untuk membuat pesanan.');
        }

        $allowedTypes = ['Dine In', 'Take Away'];
        $orderType = in_array($meta['order_type'] ?? '', $allowedTypes, true)
            ? $meta['order_type']
            : 'Dine In';

        $allowedPayments = ['Tunai', 'QRIS', 'Transfer', 'E-Wallet'];
        $paymentMethod = in_array($meta['payment_method'] ?? '', $allowedPayments, true)
            ? $meta['payment_method']
            : 'Tunai';

        $status = ($meta['order_status'] ?? 'PAID') === 'OPEN' ? 'OPEN' : 'PAID';

        $customerName = trim((string)($meta['customer_name'] ?? ''));
        if ($customerName === '') {
            $customerName = 'Pelanggan Umum';
        }

        $tableNo = trim((string)($meta['table_no'] ?? ''));
        if ($tableNo === '') {
            $tableNo = $orderType === 'Take Away' ? 'Bungkus' : 'Meja 1';
        }

        $discountInput = max(0.0, (float)($meta['discount'] ?? 0));
        $cashPaidInput = max(0.0, (float)($meta['cash_paid'] ?? 0));
        $note = trim((string)($meta['note'] ?? ''));

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $orderCode = self::generateOrderCode($pdo);
            $subtotal = 0.0;
            $resolvedLines = [];

            $lockStmt = $pdo->prepare(
                "SELECT id, name, unit, sell_price, stock, is_active FROM products WHERE id = ? FOR UPDATE"
            );
            $updStockStmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $movStmt = $pdo->prepare(
                "INSERT INTO stock_movements (product_id, type, quantity, stock_before, stock_after, note, user_id)
                 VALUES (?, 'OUT', ?, ?, ?, ?, ?)"
            );

            foreach ($cleanItems as $pid => $qty) {
                $lockStmt->execute([$pid]);
                $prod = $lockStmt->fetch();

                if (!$prod || (int)$prod['is_active'] !== 1) {
                    throw new RuntimeException('Salah satu menu tidak ditemukan atau sedang nonaktif.');
                }

                $before = (float)$prod['stock'];
                if ($before < $qty) {
                    throw new RuntimeException(
                        "Stok porsi \"{$prod['name']}\" tidak mencukupi (tersedia " . formatQty($before) . " {$prod['unit']})."
                    );
                }

                $after = $before - $qty;
                $unitPrice = (float)$prod['sell_price'];
                $lineSubtotal = round($unitPrice * $qty, 2);
                $subtotal += $lineSubtotal;

                $updStockStmt->execute([$after, $pid]);
                $movStmt->execute([
                    $pid,
                    $qty,
                    $before,
                    $after,
                    "Pesanan #{$orderCode} ({$tableNo})",
                    $userId,
                ]);

                $resolvedLines[] = [
                    'product_id' => $pid,
                    'product_name' => $prod['name'],
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = min($discountInput, $subtotal);
            $totalAmount = max(0.0, round($subtotal - $discount, 2));

            if ($status === 'OPEN') {
                $cashPaid = 0.0;
                $changeAmount = 0.0;
            } elseif ($paymentMethod === 'Tunai') {
                if ($cashPaidInput > 0 && $cashPaidInput < $totalAmount) {
                    throw new InvalidArgumentException(
                        'Uang tunai diterima kurang dari total pembayaran (' . formatRp($totalAmount) . ').'
                    );
                }
                $cashPaid = $cashPaidInput > 0 ? $cashPaidInput : $totalAmount;
                $changeAmount = max(0.0, round($cashPaid - $totalAmount, 2));
            } else {
                $cashPaid = $totalAmount;
                $changeAmount = 0.0;
            }

            $orderStmt = $pdo->prepare(
                "INSERT INTO orders
                (order_code, customer_name, table_no, order_type, payment_method, subtotal, discount, total_amount, cash_paid, change_amount, status, note, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $orderStmt->execute([
                $orderCode,
                mb_substr($customerName, 0, 120),
                mb_substr($tableNo, 0, 30),
                $orderType,
                $paymentMethod,
                $subtotal,
                $discount,
                $totalAmount,
                $cashPaid,
                $changeAmount,
                $status,
                $note !== '' ? mb_substr($note, 0, 255) : null,
                $userId,
            ]);

            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            foreach ($resolvedLines as $line) {
                $itemStmt->execute([
                    $orderId,
                    $line['product_id'],
                    $line['product_name'],
                    $line['unit_price'],
                    $line['quantity'],
                    $line['subtotal'],
                ]);
            }

            $auditStmt = $pdo->prepare(
                "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address)
                 VALUES (?, ?, 'orders', ?, NULL, ?, ?)"
            );
            $auditStmt->execute([
                $userId,
                $status === 'OPEN' ? 'ORDER_OPEN_BILL' : 'ORDER_CREATE',
                $orderId,
                json_encode([
                    'order_code' => $orderCode,
                    'table_no' => $tableNo,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'status' => $status,
                ], JSON_UNESCAPED_UNICODE),
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $pdo->commit();

            return [
                'id' => $orderId,
                'order_code' => $orderCode,
                'table_no' => $tableNo,
                'total_amount' => $totalAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'status' => $status,
            ];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function payOpenOrder(int $orderId, string $paymentMethod, float $cashPaidInput, int $userId): array
    {
        $allowedPayments = ['Tunai', 'QRIS', 'Transfer', 'E-Wallet'];
        $method = in_array($paymentMethod, $allowedPayments, true) ? $paymentMethod : 'Tunai';

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new RuntimeException('Pesanan tidak ditemukan.');
            }
            if ($order['status'] !== 'OPEN') {
                throw new RuntimeException('Pesanan ini sudah lunas atau dibatalkan.');
            }

            $total = (float)$order['total_amount'];
            if ($method === 'Tunai') {
                if ($cashPaidInput > 0 && $cashPaidInput < $total) {
                    throw new InvalidArgumentException('Uang tunai kurang dari total tagihan (' . formatRp($total) . ').');
                }
                $cashPaid = $cashPaidInput > 0 ? $cashPaidInput : $total;
                $change = max(0.0, round($cashPaid - $total, 2));
            } else {
                $cashPaid = $total;
                $change = 0.0;
            }

            $upd = $pdo->prepare(
                "UPDATE orders SET status = 'PAID', payment_method = ?, cash_paid = ?, change_amount = ? WHERE id = ?"
            );
            $upd->execute([$method, $cashPaid, $change, $orderId]);

            $audit = $pdo->prepare(
                "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address)
                 VALUES (?, 'ORDER_PAY', 'orders', ?, ?, ?, ?)"
            );
            $audit->execute([
                $userId,
                $orderId,
                json_encode(['status' => 'OPEN'], JSON_UNESCAPED_UNICODE),
                json_encode(['status' => 'PAID', 'payment_method' => $method, 'cash_paid' => $cashPaid], JSON_UNESCAPED_UNICODE),
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $pdo->commit();
            return [
                'id' => $orderId,
                'order_code' => $order['order_code'],
                'total_amount' => $total,
                'change_amount' => $change,
                'payment_method' => $method,
            ];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function voidOrder(int $orderId, int $userId): void
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new RuntimeException('Pesanan tidak ditemukan.');
            }
            if ($order['status'] === 'VOID') {
                throw new RuntimeException('Pesanan ini sudah dibatalkan sebelumnya.');
            }

            $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll();

            $lockProd = $pdo->prepare("SELECT id, stock FROM products WHERE id = ? FOR UPDATE");
            $updProd = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $movStmt = $pdo->prepare(
                "INSERT INTO stock_movements (product_id, type, quantity, stock_before, stock_after, note, user_id)
                 VALUES (?, 'IN', ?, ?, ?, ?, ?)"
            );

            foreach ($items as $it) {
                if (empty($it['product_id'])) {
                    continue;
                }
                $pid = (int)$it['product_id'];
                $qty = (float)$it['quantity'];
                $lockProd->execute([$pid]);
                $prod = $lockProd->fetch();
                if ($prod) {
                    $before = (float)$prod['stock'];
                    $after = $before + $qty;
                    $updProd->execute([$after, $pid]);
                    $movStmt->execute([
                        $pid,
                        $qty,
                        $before,
                        $after,
                        "Void Nota #{$order['order_code']}",
                        $userId,
                    ]);
                }
            }

            $updOrder = $pdo->prepare("UPDATE orders SET status = 'VOID' WHERE id = ?");
            $updOrder->execute([$orderId]);

            $audit = $pdo->prepare(
                "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address)
                 VALUES (?, 'ORDER_VOID', 'orders', ?, ?, ?, ?)"
            );
            $audit->execute([
                $userId,
                $orderId,
                json_encode(['status' => $order['status'], 'total_amount' => $order['total_amount']], JSON_UNESCAPED_UNICODE),
                json_encode(['status' => 'VOID'], JSON_UNESCAPED_UNICODE),
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function generateOrderCode(PDO $pdo): string
    {
        $prefix = 'WRK-' . date('Ymd') . '-';
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_code LIKE ?");
        $stmt->execute([$prefix . '%']);
        $seq = (int)$stmt->fetchColumn() + 1;

        for ($i = 0; $i < 10; $i++) {
            $code = $prefix . str_pad((string)($seq + $i), 3, '0', STR_PAD_LEFT);
            $check = $pdo->prepare("SELECT id FROM orders WHERE order_code = ?");
            $check->execute([$code]);
            if (!$check->fetch()) {
                return $code;
            }
        }
        return $prefix . strtoupper(bin2hex(random_bytes(2)));
    }

    public static function recentOrders(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        $orders = db()->query(
            "SELECT o.*, u.name AS cashier_name,
                    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
                    (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) AS total_qty
             FROM orders o
             JOIN users u ON u.id = o.user_id
             ORDER BY FIELD(o.status, 'OPEN', 'PAID', 'VOID'), o.created_at DESC, o.id DESC
             LIMIT {$limit}"
        )->fetchAll();

        if (!$orders) {
            return [];
        }

        $ids = array_map(static fn(array $o): int => (int)$o['id'], $orders);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT * FROM order_items WHERE order_id IN ({$placeholders}) ORDER BY id ASC");
        $stmt->execute($ids);
        $itemsByOrder = [];
        foreach ($stmt->fetchAll() as $item) {
            $itemsByOrder[(int)$item['order_id']][] = $item;
        }

        foreach ($orders as &$o) {
            $o['items'] = $itemsByOrder[(int)$o['id']] ?? [];
        }
        return $orders;
    }

    public static function openBills(): array
    {
        $orders = db()->query(
            "SELECT o.*, u.name AS cashier_name
             FROM orders o
             JOIN users u ON u.id = o.user_id
             WHERE o.status = 'OPEN'
             ORDER BY o.created_at DESC, o.id DESC"
        )->fetchAll();

        if (!$orders) {
            return [];
        }

        $ids = array_map(static fn(array $o): int => (int)$o['id'], $orders);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT * FROM order_items WHERE order_id IN ({$placeholders}) ORDER BY id ASC");
        $stmt->execute($ids);
        $byOrder = [];
        foreach ($stmt->fetchAll() as $it) {
            $byOrder[(int)$it['order_id']][] = $it;
        }
        foreach ($orders as &$o) {
            $o['items'] = $byOrder[(int)$o['id']] ?? [];
        }
        return $orders;
    }

    public static function findOrder(int $id): ?array
    {
        $stmt = db()->prepare(
            "SELECT o.*, u.name AS cashier_name
             FROM orders o
             JOIN users u ON u.id = o.user_id
             WHERE o.id = ?"
        );
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $itemStmt = db()->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
        $itemStmt->execute([$id]);
        $order['items'] = $itemStmt->fetchAll();

        return $order;
    }

    public static function todaySummary(): array
    {
        $pdo = db();
        return [
            'orders_today' => (int)$pdo->query(
                "SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'VOID'"
            )->fetchColumn(),
            'revenue_today' => (float)$pdo->query(
                "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'PAID'"
            )->fetchColumn(),
            'open_count' => (int)$pdo->query(
                "SELECT COUNT(*) FROM orders WHERE status = 'OPEN'"
            )->fetchColumn(),
            'expenses_today' => (float)$pdo->query(
                "SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date = CURDATE()"
            )->fetchColumn(),
            'expenses_total' => (float)$pdo->query(
                "SELECT COALESCE(SUM(amount), 0) FROM expenses"
            )->fetchColumn(),
            'orders_total' => (int)$pdo->query(
                "SELECT COUNT(*) FROM orders WHERE status != 'VOID'"
            )->fetchColumn(),
            'revenue_total' => (float)$pdo->query(
                "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'VOID'"
            )->fetchColumn(),
            'qty_total' => (float)$pdo->query(
                "SELECT COALESCE(SUM(oi.quantity), 0)
                 FROM order_items oi
                 JOIN orders o ON o.id = oi.order_id
                 WHERE o.status != 'VOID'"
            )->fetchColumn(),
            'popular_ids' => array_map(
                'intval',
                $pdo->query(
                    "SELECT oi.product_id
                     FROM order_items oi
                     JOIN orders o ON o.id = oi.order_id
                     WHERE oi.product_id IS NOT NULL AND o.status != 'VOID'
                     GROUP BY oi.product_id
                     ORDER BY SUM(oi.quantity) DESC
                     LIMIT 6"
                )->fetchAll(PDO::FETCH_COLUMN)
            ),
        ];
    }

    public static function todayOrders(): array
    {
        $orders = db()->query(
            "SELECT o.*, u.name AS cashier_name
             FROM orders o
             JOIN users u ON u.id = o.user_id
             WHERE DATE(o.created_at) = CURDATE()
             ORDER BY o.created_at DESC, o.id DESC"
        )->fetchAll();

        if (!$orders) {
            return [];
        }

        $ids = array_map(static fn(array $o): int => (int)$o['id'], $orders);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT * FROM order_items WHERE order_id IN ({$placeholders}) ORDER BY id ASC");
        $stmt->execute($ids);
        $byOrder = [];
        foreach ($stmt->fetchAll() as $it) {
            $byOrder[(int)$it['order_id']][] = $it;
        }
        foreach ($orders as &$o) {
            $o['items'] = $byOrder[(int)$o['id']] ?? [];
        }
        return $orders;
    }

    public static function dailySalesReport(?string $start = null, ?string $end = null): array
    {
        $whereOrd = "WHERE o.status != 'VOID'";
        $whereExp = "WHERE 1=1";
        $paramsOrd = [];
        $paramsExp = [];

        if ($start) {
            $whereOrd .= " AND DATE(o.created_at) >= ?";
            $whereExp .= " AND expense_date >= ?";
            $paramsOrd[] = $start;
            $paramsExp[] = $start;
        }
        if ($end) {
            $whereOrd .= " AND DATE(o.created_at) <= ?";
            $whereExp .= " AND expense_date <= ?";
            $paramsOrd[] = $end;
            $paramsExp[] = $end;
        }

        $ordStmt = db()->prepare(
            "SELECT DATE(o.created_at) AS sale_date,
                    COUNT(DISTINCT o.id) AS order_count,
                    COALESCE(SUM(oi.quantity), 0) AS qty_sold,
                    COALESCE(SUM(o.total_amount), 0) AS revenue
             FROM orders o
             LEFT JOIN (
                 SELECT order_id, SUM(quantity) AS quantity FROM order_items GROUP BY order_id
             ) oi ON oi.order_id = o.id
             {$whereOrd}
             GROUP BY DATE(o.created_at)
             ORDER BY sale_date DESC"
        );
        $ordStmt->execute($paramsOrd);
        $daily = $ordStmt->fetchAll();

        $expStmt = db()->prepare(
            "SELECT expense_date, COALESCE(SUM(amount), 0) AS total_exp
             FROM expenses
             {$whereExp}
             GROUP BY expense_date"
        );
        $expStmt->execute($paramsExp);
        $expMap = [];
        foreach ($expStmt->fetchAll() as $er) {
            $expMap[$er['expense_date']] = (float)$er['total_exp'];
        }

        foreach ($daily as &$d) {
            $exp = $expMap[$d['sale_date']] ?? 0.0;
            $d['expense'] = $exp;
            $d['net_cash'] = (float)$d['revenue'] - $exp;
        }
        return $daily;
    }

    public static function menuSalesReport(?string $start = null, ?string $end = null): array
    {
        $where = "WHERE o.status != 'VOID'";
        $params = [];
        if ($start) {
            $where .= " AND DATE(o.created_at) >= ?";
            $params[] = $start;
        }
        if ($end) {
            $where .= " AND DATE(o.created_at) <= ?";
            $params[] = $end;
        }

        $stmt = db()->prepare(
            "SELECT COALESCE(p.sku, '-') AS sku,
                    oi.product_name,
                    SUM(oi.quantity) AS qty_sold,
                    COUNT(DISTINCT oi.order_id) AS tx_count,
                    SUM(oi.subtotal) AS omzet,
                    SUM(oi.quantity * COALESCE(p.cost_price, oi.unit_price * 0.65)) AS cost_total
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             LEFT JOIN products p ON p.id = oi.product_id
             {$where}
             GROUP BY oi.product_id, oi.product_name, p.sku
             ORDER BY omzet DESC, qty_sold DESC"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['est_profit'] = (float)$r['omzet'] - (float)$r['cost_total'];
        }
        return $rows;
    }

    public static function recentAuditLogs(int $limit = 100): array
    {
        $limit = max(1, min(300, $limit));
        return db()->query(
            "SELECT a.*, COALESCE(u.name, 'System') AS user_name
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT {$limit}"
        )->fetchAll();
    }
}
