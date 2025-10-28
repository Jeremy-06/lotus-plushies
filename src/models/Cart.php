<?php

require_once __DIR__ . '/BaseModel.php';

class Cart extends BaseModel {
    
    protected $table = 'shopping_carts';
    
    public function getOrCreateCart($customerId) {
        $sql = "SELECT id FROM shopping_carts WHERE customer_id = ? LIMIT 1";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $customerId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cart = mysqli_fetch_assoc($result);
        
        if ($cart) {
            return $cart['id'];
        }
        
        // Create new cart
        $insertSql = "INSERT INTO shopping_carts (customer_id) VALUES (?)";
        $insertStmt = mysqli_prepare($this->conn, $insertSql);
        mysqli_stmt_bind_param($insertStmt, 'i', $customerId);
        mysqli_stmt_execute($insertStmt);
        return mysqli_insert_id($this->conn);
    }
    
    public function addItem($cartId, $productId, $quantity) {
        // Check if item already exists
        $checkSql = "SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?";
        $checkStmt = mysqli_prepare($this->conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, 'ii', $cartId, $productId);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        $existing = mysqli_fetch_assoc($result);
        
        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            $updateSql = "UPDATE cart_items SET quantity = ? WHERE id = ?";
            $updateStmt = mysqli_prepare($this->conn, $updateSql);
            mysqli_stmt_bind_param($updateStmt, 'ii', $newQuantity, $existing['id']);
            return mysqli_stmt_execute($updateStmt);
        } else {
            // Insert new item
            $insertSql = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)";
            $insertStmt = mysqli_prepare($this->conn, $insertSql);
            mysqli_stmt_bind_param($insertStmt, 'iii', $cartId, $productId, $quantity);
            return mysqli_stmt_execute($insertStmt);
        }
    }
    
    public function updateItemQuantity($cartId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($cartId, $productId);
        }
        
        $sql = "UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iii', $quantity, $cartId, $productId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function removeItem($cartId, $productId) {
        $sql = "DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $cartId, $productId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getCartItems($customerId) {
        $sql = "SELECT ci.*, p.product_name, p.selling_price, p.img_path, i.quantity_on_hand 
                FROM cart_items ci 
                INNER JOIN shopping_carts sc ON ci.cart_id = sc.id 
                INNER JOIN products p ON ci.product_id = p.id 
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE sc.customer_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $customerId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getCartTotal($customerId) {
        $sql = "SELECT SUM(ci.quantity * p.selling_price) as total 
                FROM cart_items ci 
                INNER JOIN shopping_carts sc ON ci.cart_id = sc.id 
                INNER JOIN products p ON ci.product_id = p.id 
                WHERE sc.customer_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $customerId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    }
    
    public function clearCart($customerId) {
        $sql = "DELETE ci FROM cart_items ci 
                INNER JOIN shopping_carts sc ON ci.cart_id = sc.id 
                WHERE sc.customer_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $customerId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getItemCount($customerId) {
        $sql = "SELECT SUM(ci.quantity) as count 
                FROM cart_items ci 
                INNER JOIN shopping_carts sc ON ci.cart_id = sc.id 
                WHERE sc.customer_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $customerId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['count'] ?? 0;
    }
}