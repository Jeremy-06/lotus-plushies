<?php

require_once __DIR__ . '/BaseModel.php';

class Order extends BaseModel {
    
    protected $table = 'orders';
    
    public function create($customerId, $subtotal, $shippingCost, $taxAmount, $totalAmount) {
        $orderNumber = 'ORD-' . time() . '-' . $customerId;
        
        $sql = "INSERT INTO orders (customer_id, order_number, subtotal, shipping_cost, tax_amount, total_amount) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'isdddd', $customerId, $orderNumber, $subtotal, $shippingCost, $taxAmount, $totalAmount);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        }
        return false;
    }
    
    public function addOrderItem($orderId, $productId, $quantity, $unitPrice) {
        $itemTotal = $quantity * $unitPrice;
        
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, item_total) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iiidd', $orderId, $productId, $quantity, $unitPrice, $itemTotal);
        return mysqli_stmt_execute($stmt);
    }
    
    public function updateStatus($orderId, $status) {
        $sql = "UPDATE orders SET order_status = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $status, $orderId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getCustomerOrders($customerId) {
        $sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $customerId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getOrderDetails($orderId) {
        $sql = "SELECT o.*, u.email 
                FROM orders o 
                INNER JOIN users u ON o.customer_id = u.id 
                WHERE o.id = ? LIMIT 1";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.product_name, p.img_path 
                FROM order_items oi 
                INNER JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getAllOrders() {
        $sql = "SELECT o.*, u.email, COUNT(oi.id) as item_count 
                FROM orders o 
                INNER JOIN users u ON o.customer_id = u.id 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                GROUP BY o.id 
                ORDER BY o.created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getOrdersByStatus($status) {
        $sql = "SELECT o.*, u.email 
                FROM orders o 
                INNER JOIN users u ON o.customer_id = u.id 
                WHERE o.order_status = ? 
                ORDER BY o.created_at DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $status);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}