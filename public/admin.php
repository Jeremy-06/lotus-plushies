<?php
session_start();

require_once __DIR__ . '/../src/helpers/Session.php';

Session::start();

// Check if user is logged in and is admin
if (!Session::isLoggedIn() || !Session::isAdmin()) {
    Session::setFlash('message', 'Access denied. Admin login required.');
    header('Location: index.php?page=login');
    exit();
}

// Now require the controller after authentication check
require_once __DIR__ . '/../src/controllers/AdminController.php';
require_once __DIR__ . '/../src/models/Product.php';
require_once __DIR__ . '/../src/models/Category.php';

$controller = new AdminController();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch ($page) {
    case 'products':
        $controller->products();
        break;
        
    case 'create_product':
        $controller->createProduct();
        break;
        
    case 'edit_product':
        $controller->updateProduct();
        break;
        
    case 'delete_product':
        $controller->deleteProduct();
        break;
        
    case 'orders':
        $controller->orders();
        break;
        
    case 'order_detail':
        $controller->orderDetail();
        break;
        
    case 'update_order':
        $controller->updateOrderStatus();
        break;
        
    case 'customers':
        $controller->customers();
        break;
        
    case 'dashboard':
    default:
        $controller->dashboard();
        break;
}