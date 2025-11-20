<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/helpers/Session.php';
require_once __DIR__ . '/../src/controllers/ProductController.php';
require_once __DIR__ . '/../src/controllers/CartController.php';
require_once __DIR__ . '/../src/controllers/OrderController.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/UserController.php';
require_once __DIR__ . '/../src/models/Product.php';
require_once __DIR__ . '/../src/models/Category.php';

// Set timezone
date_default_timezone_set(Config::TIMEZONE);

Session::start();

// Route handling
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch ($page) {
    case 'login':
        $controller = new AuthController();
        if ($action === 'process') {
            $controller->login();
        } else {
            $controller->showLogin();
        }
        break;
        
    case 'register':
        $controller = new AuthController();
        if ($action === 'process') {
            $controller->register();
        } else {
            $controller->showRegister();
        }
        break;
        
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
        
    case 'products':
        $controller = new ProductController();
        $controller->index();
        break;
        
    case 'product':
        $controller = new ProductController();
        $controller->show();
        break;
        
    case 'add_review':
        $controller = new ProductController();
        $controller->addReview();
        break;
        
    case 'edit_review': // New case for editing reviews
        $controller = new ProductController();
        $controller->editReview();
        break;
        
    case 'admin_reply_review':
        $controller = new ProductController();
        $controller->adminReplyReview();
        break;
        
    case 'cart':
        $controller = new CartController();
        if ($action === 'add') {
            $controller->add();
        } elseif ($action === 'update') {
            $controller->update();
        } elseif ($action === 'remove') {
            $controller->remove();
        } else {
            $controller->index();
        }
        break;
        
    case 'checkout':
        $controller = new OrderController();
        if ($action === 'process') {
            $controller->placeOrder();
        } else {
            $controller->checkout();
        }
        break;
        
    case 'order_success':
        $controller = new OrderController();
        $controller->success();
        break;
        
    case 'order_history':
        $controller = new OrderController();
        $controller->history();
        break;
    
    case 'order_detail':
        $controller = new OrderController();
        $controller->detail();
        break;
    
    case 'order':
        $controller = new OrderController();
        if ($action === 'confirm_receipt') {
            $controller->confirmReceipt();
        } elseif ($action === 'cancel') {
            $controller->cancelOrder();
        } elseif ($action === 'reorder') {
            $controller->reorder();
        }
        break;
        
    case 'profile':
        $controller = new UserController();
        if ($action === 'update') {
            $controller->updateProfile();
        } elseif ($action === 'change_password') {
            $controller->changePassword();
        } elseif ($action === 'delete_account') {
            $controller->deleteAccount();
        } else {
            $controller->profile();
        }
        break;
        
    case 'home':
    default:
        $productModel = new Product();
        $categoryModel = new Category();
        
        // Handle sorting and filtering
        $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'purchases';
        $categoryId = isset($_GET['category']) && !empty($_GET['category']) ? (int)$_GET['category'] : null;
        
        $products = $productModel->getFeaturedProducts($sortBy, $categoryId, 8);
        $categories = $categoryModel->getActive();
        include __DIR__ . '/../src/views/home.php';
        break;
}