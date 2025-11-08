<?php

require_once __DIR__ . '/BaseModel.php';

class Product extends BaseModel {
    
    protected $table = 'products';
    
    public function create($categoryId, $productName, $description, $costPrice, $sellingPrice, $supplierId = null, $imgPath = '') {
        $sql = "INSERT INTO products (category_id, product_name, description, cost_price, selling_price, supplier_id, img_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'issddis', $categoryId, $productName, $description, $costPrice, $sellingPrice, $supplierId, $imgPath);
        
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
    
    public function update($id, $categoryId, $productName, $description, $costPrice, $sellingPrice, $supplierId = null, $imgPath = null, $isActive = null) {
        $updates = [];
        $types = '';
        $values = [];
        
        // Always update these
        $updates[] = "category_id = ?";
        $types .= 'i';
        $values[] = $categoryId;
        
        $updates[] = "product_name = ?";
        $types .= 's';
        $values[] = $productName;
        
        $updates[] = "description = ?";
        $types .= 's';
        $values[] = $description;
        
        $updates[] = "cost_price = ?";
        $types .= 'd';
        $values[] = $costPrice;
        
        $updates[] = "selling_price = ?";
        $types .= 'd';
        $values[] = $sellingPrice;
        
        $updates[] = "supplier_id = ?";
        $types .= 'i';
        $values[] = $supplierId;
        
        // Conditionally update these
        if ($imgPath !== null) {
            $updates[] = "img_path = ?";
            $types .= 's';
            $values[] = $imgPath;
        }
        
        if ($isActive !== null) {
            $updates[] = "is_active = ?";
            $types .= 'i';
            $values[] = $isActive;
        }
        
        // Add id at the end
        $types .= 'i';
        $values[] = $id;
        
        $sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getWithCategory() {
        $sql = "SELECT p.*, c.category_name, s.supplier_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 
                ORDER BY p.created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function searchAndSortProducts($search = '', $sortBy = 'created_at', $sortOrder = 'DESC') {
        // Validate sort column
        $allowedColumns = ['product_name', 'category_name', 'supplier_name', 'cost_price', 'selling_price', 'quantity_on_hand', 'created_at', 'is_active'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'created_at';
        }
        
        // Validate sort order
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // Map sorting columns to actual table columns
        $sortColumn = $sortBy;
        if ($sortBy === 'product_name' || $sortBy === 'cost_price' || $sortBy === 'selling_price' || $sortBy === 'created_at' || $sortBy === 'is_active') {
            $sortColumn = 'p.' . $sortBy;
        } else if ($sortBy === 'category_name') {
            $sortColumn = 'c.category_name';
        } else if ($sortBy === 'supplier_name') {
            $sortColumn = 's.supplier_name';
        } else if ($sortBy === 'quantity_on_hand') {
            $sortColumn = 'i.quantity_on_hand';
        }
        
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $sql = "SELECT p.*, c.category_name, s.supplier_name, i.quantity_on_hand 
                    FROM products p 
                    INNER JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    LEFT JOIN inventory i ON p.id = i.product_id 
                    WHERE (p.product_name LIKE ? OR p.description LIKE ? OR c.category_name LIKE ? OR s.supplier_name LIKE ?)
                    ORDER BY $sortColumn $sortOrder";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $sql = "SELECT p.*, c.category_name, s.supplier_name, i.quantity_on_hand 
                    FROM products p 
                    INNER JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    LEFT JOIN inventory i ON p.id = i.product_id 
                    ORDER BY $sortColumn $sortOrder";
            $result = mysqli_query($this->conn, $sql);
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }
    
    public function getActiveProducts() {
        $sql = "SELECT p.*, c.category_name, s.supplier_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 AND i.quantity_on_hand > 0 
                ORDER BY p.created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getActiveProductsPaginated($limit = 9, $offset = 0) {
        $sql = "SELECT p.*, c.category_name, s.supplier_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
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
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand 
                FROM products p 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
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
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand 
                FROM products p 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
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
        $sql = "SELECT p.*, c.category_name, s.supplier_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
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
        $sql = "SELECT p.*, c.category_name, s.supplier_name, i.quantity_on_hand 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
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
    
    public function findByIdWithDetails($productId) {
        $sql = "SELECT p.*, c.category_name, s.supplier_name 
                FROM products p 
                INNER JOIN categories c ON p.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                WHERE p.id = ? 
                LIMIT 1";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
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
                AND o.order_status NOT IN ('completed', 'cancelled')";
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
            // Get product image path before deletion
            $product = $this->findById($productId);
            $imagePath = $product['img_path'] ?? null;
            
            // Get all product images from product_images table
            $productImages = $this->getProductImages($productId);
            
            // NOTE: We don't delete order_items anymore to preserve order history
            // The foreign key will set product_id to NULL automatically
            // But let's make sure snapshot data is populated for any order_items that don't have it
            $sql = "UPDATE order_items SET 
                    product_name = COALESCE(product_name, ?),
                    product_image = COALESCE(product_image, ?)
                    WHERE product_id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ssi', $product['product_name'], $product['img_path'], $productId);
            mysqli_stmt_execute($stmt);
            
            // Delete all product images from product_images table and their physical files
            foreach ($productImages as $image) {
                // Delete physical file
                $fullImagePath = __DIR__ . '/../../public/uploads/' . $image['image_path'];
                if (file_exists($fullImagePath)) {
                    @unlink($fullImagePath);
                }
            }
            
            // Delete all records from product_images table
            $sql = "DELETE FROM product_images WHERE product_id = ?";
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
            
            // Delete the physical image file if it exists (legacy img_path)
            if (!empty($imagePath)) {
                $fullImagePath = __DIR__ . '/../../public/uploads/' . $imagePath;
                // Only attempt to delete if file exists, no error if it doesn't
                if (file_exists($fullImagePath)) {
                    @unlink($fullImagePath);
                }
            }
            
            // Commit transaction
            mysqli_commit($this->conn);
            return true;
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($this->conn);
            return false;
        }
    }
    
    // ========== Product Images Methods ==========
    
    /**
     * Get all images for a product
     * @param int $productId
     * @return array
     */
    public function getProductImages($productId) {
        $sql = "SELECT * FROM product_images 
                WHERE product_id = ? 
                ORDER BY is_primary DESC, display_order ASC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    /**
     * Add a new image for a product
     * @param int $productId
     * @param string $imagePath
     * @param int $displayOrder
     * @param bool $isPrimary
     * @return int|bool Image ID on success, false on failure
     */
    public function addProductImage($productId, $imagePath, $displayOrder = 0, $isPrimary = false) {
        // If setting as primary, unset other primary images
        if ($isPrimary) {
            $this->unsetPrimaryImage($productId);
        }
        
        $sql = "INSERT INTO product_images (product_id, image_path, display_order, is_primary) 
                VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        $isPrimaryInt = $isPrimary ? 1 : 0;
        mysqli_stmt_bind_param($stmt, 'isii', $productId, $imagePath, $displayOrder, $isPrimaryInt);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        }
        return false;
    }
    
    /**
     * Delete a product image
     * @param int $imageId
     * @return bool
     */
    public function deleteProductImage($imageId) {
        // Get image path before deletion
        $sql = "SELECT image_path FROM product_images WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $imageId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $image = mysqli_fetch_assoc($result);
        
        if (!$image) {
            return false;
        }
        
        // Delete from database
        $sql = "DELETE FROM product_images WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $imageId);
        
        if (mysqli_stmt_execute($stmt)) {
            // Delete physical file
            $fullImagePath = __DIR__ . '/../../public/uploads/' . $image['image_path'];
            if (file_exists($fullImagePath)) {
                @unlink($fullImagePath);
            }
            return true;
        }
        return false;
    }
    
    /**
     * Set an image as primary
     * @param int $imageId
     * @return bool
     */
    public function setPrimaryImage($imageId) {
        // Get product_id for this image
        $sql = "SELECT product_id FROM product_images WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $imageId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $image = mysqli_fetch_assoc($result);
        
        if (!$image) {
            return false;
        }
        
        // Unset all primary images for this product
        $this->unsetPrimaryImage($image['product_id']);
        
        // Set this image as primary
        $sql = "UPDATE product_images SET is_primary = 1 WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $imageId);
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Unset primary image for a product
     * @param int $productId
     * @return bool
     */
    private function unsetPrimaryImage($productId) {
        $sql = "UPDATE product_images SET is_primary = 0 WHERE product_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        return mysqli_stmt_execute($stmt);
    }
    
    /**
     * Update display order for an image
     * @param int $imageId
     * @param int $displayOrder
     * @return bool
     */
    public function updateImageOrder($imageId, $displayOrder) {
        $sql = "UPDATE product_images SET display_order = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $displayOrder, $imageId);
        return mysqli_stmt_execute($stmt);
    }
}