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
    
    public function update($id, $categoryId, $productName, $description, $costPrice, $sellingPrice, $imgPath = null, $isActive = null) {
        if ($imgPath && $isActive !== null) {
            $sql = "UPDATE products SET category_id = ?, product_name = ?, description = ?, 
                    cost_price = ?, selling_price = ?, img_path = ?, is_active = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'issddsii', $categoryId, $productName, $description, $costPrice, $sellingPrice, $imgPath, $isActive, $id);
        } elseif ($imgPath) {
            $sql = "UPDATE products SET category_id = ?, product_name = ?, description = ?, 
                    cost_price = ?, selling_price = ?, img_path = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'issddsi', $categoryId, $productName, $description, $costPrice, $sellingPrice, $imgPath, $id);
        } elseif ($isActive !== null) {
            $sql = "UPDATE products SET category_id = ?, product_name = ?, description = ?, 
                    cost_price = ?, selling_price = ?, is_active = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'issddii', $categoryId, $productName, $description, $costPrice, $sellingPrice, $isActive, $id);
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
    
    public function searchAndSortProducts($search = '', $sortBy = 'created_at', $sortOrder = 'DESC') {
        // Validate sort column
        $allowedColumns = ['product_name', 'category_name', 'cost_price', 'selling_price', 'quantity_on_hand', 'created_at'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'created_at';
        }
        
        // Validate sort order
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // Map sorting columns to actual table columns
        $sortColumn = $sortBy;
        if ($sortBy === 'product_name' || $sortBy === 'cost_price' || $sortBy === 'selling_price' || $sortBy === 'created_at') {
            $sortColumn = 'p.' . $sortBy;
        } else if ($sortBy === 'category_name') {
            $sortColumn = 'c.category_name';
        } else if ($sortBy === 'quantity_on_hand') {
            $sortColumn = 'i.quantity_on_hand';
        }
        
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $sql = "SELECT p.*, c.category_name, i.quantity_on_hand 
                    FROM products p 
                    INNER JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN inventory i ON p.id = i.product_id 
                    WHERE (p.product_name LIKE ? OR p.description LIKE ? OR c.category_name LIKE ?)
                    ORDER BY $sortColumn $sortOrder";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sss', $searchTerm, $searchTerm, $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $sql = "SELECT p.*, c.category_name, i.quantity_on_hand 
                    FROM products p 
                    INNER JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN inventory i ON p.id = i.product_id 
                    ORDER BY $sortColumn $sortOrder";
            $result = mysqli_query($this->conn, $sql);
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
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
    
    public function getActiveProductsPaginated($limit = 9, $offset = 0) {
        $sql = "SELECT p.*, c.category_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 AND i.quantity_on_hand > 0 
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function countActiveProducts() {
        $sql = "SELECT COUNT(*) as total 
                FROM products p 
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 AND i.quantity_on_hand > 0";
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
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
    
    public function getByCategoryPaginated($categoryId, $limit = 9, $offset = 0) {
        $sql = "SELECT p.*, i.quantity_on_hand 
                FROM products p 
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.category_id = ? AND p.is_active = 1 AND i.quantity_on_hand > 0 
                ORDER BY p.product_name ASC
                LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iii', $categoryId, $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function countByCategory($categoryId) {
        $sql = "SELECT COUNT(*) as total 
                FROM products p 
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.category_id = ? AND p.is_active = 1 AND i.quantity_on_hand > 0";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
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
    
    public function searchPaginated($keyword, $limit = 9, $offset = 0) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT p.*, c.category_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE (p.product_name LIKE ? OR p.description LIKE ?) AND p.is_active = 1 
                ORDER BY p.product_name ASC
                LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssii', $searchTerm, $searchTerm, $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function countSearch($keyword) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT COUNT(*) as total 
                FROM products p 
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE (p.product_name LIKE ? OR p.description LIKE ?) AND p.is_active = 1";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
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
    
    public function hasOrderItems($productId) {
        $sql = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['count'] > 0;
    }
    
    public function hasActiveOrderItems($productId) {
        // Check if product has order items in non-completed orders
        $sql = "SELECT COUNT(*) as count 
                FROM order_items oi 
                INNER JOIN orders o ON oi.order_id = o.id 
                WHERE oi.product_id = ? 
                AND o.order_status IN ('pending', 'processing', 'shipped')";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row['count'] > 0;
    }
    
    public function deleteWithOrderItems($productId) {
        // Start transaction
        mysqli_begin_transaction($this->conn);
        
        try {
            // First, delete order items that are from completed/cancelled orders only
            $sql = "DELETE oi FROM order_items oi 
                    INNER JOIN orders o ON oi.order_id = o.id 
                    WHERE oi.product_id = ? 
                    AND o.order_status IN ('completed', 'delivered', 'cancelled')";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $productId);
            mysqli_stmt_execute($stmt);
            
            // Then delete from inventory
            $sql = "DELETE FROM inventory WHERE product_id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $productId);
            mysqli_stmt_execute($stmt);
            
            // Finally, delete the product
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $productId);
            mysqli_stmt_execute($stmt);
            
            // Commit transaction
            mysqli_commit($this->conn);
            return true;
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($this->conn);
            return false;
        }
    }
}