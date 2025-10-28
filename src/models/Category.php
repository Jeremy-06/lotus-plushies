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
}