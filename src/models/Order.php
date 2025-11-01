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
        
        // Get product name and image to store as snapshot
        $productSql = "SELECT product_name, img_path FROM products WHERE id = ?";
        $productStmt = mysqli_prepare($this->conn, $productSql);
        mysqli_stmt_bind_param($productStmt, 'i', $productId);
        mysqli_stmt_execute($productStmt);
        $productResult = mysqli_stmt_get_result($productStmt);
        $product = mysqli_fetch_assoc($productResult);
        
        $productName = $product['product_name'] ?? 'Unknown Product';
        $productImage = $product['img_path'] ?? '';
        
        $sql = "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, unit_price, item_total) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'iissidd', $orderId, $productId, $productName, $productImage, $quantity, $unitPrice, $itemTotal);
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
        // Use snapshot data from order_items, with fallback to products table if product still exists
        $sql = "SELECT oi.*, 
                COALESCE(oi.product_name, p.product_name, 'Deleted Product') as product_name,
                COALESCE(oi.product_image, p.img_path, '') as img_path
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
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
        $sql = "SELECT o.*, u.email, COUNT(oi.id) as item_count
                FROM orders o
                INNER JOIN users u ON o.customer_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.order_status = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $status);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function searchOrders($search, $status = '') {
        $searchTerm = '%' . $search . '%';
        
        $sql = "SELECT o.*, u.email, COUNT(oi.id) as item_count
                FROM orders o
                INNER JOIN users u ON o.customer_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE (o.order_number LIKE ? 
                    OR u.email LIKE ? 
                    OR CAST(o.total_amount AS CHAR) LIKE ?)";
        
        if (!empty($status)) {
            $sql .= " AND o.order_status = ?";
        }
        
        $sql .= " GROUP BY o.id
                  ORDER BY o.created_at DESC";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!empty($status)) {
            mysqli_stmt_bind_param($stmt, 'ssss', $searchTerm, $searchTerm, $searchTerm, $status);
        } else {
            mysqli_stmt_bind_param($stmt, 'sss', $searchTerm, $searchTerm, $searchTerm);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function markAsCompleted($orderId, $customerId) {
    // Verify that the order belongs to the customer and is delivered or shipped
    // Allow customers to confirm receipt if the order is in 'delivered' or 'shipped' state
    $sql = "UPDATE orders 
        SET order_status = 'completed' 
        WHERE id = ? AND customer_id = ? AND order_status IN ('delivered','shipped')";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $orderId, $customerId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function cancelOrder($orderId) {
        $sql = "UPDATE orders SET order_status = 'cancelled' WHERE id = ? AND order_status = 'pending'";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getCompletedOrdersCount() {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'completed'";
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['count'] ?? 0;
    }
    
    public function getCompletedOrdersTotal() {
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE order_status = 'completed'";
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    }
    
    // Sales Report Methods
    public function getSalesByPeriod($startDate, $endDate) {
        $sql = "SELECT 
                COUNT(*) as order_count,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as avg_order_value,
                SUM(subtotal) as subtotal,
                SUM(shipping_cost) as shipping_total,
                SUM(tax_amount) as tax_total
                FROM orders 
                WHERE order_status = 'completed' 
                AND created_at >= ? 
                AND created_at <= ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function getDailySales($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';
        return $this->getSalesByPeriod($startDate, $endDate);
    }
    
    public function getWeeklySales() {
        $startDate = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $endDate = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        return $this->getSalesByPeriod($startDate, $endDate);
    }
    
    public function getMonthlySales($month = null, $year = null) {
        if (!$month) $month = date('m');
        if (!$year) $year = date('Y');
        $startDate = "$year-$month-01 00:00:00";
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        return $this->getSalesByPeriod($startDate, $endDate);
    }
    
    public function getYearlySales($year = null) {
        if (!$year) $year = date('Y');
        $startDate = "$year-01-01 00:00:00";
        $endDate = "$year-12-31 23:59:59";
        return $this->getSalesByPeriod($startDate, $endDate);
    }
    
    public function getCustomRangeSales($startDate, $endDate) {
        // Ensure proper datetime format
        $start = date('Y-m-d 00:00:00', strtotime($startDate));
        $end = date('Y-m-d 23:59:59', strtotime($endDate));
        return $this->getSalesByPeriod($start, $end);
    }
    
    public function getTopSellingProducts($startDate = null, $endDate = null, $limit = 10) {
        $sql = "SELECT 
                oi.product_name,
                oi.product_image,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.item_total) as total_revenue,
                COUNT(DISTINCT oi.order_id) as order_count
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE o.order_status = 'completed'";
        
        if ($startDate && $endDate) {
            $sql .= " AND o.created_at >= ? AND o.created_at <= ?";
        }
        
        $sql .= " GROUP BY oi.product_id, oi.product_name, oi.product_image
                  ORDER BY total_quantity DESC
                  LIMIT ?";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($startDate && $endDate) {
            mysqli_stmt_bind_param($stmt, 'ssi', $startDate, $endDate, $limit);
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $limit);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getSalesOrdersList($startDate, $endDate) {
        $sql = "SELECT o.*, u.email, COUNT(oi.id) as item_count
                FROM orders o
                INNER JOIN users u ON o.customer_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.order_status = 'completed'
                AND o.created_at >= ?
                AND o.created_at <= ?
                GROUP BY o.id
                ORDER BY o.created_at DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}