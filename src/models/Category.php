<?php

require_once __DIR__ . '/BaseModel.php';

class Category extends BaseModel {
    
    protected $table = 'categories';
    
    public function create($categoryName, $description = '') {
        $sql = "INSERT INTO categories (category_name, description) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $categoryName, $description);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        }
        return false;
    }
    
    public function update($id, $categoryName, $description, $isActive) {
        $sql = "UPDATE categories SET category_name = ?, description = ?, is_active = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssii', $categoryName, $description, $isActive, $id);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getActive() {
        $sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY category_name ASC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getAll() {
        $sql = "SELECT * FROM categories ORDER BY category_name ASC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function delete($id) {
        // Check if category is used by any products
        $checkSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
        $checkStmt = mysqli_prepare($this->conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, 'i', $id);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['count'] > 0) {
            return false; // Cannot delete category with associated products
        }
        
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
}