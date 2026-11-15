<?php
declare(strict_types=1);

class OrderController
{
    public static function checkout(): void
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '/');
        }

        verifyCsrf();

        try {
            $items = $_POST['items'] ?? [];
            if (!is_array($items)) {
                throw new InvalidArgumentException('Daftar menu pesanan tidak valid.');
            }

            $result = OrderModel::checkout($items, [
                'customer_name' => $_POST['customer_name'] ?? '',
                'table_no' => $_POST['table_no'] ?? '',
                'order_type' => $_POST['order_type'] ?? 'Dine In',
                'payment_method' => $_POST['payment_method'] ?? 'Tunai',
                'order_status' => $_POST['order_status'] ?? 'PAID',
                'discount' => $_POST['discount'] ?? 0,
                'cash_paid' => $_POST['cash_paid'] ?? 0,
                'note' => $_POST['note'] ?? '',
            ], (int)user()['id']);

            if ($result['status'] === 'OPEN') {
                flash('success', "Bill #{$result['order_code']} ({$result['table_no']}) disimpan sebagai Belum Bayar (" . formatRp($result['total_amount']) . ").");
                redirect(BASE_URL . '/');
            }

            $msg = "Pesanan #{$result['order_code']} lunas (" . formatRp($result['total_amount']) . " via {$result['payment_method']})";
            if ($result['change_amount'] > 0) {
                $msg .= ' · Kembalian ' . formatRp($result['change_amount']);
            }
            flash('success', $msg);
            redirect(BASE_URL . '/?page=dashboard&receipt=' . (int)$result['id']);
        } catch (Throwable $e) {
            flash('danger', $e->getMessage());
            redirect(BASE_URL . '/?page=dashboard');
        }
    }

    public static function payBill(): void
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '/');
        }
        verifyCsrf();

        try {
            $id = (int)($_POST['order_id'] ?? 0);
            $method = (string)($_POST['payment_method'] ?? 'Tunai');
            $cashPaid = (float)($_POST['cash_paid'] ?? 0);

            $res = OrderModel::payOpenOrder($id, $method, $cashPaid, (int)user()['id']);
            $msg = "Tagihan #{$res['order_code']} berhasil dilunasi ({$res['payment_method']})";
            if ($res['change_amount'] > 0) {
                $msg .= ' · Kembalian ' . formatRp($res['change_amount']);
            }
            flash('success', $msg);
            redirect(BASE_URL . '/?page=dashboard&receipt=' . (int)$res['id']);
        } catch (Throwable $e) {
            flash('danger', $e->getMessage());
            redirect(BASE_URL . '/?page=dashboard');
        }
    }

    public static function voidOrder(): void
    {
        requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '/?page=sales-report');
        }
        verifyCsrf();

        try {
            $id = (int)($_POST['order_id'] ?? 0);
            OrderModel::voidOrder($id, (int)user()['id']);
            flash('success', 'Transaksi berhasil dibatalkan (VOID) dan stok porsi telah dikembalikan.');
        } catch (Throwable $e) {
            flash('danger', $e->getMessage());
        }

        $back = ($_POST['redirect_to'] ?? '') === 'history' ? '/?page=history' : '/?page=sales-report';
        redirect(BASE_URL . $back);
    }

    public static function receipt(): void
    {
        requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $order = OrderModel::findOrder($id);
        if (!$order) {
            flash('danger', 'Struk pesanan tidak ditemukan.');
            redirect(BASE_URL . '/?page=history');
        }
        require __DIR__ . '/../views/receipt.php';
    }

    public static function salesReport(): void
    {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $action = $_POST['action'] ?? '';
            try {
                if ($action === 'add_expense') {
                    ExpenseModel::create([
                        'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
                        'category' => $_POST['category'] ?? 'Operasional',
                        'description' => $_POST['description'] ?? '',
                        'amount' => $_POST['amount'] ?? 0,
                    ], (int)user()['id']);
                    flash('success', 'Pengeluaran kas berhasil dicatat.');
                } elseif ($action === 'delete_expense') {
                    ExpenseModel::delete((int)($_POST['id'] ?? 0), (int)user()['id']);
                    flash('success', 'Catatan pengeluaran dihapus.');
                }
            } catch (Throwable $e) {
                flash('danger', $e->getMessage());
            }
            redirect(BASE_URL . '/?page=sales-report');
        }

        $start = trim($_GET['start'] ?? '');
        $end = trim($_GET['end'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
            $start = null;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            $end = null;
        }

        $dailyReport = OrderModel::dailySalesReport($start, $end);
        $menuReport = OrderModel::menuSalesReport($start, $end);

        if (($_GET['export'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="laporan_warkop_mk_' . date('Ymd') . '.csv"');
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Jumlah Transaksi', 'Item Terjual', 'Omzet Penjualan (Rp)', 'Pengeluaran (Rp)', 'Kas Bersih (Rp)']);
            foreach ($dailyReport as $d) {
                fputcsv($out, [
                    $d['sale_date'],
                    $d['order_count'],
                    $d['qty_sold'],
                    $d['revenue'],
                    $d['expense'],
                    $d['net_cash'],
                ]);
            }
            fclose($out);
            exit;
        }

        $summary = OrderModel::todaySummary();
        $todayOrders = OrderModel::todayOrders();
        $expenses = ExpenseModel::all($start, $end);
        $recentOrders = OrderModel::recentOrders(200);
        $auditLogs = OrderModel::recentAuditLogs(80);
        require __DIR__ . '/../views/sales_report.php';
    }
}
