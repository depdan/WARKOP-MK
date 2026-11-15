<?php
declare(strict_types=1);

require __DIR__ . '/../config/app.php';
require __DIR__ . '/../app/helpers/db.php';
require __DIR__ . '/../app/helpers/security.php';
require __DIR__ . '/../app/helpers/auth.php';
require __DIR__ . '/../app/helpers/ui.php';
require __DIR__ . '/../app/models/StockModel.php';
require __DIR__ . '/../app/models/OrderModel.php';
require __DIR__ . '/../app/models/ExpenseModel.php';
require __DIR__ . '/../app/models/UserModel.php';
require __DIR__ . '/../app/controllers/AuthController.php';
require __DIR__ . '/../app/controllers/DashboardController.php';
require __DIR__ . '/../app/controllers/OrderController.php';
require __DIR__ . '/../app/controllers/StockController.php';
require __DIR__ . '/../app/controllers/UserController.php';

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'login':
        AuthController::login();
        break;
    case 'logout':
        AuthController::logout();
        break;
    case 'checkout':
        OrderController::checkout();
        break;
    case 'pay-bill':
        OrderController::payBill();
        break;
    case 'void-order':
        OrderController::voidOrder();
        break;
    case 'receipt':
        OrderController::receipt();
        break;
    case 'sales-report':
        OrderController::salesReport();
        break;
    case 'products':
        StockController::products();
        break;
    case 'stock-movement':
        StockController::movement();
        break;
    case 'history':
        StockController::history();
        break;
    case 'users':
        UserController::index();
        break;
    default:
        DashboardController::index();
}
