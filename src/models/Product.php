<?php

require_once __DIR__ . '/BaseModel.php';

class Product extends BaseModel {
    
    protected $table = 'products';
    
    public function create($categoryId, $productName, $description, $costPrice, $sellingPrice, $imgPath = '') {
        $sql = "INSERT INTO products (category_id, product_name, description, cost_price, selling_price, img_path) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'issdds', $categoryId, $productName, $description, $costPrice, $sellingPrice, $imgPath);
        
        if (mysqli_stmt_execute($stmt)) {
            $productId = mysqli_insert_id($this->conn);
            // Create inventory record
            $invSql = "INSERT INTO inventory (product_id, quantity_on_hand) VALUES (?, 0)";
            $invStmt = mysqli_prepare($this->conn, $invSql);
            mysqli_stmt_bind_param($invStmt, 'i', $productId);
            mysqli_stmt_execute($invStmt);
            return $productId;
        }
        return false;
    }
    
    public function update($id, $categoryId, $productName, $description, $costPrice, $sellingPrice, $imgPath = null) {
        if ($imgPath) {
            $sql = "UPDATE products SET category_id = ?, product_name = ?, description = ?, 
                    cost_price = ?, selling_price = ?, img_path = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'issddsi', $categoryId, $productName, $description, $costPrice, $sellingPrice, $imgPath, $id);
        } else {
            $sql = "UPDATE products SET category_id = ?, product_name = ?, description = ?, 
                    cost_price = ?, selling_price = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'issddi', $categoryId, $productName, $description, $costPrice, $sellingPrice, $id);
        }
        return mysqli_stmt_execute($stmt);
    }
    
    public function getWithCategory() {
        $sql = "SELECT p.*, c.category_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 
                ORDER BY p.created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getActiveProducts() {
        $sql = "SELECT p.*, c.category_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 AND i.quantity_on_hand > 0 
                ORDER BY p.created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getByCategory($categoryId) {
        $sql = "SELECT p.*, i.quantity_on_hand 
                FROM products p 
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.category_id = ? AND p.is_active = 1 AND i.quantity_on_hand > 0 
                ORDER BY p.product_name ASC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function search($keyword) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT p.*, c.category_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE (p.product_name LIKE ? OR p.description LIKE ?) AND p.is_active = 1 
                ORDER BY p.product_name ASC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function updateInventory($productId, $quantity) {
        $sql = "UPDATE inventory SET quantity_on_hand = ? WHERE product_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $quantity, $productId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getInventory($productId) {
        $sql = "SELECT quantity_on_hand FROM inventory WHERE product_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row ? $row['quantity_on_hand'] : 0;
    }
}