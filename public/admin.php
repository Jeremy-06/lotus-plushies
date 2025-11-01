<?php
session_start();

require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/helpers/Session.php';

// Set timezone
date_default_timezone_set(Config::TIMEZONE);

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
    
    case 'users':
        $controller->users();
        break;
        
    case 'edit_user':
        $controller->editUser();
        break;
        
    case 'delete_user':
        $controller->deleteUser();
        break;
        
    case 'categories':
        $controller->categories();
        break;
        
    case 'create_category':
        $controller->createCategory();
        break;
        
    case 'edit_category':
        $controller->editCategory();
        break;
        
    case 'delete_category':
        $controller->deleteCategory();
        break;
        
    case 'sales_report':
        $controller->salesReport();
        break;
    
    case 'expenses':
        $controller->expenses();
        break;
        
    case 'create_expense':
        $controller->createExpense();
        break;
        
    case 'edit_expense':
        $controller->editExpense();
        break;
        
    case 'delete_expense':
        $controller->deleteExpense();
        break;
        
    case 'dashboard':
    default:
        $controller->dashboard();
        break;
}