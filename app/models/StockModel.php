<?php
declare(strict_types=1);

class StockModel
{
    public static function products(?string $search = null): array
    {
        $sql = "SELECT p.*, c.name AS category_name, s.name AS supplier_name
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN suppliers s ON s.id = p.supplier_id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $params = ["%{$search}%", "%{$search}%"];
        }

        $sql .= " ORDER BY p.is_active DESC, p.name ASC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findProduct(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function categories(): array
    {
        return db()->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    }

    public static function suppliers(): array
    {
        return db()->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();
    }

    public static function createProduct(array $data, int $userId): void
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO products
                (category_id, supplier_id, sku, name, unit, cost_price, sell_price, stock, min_stock)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $data['category_id'] ?: null,
                $data['supplier_id'] ?: null,
                $data['sku'],
                $data['name'],
                $data['unit'],
                $data['cost_price'],
                $data['sell_price'],
                $data['stock'],
                $data['min_stock']
            ]);

            $productId = (int)$pdo->lastInsertId();

            if ((float)$data['stock'] > 0) {
                $movement = $pdo->prepare(
                    "INSERT INTO stock_movements
                    (product_id, type, quantity, stock_before, stock_after, note, user_id)
                    VALUES (?, 'IN', ?, 0, ?, 'Stok awal produk', ?)"
                );
                $movement->execute([
                    $productId,
                    $data['stock'],
                    $data['stock'],
                    $userId
                ]);
            }

            self::audit($userId, 'CREATE', 'products', $productId, null, $data);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateProduct(int $id, array $data, int $userId): void
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $old = self::findProduct($id);
            if (!$old) {
                throw new RuntimeException('Produk tidak ditemukan.');
            }

            // Stok tidak diedit melalui CRUD. Perubahan stok wajib melalui Mutasi Stok.
            $stmt = $pdo->prepare(
                "UPDATE products SET
                    category_id = ?,
                    supplier_id = ?,
                    sku = ?,
                    name = ?,
                    unit = ?,
                    cost_price = ?,
                    sell_price = ?,
                    min_stock = ?,
                    is_active = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $data['category_id'] ?: null,
                $data['supplier_id'] ?: null,
                $data['sku'],
                $data['name'],
                $data['unit'],
                $data['cost_price'],
                $data['sell_price'],
                $data['min_stock'],
                $data['is_active'],
                $id
            ]);

            self::audit($userId, 'UPDATE', 'products', $id, $old, $data);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function deleteProduct(int $id, int $userId): void
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $old = self::findProduct($id);
            if (!$old) {
                throw new RuntimeException('Produk tidak ditemukan.');
            }

            // Soft delete supaya histori transaksi tetap aman.
            $stmt = $pdo->prepare(
                "UPDATE products SET is_active = 0 WHERE id = ?"
            );
            $stmt->execute([$id]);

            self::audit(
                $userId,
                'DELETE',
                'products',
                $id,
                $old,
                ['is_active' => 0]
            );

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function restoreProduct(int $id, int $userId): void
    {
        $stmt = db()->prepare(
            "UPDATE products SET is_active = 1 WHERE id = ?"
        );
        $stmt->execute([$id]);

        self::audit(
            $userId,
            'RESTORE',
            'products',
            $id,
            ['is_active' => 0],
            ['is_active' => 1]
        );
    }

    private static function audit(
        int $userId,
        string $action,
        string $table,
        int $recordId,
        ?array $oldData,
        ?array $newData
    ): void {
        $stmt = db()->prepare(
            "INSERT INTO audit_logs
            (user_id, action, table_name, record_id, old_data, new_data, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $userId,
            $action,
            $table,
            $recordId,
            $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }

    public static function mutate(
        int $productId,
        string $type,
        float $qty,
        string $note,
        int $userId
    ): void {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Jumlah harus lebih dari 0.');
        }

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                "SELECT stock, name FROM products WHERE id = ? FOR UPDATE"
            );
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new RuntimeException('Produk tidak ditemukan.');
            }

            $before = (float)$product['stock'];
            $after = $type === 'IN'
                ? $before + $qty
                : $before - $qty;

            if ($after < 0) {
                throw new RuntimeException('Stok tidak mencukupi.');
            }

            $update = $pdo->prepare(
                "UPDATE products SET stock = ? WHERE id = ?"
            );
            $update->execute([$after, $productId]);

            $movement = $pdo->prepare(
                "INSERT INTO stock_movements
                (product_id, type, quantity, stock_before, stock_after, note, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $movement->execute([
                $productId,
                $type,
                $qty,
                $before,
                $after,
                $note,
                $userId
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function movements(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));

        return db()->query(
            "SELECT m.*, p.name AS product_name, p.sku, u.name AS user_name
             FROM stock_movements m
             JOIN products p ON p.id = m.product_id
             JOIN users u ON u.id = m.user_id
             ORDER BY m.created_at DESC
             LIMIT {$limit}"
        )->fetchAll();
    }

    public static function dashboard(): array
    {
        $pdo = db();

        return [
            'products' => (int)$pdo->query(
                "SELECT COUNT(*) FROM products WHERE is_active=1"
            )->fetchColumn(),
            'low_stock' => (int)$pdo->query(
                "SELECT COUNT(*) FROM products
                 WHERE is_active=1 AND stock <= min_stock"
            )->fetchColumn(),
            'stock_value' => (float)$pdo->query(
                "SELECT COALESCE(SUM(stock * cost_price),0)
                 FROM products WHERE is_active=1"
            )->fetchColumn(),
            'today_movements' => (int)$pdo->query(
                "SELECT COUNT(*) FROM stock_movements
                 WHERE DATE(created_at)=CURDATE()"
            )->fetchColumn(),
        ];
    }
}
