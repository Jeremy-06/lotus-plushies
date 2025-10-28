<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../helpers/Session.php';

class UserController {
    
    private $userModel;
    private $orderModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->orderModel = new Order();
    }
    
    public function profile() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login to view profile');
            header('Location: login.php');
            exit();
        }
        
        $userId = Session::getUserId();
        $user = $this->userModel->findById($userId);
        $recentOrders = $this->orderModel->getCustomerOrders($userId);
        
        include __DIR__ . '/../views/profile.php';
    }
}