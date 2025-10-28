<?php

require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';

class CartController {
    
    private $cartModel;
    private $productModel;
    
    public function __construct() {
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }
    
    public function index() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login to view cart');
            header('Location: login.php');
            exit();
        }
        
        $customerId = Session::getUserId();
        $cartItems = $this->cartModel->getCartItems($customerId);
        $cartTotal = $this->cartModel->getCartTotal($customerId);
        
        include __DIR__ . '/../views/cart.php';
    }
    
    public function add() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login to add items to cart');
            header('Location: login.php');
            exit();
        }
        
        if (!isset($_POST['type']) || $_POST['type'] !== 'add') {
            header('Location: products.php');
            exit();
        }
        
        // Validate CSRF token
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('message', 'Invalid request');
            header('Location: products.php');
            exit();
        }
        
        $productId = intval($_POST['product_id']);
        $quantity = intval($_POST['product_qty']);
        
        if ($quantity <= 0) {
            Session::setFlash('message', 'Invalid quantity');
            header('Location: products.php');
            exit();
        }
        
        // Check inventory
        $availableQty = $this->productModel->getInventory($productId);
        if ($quantity > $availableQty) {
            Session::setFlash('message', 'Insufficient stock available');
            header('Location: products.php');
            exit();
        }
        
        $customerId = Session::getUserId();
        $cartId = $this->cartModel->getOrCreateCart($customerId);
        
        if ($this->cartModel->addItem($cartId, $productId, $quantity)) {
            Session::setFlash('success', 'Product added to cart');
        } else {
            Session::setFlash('message', 'Failed to add product to cart');
        }
        
        header('Location: products.php');
        exit();
    }
    
    public function update() {
        if (!Session::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
        
        // Validate CSRF token
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('message', 'Invalid request');
            header('Location: cart.php');
            exit();
        }
        
        $customerId = Session::getUserId();
        $cartId = $this->cartModel->getOrCreateCart($customerId);
        
        // Update quantities
        if (isset($_POST['product_qty']) && is_array($_POST['product_qty'])) {
            foreach ($_POST['product_qty'] as $productId => $quantity) {
                if (is_numeric($quantity)) {
                    $this->cartModel->updateItemQuantity($cartId, intval($productId), intval($quantity));
                }
            }
        }
        
        // Remove items
        if (isset($_POST['remove_code']) && is_array($_POST['remove_code'])) {
            foreach ($_POST['remove_code'] as $productId) {
                $this->cartModel->removeItem($cartId, intval($productId));
            }
        }
        
        Session::setFlash('success', 'Cart updated');
        header('Location: cart.php');
        exit();
    }
    
    public function remove() {
        if (!Session::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
        
        if (!isset($_GET['product_id'])) {
            header('Location: cart.php');
            exit();
        }
        
        $customerId = Session::getUserId();
        $cartId = $this->cartModel->getOrCreateCart($customerId);
        $productId = intval($_GET['product_id']);
        
        if ($this->cartModel->removeItem($cartId, $productId)) {
            Session::setFlash('success', 'Item removed from cart');
        } else {
            Session::setFlash('message', 'Failed to remove item');
        }
        
        header('Location: cart.php');
        exit();
    }
}