<?php

require_once __DIR__ . '/BaseModel.php';

class Product extends BaseModel {
    
    protected $table = 'products';
    
    public function create($categoryIds, $productName, $description, $costPrice, $sellingPrice, $supplierId = null, $imgPath = '') {
        $this->conn->begin_transaction();
        try {
            $averageRating = 0.00;
            $reviewCount = 0;
            $sql = "INSERT INTO products (product_name, description, cost_price, selling_price, supplier_id, img_path, average_rating, review_count) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssddisdd', $productName, $description, $costPrice, $sellingPrice, $supplierId, $imgPath, $averageRating, $reviewCount);
            
            if ($stmt->execute()) {
                $productId = $this->conn->insert_id;
                
                // Insert categories
                if (!empty($categoryIds) && is_array($categoryIds)) {
                    foreach ($categoryIds as $categoryId) {
                        $catSql = "INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)";
                        $catStmt = $this->conn->prepare($catSql);
                        $catStmt->bind_param('ii', $productId, $categoryId);
                        if (!$catStmt->execute()) {
                            throw new Exception("Failed to insert category: " . $this->conn->error);
                        }
                    }
                }
                
                $invSql = "INSERT INTO inventory (product_id, quantity_on_hand) VALUES (?, 0)";
                $invStmt = $this->conn->prepare($invSql);
                $invStmt->bind_param('i', $productId);
                $invStmt->execute();
                
                $this->conn->commit();
                return $productId;
            } else {
                throw new Exception("Failed to insert product: " . $this->conn->error);
            }
            $this->conn->rollback();
            return false;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    public function update($id, $categoryIds, $productName, $description, $costPrice, $sellingPrice, $supplierId = null, $imgPath = null, $isActive = null) {
        $this->conn->begin_transaction();
        try {
            $updates = [];
            $params = [];
            $types = '';
            
            $updates[] = "product_name = ?";
            $params[] = $productName;
            $types .= 's';
            
            $updates[] = "description = ?";
            $params[] = $description;
            $types .= 's';
            
            $updates[] = "cost_price = ?";
            $params[] = $costPrice;
            $types .= 'd';
            
            $updates[] = "selling_price = ?";
            $params[] = $sellingPrice;
            $types .= 'd';
            
            $updates[] = "supplier_id = ?";
            $params[] = $supplierId;
            $types .= 'i';
            
            if ($imgPath !== null) {
                $updates[] = "img_path = ?";
                $params[] = $imgPath;
                $types .= 's';
            }
            
            if ($isActive !== null) {
                $updates[] = "is_active = ?";
                $params[] = $isActive;
                $types .= 'i';
            }
            
            $params[] = $id;
            $types .= 'i';
            
            $sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            // Update categories
            $delSql = "DELETE FROM product_categories WHERE product_id = ?";
            $delStmt = $this->conn->prepare($delSql);
            $delStmt->bind_param('i', $id);
            $delStmt->execute();
            
            if (!empty($categoryIds) && is_array($categoryIds)) {
                foreach ($categoryIds as $categoryId) {
                    $catSql = "INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)";
                    $catStmt = $this->conn->prepare($catSql);
                    $catStmt->bind_param('ii', $id, $categoryId);
                    $catStmt->execute();
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    public function getWithCategory() {
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand, 
                       GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
                FROM products p 
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 
                GROUP BY p.id
                ORDER BY p.created_at DESC";
        $result = $this->conn->query($sql);
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process category data
        foreach ($products as &$product) {
            $product['categories'] = [];
            if (!empty($product['category_names'])) {
                $names = explode(', ', $product['category_names']);
                $ids = explode(',', $product['category_ids']);
                $product['categories'] = array_combine($ids, $names);
            }
            unset($product['category_names'], $product['category_ids']);
        }
        
        return $products;
    }
    
    public function searchAndSortProducts($search = '', $sortBy = 'created_at', $sortOrder = 'DESC') {
        $allowedColumns = ['product_name', 'supplier_name', 'cost_price', 'selling_price', 'quantity_on_hand', 'created_at', 'is_active'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'created_at';
        }
        
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sortColumn = 'p.' . $sortBy;
        if ($sortBy === 'supplier_name') {
            $sortColumn = 's.supplier_name';
        } elseif ($sortBy === 'quantity_on_hand') {
            $sortColumn = 'i.quantity_on_hand';
        }
        
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand,
                       GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
                FROM products p 
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN inventory i ON p.id = i.product_id";
        
        $whereConditions = [];
        $params = [];
        $types = '';
        
        if (!empty($search)) {
            $whereConditions[] = "(p.product_name LIKE ? OR p.description LIKE ? OR c.category_name LIKE ? OR s.supplier_name LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $types .= 'ssss';
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $sql .= " GROUP BY p.id ORDER BY $sortColumn $sortOrder";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process category data
        foreach ($products as &$product) {
            $product['categories'] = [];
            if (!empty($product['category_names'])) {
                $names = explode(', ', $product['category_names']);
                $ids = explode(',', $product['category_ids']);
                $product['categories'] = array_combine($ids, $names);
            }
            $product['category_name'] = $product['category_names'];
            unset($product['category_names'], $product['category_ids']);
        }
        
        return $products;
    }
    
    public function getActiveProducts() {
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand,
                       GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
                FROM products p 
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 AND i.quantity_on_hand > 0 
                GROUP BY p.id
                ORDER BY p.created_at DESC";
        $result = $this->conn->query($sql);
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process category data
        foreach ($products as &$product) {
            $product['categories'] = [];
            if (!empty($product['category_names'])) {
                $names = explode(', ', $product['category_names']);
                $ids = explode(',', $product['category_ids']);
                $product['categories'] = array_combine($ids, $names);
            }
            $product['category_name'] = $product['category_names'];
            unset($product['category_names'], $product['category_ids']);
        }
        
        return $products;
    }
    
    public function getActiveProductsPaginated($limit = 9, $offset = 0) {
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand,
                       GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
                FROM products p 
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 AND i.quantity_on_hand > 0 
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process category data
        foreach ($products as &$product) {
            $product['categories'] = [];
            if (!empty($product['category_names'])) {
                $names = explode(', ', $product['category_names']);
                $ids = explode(',', $product['category_ids']);
                $product['categories'] = array_combine($ids, $names);
            }
            $product['category_name'] = $product['category_names'];
            unset($product['category_names'], $product['category_ids']);
        }
        
        return $products;
    }
    
    public function countActiveProducts() {
        $sql = "SELECT COUNT(*) 
                FROM products p 
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.is_active = 1 AND i.quantity_on_hand > 0";
        $result = $this->conn->query($sql);
        return $result->fetch_row()[0];
    }
    
    public function getByCategory($categoryId) {
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand,
                       GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
                FROM products p 
                INNER JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN product_categories pc2 ON p.id = pc2.product_id
                LEFT JOIN categories c ON pc2.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE pc.category_id = ? AND p.is_active = 1 AND i.quantity_on_hand > 0 
                GROUP BY p.id
                ORDER BY p.product_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process category data
        foreach ($products as &$product) {
            $product['categories'] = [];
            if (!empty($product['category_names'])) {
                $names = explode(', ', $product['category_names']);
                $ids = explode(',', $product['category_ids']);
                $product['categories'] = array_combine($ids, $names);
            }
            $product['category_name'] = $product['category_names'];
            unset($product['category_names'], $product['category_ids']);
        }
        
        return $products;
    }
    
    public function getByCategoryPaginated($categoryId, $limit = 9, $offset = 0) {
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand,
                       GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
                FROM products p 
                INNER JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN product_categories pc2 ON p.id = pc2.product_id
                LEFT JOIN categories c ON pc2.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE pc.category_id = ? AND p.is_active = 1 AND i.quantity_on_hand > 0 
                GROUP BY p.id
                ORDER BY p.product_name ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $categoryId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process category data
        foreach ($products as &$product) {
            $product['categories'] = [];
            if (!empty($product['category_names'])) {
                $names = explode(', ', $product['category_names']);
                $ids = explode(',', $product['category_ids']);
                $product['categories'] = array_combine($ids, $names);
            }
            $product['category_name'] = $product['category_names'];
            unset($product['category_names'], $product['category_ids']);
        }
        
        return $products;
    }
    
    public function countByCategory($categoryId) {
        $sql = "SELECT COUNT(*) 
                FROM products p 
                INNER JOIN inventory i ON p.id = i.product_id 
                WHERE p.category_id = ? AND p.is_active = 1 AND i.quantity_on_hand > 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_row()[0];
    }
    
    public function search($keyword) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand,
                       GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
                FROM products p 
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE (p.product_name LIKE ? OR p.description LIKE ?) AND p.is_active = 1 
                GROUP BY p.id
                ORDER BY p.product_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process category data
        foreach ($products as &$product) {
            $product['categories'] = [];
            if (!empty($product['category_names'])) {
                $names = explode(', ', $product['category_names']);
                $ids = explode(',', $product['category_ids']);
                $product['categories'] = array_combine($ids, $names);
            }
            $product['category_name'] = $product['category_names'];
            unset($product['category_names'], $product['category_ids']);
        }
        
        return $products;
    }
    
    public function searchPaginated($keyword, $limit = 9, $offset = 0) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT p.*, s.supplier_name, i.quantity_on_hand,
                       GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
                FROM products p 
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE (p.product_name LIKE ? OR p.description LIKE ?) AND p.is_active = 1 
                GROUP BY p.id
                ORDER BY p.product_name ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssii', $searchTerm, $searchTerm, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        
        // Process category data
        foreach ($products as &$product) {
            $product['categories'] = [];
            if (!empty($product['category_names'])) {
                $names = explode(', ', $product['category_names']);
                $ids = explode(',', $product['category_ids']);
                $product['categories'] = array_combine($ids, $names);
            }
            $product['category_name'] = $product['category_names'];
            unset($product['category_names'], $product['category_ids']);
        }
        
        return $products;
    }
    
    public function countSearch($keyword) {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT COUNT(*) 
                FROM products p 
                LEFT JOIN inventory i ON p.id = i.product_id 
                WHERE (p.product_name LIKE ? OR p.description LIKE ?) AND p.is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_row()[0];
    }
    
    public function updateInventory($productId, $quantity) {
        $sql = "UPDATE inventory SET quantity_on_hand = ? WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $quantity, $productId);
        return $stmt->execute();
    }
    
    public function getInventory($productId) {
        $sql = "SELECT quantity_on_hand FROM inventory WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_row()[0] ?? 0;
    }
    
    public function findByIdWithDetails($productId) {
        $sql = "SELECT p.*, s.supplier_name,
                       GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') as category_names,
                       GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ',') as category_ids
                FROM products p 
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id 
                LEFT JOIN suppliers s ON p.supplier_id = s.id 
                WHERE p.id = ? 
                GROUP BY p.id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if ($product) {
            $product['categories'] = [];
            if (!empty($product['category_names'])) {
                $names = explode(', ', $product['category_names']);
                $ids = explode(',', $product['category_ids']);
                $product['categories'] = array_combine($ids, $names);
            }
            $product['category_name'] = $product['category_names'];
            unset($product['category_names'], $product['category_ids']);
        }
        
        return $product;
    }
    
    public function hasOrderItems($productId) {
        $sql = "SELECT COUNT(*) FROM order_items WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_row()[0] > 0;
    }
    
    public function hasActiveOrderItems($productId) {
        $sql = "SELECT COUNT(*) 
                FROM order_items oi 
                INNER JOIN orders o ON oi.order_id = o.id 
                WHERE oi.product_id = ? 
                AND o.order_status NOT IN ('completed', 'cancelled')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_row()[0] > 0;
    }
    
    public function deleteWithOrderItems($productId) {
        $this->db->beginTransaction();
        try {
            $product = $this->findById($productId);
            $imagePath = $product['img_path'] ?? null;
            $productImages = $this->getProductImages($productId);
            
            $sql = "UPDATE order_items SET 
                    product_name = COALESCE(product_name, ?),
                    product_image = COALESCE(product_image, ?)
                    WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssi', $product['product_name'], $product['img_path'], $productId);
            $stmt->execute();
            
            foreach ($productImages as $image) {
                $fullImagePath = __DIR__ . '/../../public/uploads/' . $image['image_path'];
                if (file_exists($fullImagePath)) {
                    @unlink($fullImagePath);
                }
            }
            
            $sql = "DELETE FROM product_images WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            
            $sql = "DELETE FROM inventory WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            
            if (!empty($imagePath)) {
                $fullImagePath = __DIR__ . '/../../public/uploads/' . $imagePath;
                if (file_exists($fullImagePath)) {
                    @unlink($fullImagePath);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    public function getProductImages($productId) {
        $sql = "SELECT * FROM product_images 
                WHERE product_id = ? 
                ORDER BY is_primary DESC, display_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function addProductImage($productId, $imagePath, $displayOrder = 0, $isPrimary = false) {
        if ($isPrimary) {
            $this->unsetPrimaryImage($productId);
        }
        
        $sql = "INSERT INTO product_images (product_id, image_path, display_order, is_primary) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $isPrimaryInt = (int)$isPrimary;
        $stmt->bind_param('isii', $productId, $imagePath, $displayOrder, $isPrimaryInt);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    public function deleteProductImage($imageId) {
        $sql = "SELECT image_path FROM product_images WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $imageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();
        
        if (!$image) {
            return false;
        }
        
        $sql = "DELETE FROM product_images WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $imageId);
        
        if ($stmt->execute()) {
            $fullImagePath = __DIR__ . '/../../public/uploads/' . $image['image_path'];
            if (file_exists($fullImagePath)) {
                @unlink($fullImagePath);
            }
            return true;
        }
        return false;
    }
    
    public function setPrimaryImage($imageId) {
        $sql = "SELECT product_id FROM product_images WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $imageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();
        
        if (!$image) {
            return false;
        }
        
        $this->unsetPrimaryImage($image['product_id']);
        
        $sql = "UPDATE product_images SET is_primary = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $imageId);
        return $stmt->execute();
    }
    
    private function unsetPrimaryImage($productId) {
        $sql = "UPDATE product_images SET is_primary = 0 WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        return $stmt->execute();
    }
    
    public function updateImageOrder($imageId, $displayOrder) {
        $sql = "UPDATE product_images SET display_order = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $displayOrder, $imageId);
        return $stmt->execute();
    }

    public function updateRating($productId, $averageRating, $reviewCount) {
        $sql = "UPDATE products SET average_rating = ?, review_count = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('dii', $averageRating, $reviewCount, $productId);
        return $stmt->execute();
    }

    public function hasUserReviewedProduct($userId, $productId) {
        $sql = "SELECT COUNT(*) FROM reviews WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_row()[0] > 0;
    }

    public function getProductCategories($productId) {
        $sql = "SELECT c.id, c.category_name 
                FROM categories c 
                INNER JOIN product_categories pc ON c.id = pc.category_id 
                WHERE pc.product_id = ? 
                ORDER BY c.category_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function setProductCategories($productId, $categoryIds) {
        $this->conn->begin_transaction();
        try {
            // Delete existing categories
            $delSql = "DELETE FROM product_categories WHERE product_id = ?";
            $delStmt = $this->conn->prepare($delSql);
            $delStmt->bind_param('i', $productId);
            $delStmt->execute();
            
            // Insert new categories
            if (!empty($categoryIds) && is_array($categoryIds)) {
                foreach ($categoryIds as $categoryId) {
                    $insSql = "INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)";
                    $insStmt = $this->conn->prepare($insSql);
                    $insStmt->bind_param('ii', $productId, $categoryId);
                    $insStmt->execute();
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
