<?php

require_once __DIR__ . '/BaseModel.php';

class Expense extends BaseModel {
    
    protected $table = 'expenses';
    
    public function create($expenseDate, $category, $description, $amount, $paymentMethod = 'cash', $receiptNumber = null, $vendorName = null, $notes = null, $createdBy = null) {
        $sql = "INSERT INTO expenses (expense_date, category, description, amount, payment_method, receipt_number, vendor_name, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssdssssi', $expenseDate, $category, $description, $amount, $paymentMethod, $receiptNumber, $vendorName, $notes, $createdBy);
        
        if (mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        }
        return false;
    }
    
    public function update($id, $expenseDate, $category, $description, $amount, $paymentMethod = 'cash', $receiptNumber = null, $vendorName = null, $notes = null) {
        $sql = "UPDATE expenses 
                SET expense_date = ?, category = ?, description = ?, amount = ?, payment_method = ?, receipt_number = ?, vendor_name = ?, notes = ?
                WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssdssss i', $expenseDate, $category, $description, $amount, $paymentMethod, $receiptNumber, $vendorName, $notes, $id);
        return mysqli_stmt_execute($stmt);
    }
    
    public function getAllExpenses() {
        $sql = "SELECT e.*, u.email as created_by_email 
                FROM expenses e 
                LEFT JOIN users u ON e.created_by = u.id 
                ORDER BY e.expense_date DESC, e.created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getExpensesByDateRange($startDate, $endDate) {
        $sql = "SELECT e.*, u.email as created_by_email 
                FROM expenses e 
                LEFT JOIN users u ON e.created_by = u.id 
                WHERE e.expense_date >= ? AND e.expense_date <= ?
                ORDER BY e.expense_date DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getExpensesByCategory($category) {
        $sql = "SELECT e.*, u.email as created_by_email 
                FROM expenses e 
                LEFT JOIN users u ON e.created_by = u.id 
                WHERE e.category = ?
                ORDER BY e.expense_date DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $category);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getTotalExpenses($startDate = null, $endDate = null) {
        if ($startDate && $endDate) {
            $sql = "SELECT SUM(amount) as total FROM expenses WHERE expense_date >= ? AND expense_date <= ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $sql = "SELECT SUM(amount) as total FROM expenses";
            $result = mysqli_query($this->conn, $sql);
        }
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    }
    
    public function getExpensesByPeriod($startDate, $endDate) {
        $sql = "SELECT 
                COUNT(*) as expense_count,
                SUM(amount) as total_expenses,
                AVG(amount) as avg_expense
                FROM expenses 
                WHERE expense_date >= ? AND expense_date <= ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    
    public function getExpensesByCategoryPeriod($startDate, $endDate) {
        $sql = "SELECT 
                category,
                SUM(amount) as total,
                COUNT(*) as count
                FROM expenses 
                WHERE expense_date >= ? AND expense_date <= ?
                GROUP BY category
                ORDER BY total DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getCategories() {
        $sql = "SELECT DISTINCT category FROM expenses ORDER BY category";
        $result = mysqli_query($this->conn, $sql);
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row['category'];
        }
        return $categories;
    }
    
    public function searchAndSort($search = '', $sortBy = 'expense_date', $sortOrder = 'DESC', $categoryFilter = '') {
        $sql = "SELECT e.*, u.email as created_by_email 
                FROM expenses e 
                LEFT JOIN users u ON e.created_by = u.id 
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        if (!empty($search)) {
            $sql .= " AND (e.description LIKE ? OR e.vendor_name LIKE ? OR e.receipt_number LIKE ?)";
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'sss';
        }
        
        if (!empty($categoryFilter)) {
            $sql .= " AND e.category = ?";
            $params[] = $categoryFilter;
            $types .= 's';
        }
        
        $allowedSorts = ['expense_date', 'category', 'amount', 'created_at'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'expense_date';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql .= " ORDER BY e.$sortBy $sortOrder";
        
        if (!empty($params)) {
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($this->conn, $sql);
        }
        
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
