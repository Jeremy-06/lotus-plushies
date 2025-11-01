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
            header('Location: index.php?page=login');
            exit();
        }
        
        $customerId = Session::getUserId();
        // If the user submitted a selection from the cart, store it in session
        if (isset($_POST['checkout_items']) && is_array($_POST['checkout_items'])) {
            $selectedProductIds = array_map('intval', $_POST['checkout_items']);
            Session::set('checkout_selection', $selectedProductIds);
        }

        $cartItems = $this->cartModel->getCartItems($customerId);

        // Apply selection filter if present
        $selection = Session::get('checkout_selection', null);
        if (is_array($selection) && !empty($selection)) {
            $cartItems = array_values(array_filter($cartItems, function($item) use ($selection) {
                return in_array((int)$item['product_id'], $selection, true);
            }));
        }
        
        if (empty($cartItems)) {
            Session::setFlash('message', 'Your cart is empty');
            header('Location: index.php?page=cart');
            exit();
        }
        
        // Recalculate subtotal based on selection if any
        if (is_array($selection) && !empty($selection)) {
            $subtotal = 0.0;
            foreach ($cartItems as $item) {
                $subtotal += $item['selling_price'] * $item['quantity'];
            }
        } else {
            $subtotal = $this->cartModel->getCartTotal($customerId);
        }
        $shippingCost = 0.00; // No shipping - walk-in/pickup
        $taxAmount = 0.00; // No tax
        $totalAmount = $subtotal + $shippingCost + $taxAmount;
        
        include __DIR__ . '/../views/checkout.php';
    }
    
    public function placeOrder() {
        if (!Session::isLoggedIn()) {
            header('Location: index.php?page=login');
            exit();
        }
        
        if (!isset($_POST['place_order'])) {
            header('Location: index.php?page=checkout');
            exit();
        }
        
        // Validate CSRF token
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('message', 'Invalid request');
            header('Location: index.php?page=checkout');
            exit();
        }
        
        $customerId = Session::getUserId();
        $cartItems = $this->cartModel->getCartItems($customerId);
        
        // Apply selection
        $selection = Session::get('checkout_selection', null);
        if (is_array($selection) && !empty($selection)) {
            $cartItems = array_values(array_filter($cartItems, function($item) use ($selection) {
                return in_array((int)$item['product_id'], $selection, true);
            }));
        }
        
        if (empty($cartItems)) {
            Session::setFlash('message', 'Your cart is empty');
            header('Location: index.php?page=cart');
            exit();
        }
        
        // Calculate totals
        if (is_array($selection) && !empty($selection)) {
            $subtotal = 0.0;
            foreach ($cartItems as $item) {
                $subtotal += $item['selling_price'] * $item['quantity'];
            }
        } else {
            $subtotal = $this->cartModel->getCartTotal($customerId);
        }
        $shippingCost = 0.00; // No shipping - walk-in/pickup
        $taxAmount = 0.00; // No tax
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
            
            // Clear selected items from cart (or all if no selection)
            if (is_array($selection) && !empty($selection)) {
                $cartId = $this->cartModel->getOrCreateCart($customerId);
                foreach ($selection as $productId) {
                    $this->cartModel->removeItem($cartId, (int)$productId);
                }
                Session::remove('checkout_selection');
            } else {
                $this->cartModel->clearCart($customerId);
            }
            
            Session::set('order_id', $orderId);
            header('Location: index.php?page=order_success');
            exit();
        } else {
            Session::setFlash('message', 'Failed to place order. Please try again');
            header('Location: index.php?page=checkout');
            exit();
        }
    }
    
    public function success() {
        if (!Session::isLoggedIn()) {
            header('Location: index.php?page=login');
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
            header('Location: index.php?page=login');
            exit();
        }
        
        $customerId = Session::getUserId();
        $allOrders = $this->orderModel->getCustomerOrders($customerId);
        $orders = $allOrders; // Keep all orders for badge counts
        
        // Apply filter if specified for display
        if (isset($_GET['filter']) && $_GET['filter'] !== 'all') {
            $filter = $_GET['filter'];
            $filteredOrders = array_filter($allOrders, function($order) use ($filter) {
                // Group processing and delivered into shipped for customer view
                if ($filter === 'shipped') {
                    return in_array($order['order_status'], ['processing', 'shipped', 'delivered']);
                }
                return $order['order_status'] === $filter;
            });
            $orders = $filteredOrders;
        }
        
        include __DIR__ . '/../views/order_history.php';
    }

    public function detail() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login to view order details');
            header('Location: index.php?page=login');
            exit();
        }
        if (!isset($_GET['id'])) {
            header('Location: index.php?page=order_history');
            exit();
        }
        $orderId = intval($_GET['id']);
        $order = $this->orderModel->getOrderDetails($orderId);
        if (!$order) {
            Session::setFlash('message', 'Order not found');
            header('Location: index.php?page=order_history');
            exit();
        }
        // Ensure the logged in user owns this order
        if ((int)$order['customer_id'] !== (int)Session::getUserId()) {
            Session::setFlash('message', 'You are not allowed to view this order');
            header('Location: index.php?page=order_history');
            exit();
        }
        $orderItems = $this->orderModel->getOrderItems($orderId);
        include __DIR__ . '/../views/order_detail.php';
    }
    
    public function confirmReceipt() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login first');
            header('Location: index.php?page=login');
            exit();
        }
        
        if (!isset($_GET['id'])) {
            Session::setFlash('message', 'Invalid order');
            header('Location: index.php?page=order_history');
            exit();
        }
        
        $orderId = intval($_GET['id']);
        $customerId = Session::getUserId();
        
        // Verify order belongs to customer and mark as completed
        if ($this->orderModel->markAsCompleted($orderId, $customerId)) {
            Session::setFlash('success', 'Order marked as received. Thank you!');
        } else {
            Session::setFlash('message', 'Failed to update order status');
        }
        
        header('Location: index.php?page=order_history');
        exit();
    }
    
    public function cancelOrder() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login first');
            header('Location: index.php?page=login');
            exit();
        }
        
        if (!isset($_GET['id'])) {
            Session::setFlash('message', 'Invalid order');
            header('Location: index.php?page=order_history');
            exit();
        }
        
        $orderId = intval($_GET['id']);
        $customerId = Session::getUserId();
        
        // Get order details to verify ownership and status
        $order = $this->orderModel->getOrderDetails($orderId);
        
        if (!$order || (int)$order['customer_id'] !== (int)$customerId) {
            Session::setFlash('message', 'Order not found or unauthorized');
            header('Location: index.php?page=order_history');
            exit();
        }
        
        // Only allow cancellation of pending orders
        if ($order['order_status'] !== 'pending') {
            Session::setFlash('message', 'Only pending orders can be cancelled');
            header('Location: index.php?page=order_history');
            exit();
        }
        
        // Cancel the order and restore inventory
        if ($this->orderModel->cancelOrder($orderId)) {
            // Restore inventory for cancelled order
            $orderItems = $this->orderModel->getOrderItems($orderId);
            foreach ($orderItems as $item) {
                $currentQty = $this->productModel->getInventory($item['product_id']);
                $newQty = $currentQty + $item['quantity'];
                $this->productModel->updateInventory($item['product_id'], $newQty);
            }
            
            Session::setFlash('success', 'Order cancelled successfully. Inventory has been restored.');
        } else {
            Session::setFlash('message', 'Failed to cancel order');
        }
        
        header('Location: index.php?page=order_history');
        exit();
    }
}