<?php
declare(strict_types=1);

class DashboardController {
    public static function index(): void {
        requireLogin();
        $search = trim($_GET['q'] ?? '');
        $stats = StockModel::dashboard();
        $orderStats = OrderModel::todaySummary();
        $openBills = OrderModel::openBills();
        $allProducts = StockModel::products($search !== '' ? $search : null);
        $activeProducts = array_values(array_filter($allProducts, fn($p) => (int)$p['is_active'] === 1));
        $lowStock = array_values(array_filter($activeProducts, fn($p) => (float)$p['stock'] <= (float)$p['min_stock']));
        $categories = StockModel::categories();
        $popularIds = $orderStats['popular_ids'] ?? [];
        $receiptOrder = !empty($_GET['receipt']) ? OrderModel::findOrder((int)$_GET['receipt']) : null;
        require __DIR__ . '/../views/dashboard.php';
    }
}
