<?php

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';

class OrderController {
    
    private $orderModel;
    private $cartModel;
    private $productModel;
    
    public function __construct() {
        $this->orderModel = new Order();
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }
    
    public function checkout() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login to checkout');
            header('Location: login.php');
            exit();
        }
        
        $customerId = Session::getUserId();
        $cartItems = $this->cartModel->getCartItems($customerId);
        
        if (empty($cartItems)) {
            Session::setFlash('message', 'Your cart is empty');
            header('Location: cart.php');
            exit();
        }
        
        $subtotal = $this->cartModel->getCartTotal($customerId);
        $shippingCost = 50.00; // Fixed shipping
        $taxAmount = $subtotal * 0.12; // 12% tax
        $totalAmount = $subtotal + $shippingCost + $taxAmount;
        
        include __DIR__ . '/../views/checkout.php';
    }
    
    public function placeOrder() {
        if (!Session::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
        
        if (!isset($_POST['place_order'])) {
            header('Location: checkout.php');
            exit();
        }
        
        // Validate CSRF token
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('message', 'Invalid request');
            header('Location: checkout.php');
            exit();
        }
        
        $customerId = Session::getUserId();
        $cartItems = $this->cartModel->getCartItems($customerId);
        
        if (empty($cartItems)) {
            Session::setFlash('message', 'Your cart is empty');
            header('Location: cart.php');
            exit();
        }
        
        // Calculate totals
        $subtotal = $this->cartModel->getCartTotal($customerId);
        $shippingCost = 50.00;
        $taxAmount = $subtotal * 0.12;
        $totalAmount = $subtotal + $shippingCost + $taxAmount;
        
        // Create order
        $orderId = $this->orderModel->create($customerId, $subtotal, $shippingCost, $taxAmount, $totalAmount);
        
        if ($orderId) {
            // Add order items and update inventory
            foreach ($cartItems as $item) {
                $this->orderModel->addOrderItem($orderId, $item['product_id'], $item['quantity'], $item['selling_price']);
                
                // Update inventory
                $currentQty = $this->productModel->getInventory($item['product_id']);
                $newQty = $currentQty - $item['quantity'];
                $this->productModel->updateInventory($item['product_id'], $newQty);
            }
            
            // Clear cart
            $this->cartModel->clearCart($customerId);
            
            Session::set('order_id', $orderId);
            header('Location: order_success.php');
            exit();
        } else {
            Session::setFlash('message', 'Failed to place order. Please try again');
            header('Location: checkout.php');
            exit();
        }
    }
    
    public function success() {
        if (!Session::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
        
        if (!Session::has('order_id')) {
            header('Location: index.php');
            exit();
        }
        
        $orderId = Session::get('order_id');
        $order = $this->orderModel->getOrderDetails($orderId);
        Session::remove('order_id');
        
        include __DIR__ . '/../views/order_success.php';
    }
    
    public function history() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login to view order history');
            header('Location: login.php');
            exit();
        }
        
        $customerId = Session::getUserId();
        $orders = $this->orderModel->getCustomerOrders($customerId);
        
        include __DIR__ . '/../views/order_history.php';
    }
}