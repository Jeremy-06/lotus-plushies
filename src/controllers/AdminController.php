<?php

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Validation.php';
require_once __DIR__ . '/../helpers/CSRF.php';
require_once __DIR__ . '/../helpers/ErrorHandler.php';
require_once __DIR__ . '/../helpers/FileUpload.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Expense.php';

class AdminController {
    private $productModel;
    private $categoryModel;
    private $orderModel;
    private $userModel;
    private $expenseModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->expenseModel = new Expense();
    }

    public function dashboard() {
        $orders = $this->orderModel->getAllOrders();
        $products = $this->productModel->getWithCategory();
        $customers = $this->userModel->getAllCustomers();
        $totalProducts = count($products);
        $totalOrders = count($orders);
        $totalCustomers = count($customers);
        $pendingOrders = 0;
        foreach ($orders as $o) {
            if (($o['order_status'] ?? '') === 'pending') {
                $pendingOrders++;
            }
        }
        
        // Get completed orders (sales)
        $completedOrders = $this->orderModel->getCompletedOrdersCount();
        $totalSales = $this->orderModel->getCompletedOrdersTotal();
        
        include __DIR__ . '/../views/admin/dashboard.php';
    }

    public function products() {
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort'] ?? 'created_at';
        $sortOrder = $_GET['order'] ?? 'DESC';
        
        $products = $this->productModel->searchAndSortProducts($search, $sortBy, $sortOrder);
        $categories = $this->categoryModel->getActive();
        include __DIR__ . '/../views/admin/products.php';
    }

    public function createProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=create_product');
                exit();
            }
            $validator = new Validation();
            $validator->required('product_name', $_POST['product_name'] ?? '')
                      ->required('category_id', $_POST['category_id'] ?? '')
                      ->required('cost_price', $_POST['cost_price'] ?? '')
                      ->required('selling_price', $_POST['selling_price'] ?? '');

            if ($validator->hasErrors()) {
                Session::setFlash('message', 'Please fill in all required fields');
                header('Location: admin.php?page=create_product');
                exit();
            }

            // Create product first to get the product ID
            $imgPath = '';
            
            try {
                $created = $this->productModel->create(
                    intval($_POST['category_id']),
                    trim($_POST['product_name']),
                    trim($_POST['description'] ?? ''),
                    floatval($_POST['cost_price']),
                    floatval($_POST['selling_price']),
                    $imgPath
                );

                if (!$created) {
                    throw new Exception('Failed to create product in database');
                }
                
                // Now handle image upload with the product ID
                if (!empty($_FILES['img_path']['name'])) {
                    $uploadResult = FileUpload::uploadProductImage($_FILES['img_path'], $created);
                    if ($uploadResult['success']) {
                        $imgPath = 'products/' . $uploadResult['filename'];
                        // Update product with image path
                        $this->productModel->update(
                            $created,
                            intval($_POST['category_id']),
                            trim($_POST['product_name']),
                            trim($_POST['description'] ?? ''),
                            floatval($_POST['cost_price']),
                            floatval($_POST['selling_price']),
                            $imgPath
                        );
                    } else {
                        // Log error but don't fail product creation
                        ErrorHandler::log('Product image upload failed: ' . $uploadResult['error'], 'WARNING', ['product_id' => $created]);
                    }
                }

                // Apply initial inventory if provided
                if (isset($_POST['quantity']) && is_numeric($_POST['quantity'])) {
                    $quantity = intval($_POST['quantity']);
                    $this->productModel->updateInventory($created, $quantity);
                    
                    // Automatically create expense entry for initial stock
                    if ($quantity > 0) {
                        $costPrice = floatval($_POST['cost_price']);
                        $totalCost = $costPrice * $quantity;
                        $productName = trim($_POST['product_name']);
                        $description = "Initial stock for product: {$productName} ({$quantity} units @ ₱{$costPrice} each)";
                        
                        $this->expenseModel->create(
                            date('Y-m-d'), // today's date
                            'Inventory',
                            $description,
                            $totalCost,
                            'cash',
                            null, // receipt_number
                            null, // vendor_name
                            'Auto-generated expense for initial product stock',
                            Session::get('user_id')
                        );
                    }
                }
                Session::setFlash('success', 'Product created and expense recorded');
                header('Location: admin.php?page=products');
            } catch (Exception $e) {
                ErrorHandler::log('Product creation failed: ' . $e->getMessage(), 'ERROR');
                Session::setFlash('message', 'Failed to create product');
                header('Location: admin.php?page=create_product');
            }
            exit();
        }
        $categories = $this->categoryModel->getActive();
        include __DIR__ . '/../views/admin/product_create.php';
    }

    public function updateProduct() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=products');
            exit();
        }
        $id = intval($_GET['id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=edit_product&id=' . $id);
                exit();
            }
            
            // Get current product data for old image
            $currentProduct = $this->productModel->findById($id);
            $imgPath = null;
            
            if (!empty($_FILES['img_path']['name'])) {
                $oldImagePath = $currentProduct['img_path'] ?? null;
                $uploadResult = FileUpload::uploadProductImage($_FILES['img_path'], $id, $oldImagePath);
                if ($uploadResult['success']) {
                    $imgPath = 'products/' . $uploadResult['filename'];
                } else {
                    Session::setFlash('message', $uploadResult['error']);
                    header('Location: admin.php?page=edit_product&id=' . $id);
                    exit();
                }
            }
            
            try {
                $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
                $ok = $this->productModel->update(
                    $id,
                    intval($_POST['category_id']),
                    trim($_POST['product_name']),
                    trim($_POST['description'] ?? ''),
                    floatval($_POST['cost_price']),
                    floatval($_POST['selling_price']),
                    $imgPath,
                    $isActive
                );
                
                if (!$ok) {
                    throw new Exception('Failed to update product');
                }
                
                if (isset($_POST['quantity']) && is_numeric($_POST['quantity'])) {
                    $newQuantity = intval($_POST['quantity']);
                    $oldQuantity = $this->productModel->getInventory($id);
                    
                    // Update inventory
                    $this->productModel->updateInventory($id, $newQuantity);
                    
                    // If stock increased, record as expense
                    if ($newQuantity > $oldQuantity) {
                        $quantityAdded = $newQuantity - $oldQuantity;
                        $costPrice = floatval($_POST['cost_price']);
                        $totalCost = $costPrice * $quantityAdded;
                        $productName = trim($_POST['product_name']);
                        $description = "Restocking: {$productName} (+{$quantityAdded} units @ ₱{$costPrice} each)";
                        
                        $this->expenseModel->create(
                            date('Y-m-d'), // today's date
                            'Inventory',
                            $description,
                            $totalCost,
                            'cash',
                            null, // receipt_number
                            null, // vendor_name
                            'Auto-generated expense for product restocking',
                            Session::get('user_id')
                        );
                    }
                }
                Session::setFlash('success', 'Product updated and expense recorded (if restocked)');
            } catch (Exception $e) {
                ErrorHandler::log('Product update failed: ' . $e->getMessage(), 'ERROR', ['product_id' => $id]);
                Session::setFlash('message', 'Failed to update product');
            }
            header('Location: admin.php?page=products');
            exit();
        }
        $product = $this->productModel->findById($id);
        $categories = $this->categoryModel->getActive();
        $inventory = $this->productModel->getInventory($id);
        include __DIR__ . '/../views/admin/product_edit.php';
    }

    public function deleteProduct() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=products');
            exit();
        }
        $id = intval($_GET['id']);
        
        // Check if product has active order items (pending, processing, or shipped)
        if ($this->productModel->hasActiveOrderItems($id)) {
            Session::setFlash('message', 'Cannot delete product. This product has active orders (pending, processing, or shipped). Please wait until all orders are completed or cancelled.');
            header('Location: admin.php?page=products');
            exit();
        }
        
        // Use custom delete method that handles order items from completed orders
        if ($this->productModel->deleteWithOrderItems($id)) {
            Session::setFlash('success', 'Product deleted successfully');
        } else {
            Session::setFlash('message', 'Failed to delete product');
        }
        header('Location: admin.php?page=products');
        exit();
    }

    public function orders() {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        // Get filtered orders for display
        if (!empty($search)) {
            $orders = $this->orderModel->searchOrders($search, $status);
        } elseif (!empty($status)) {
            $orders = $this->orderModel->getOrdersByStatus($status);
        } else {
            $orders = $this->orderModel->getAllOrders();
        }
        
        // Get all orders for badge counts
        $allOrders = $this->orderModel->getAllOrders();
        
        include __DIR__ . '/../views/admin/orders.php';
    }

    public function orderDetail() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=orders');
            exit();
        }
        $orderId = intval($_GET['id']);
        $order = $this->orderModel->getOrderDetails($orderId);
        $orderItems = $this->orderModel->getOrderItems($orderId);
        include __DIR__ . '/../views/admin/order_detail.php';
    }

    public function updateOrderStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=orders');
                exit();
            }
            $orderId = intval($_POST['order_id'] ?? 0);
            $status = $_POST['status'] ?? 'pending';
            if ($orderId && $this->orderModel->updateStatus($orderId, $status)) {
                Session::setFlash('success', 'Order status updated');
            } else {
                Session::setFlash('message', 'Failed to update order');
            }
            header('Location: admin.php?page=order_detail&id=' . $orderId);
            exit();
        }
        header('Location: admin.php?page=orders');
        exit();
    }

    public function customers() {
        $customers = $this->userModel->getAllCustomers();
        include __DIR__ . '/../views/admin/customers.php';
    }

    public function users() {
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort'] ?? 'created_at';
        $sortOrder = $_GET['order'] ?? 'DESC';
        $roleFilter = $_GET['role'] ?? '';
        
        $users = $this->userModel->searchAndSortUsers($search, $sortBy, $sortOrder, $roleFilter);
        include __DIR__ . '/../views/admin/users.php';
    }

    public function editUser() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=users');
            exit();
        }
        $id = intval($_GET['id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=edit_user&id=' . $id);
                exit();
            }
            $role = $_POST['role'] === 'admin' ? 'admin' : 'customer';
            if ($this->userModel->updateRole($id, $role)) {
                Session::setFlash('success', 'User role updated');
            } else {
                Session::setFlash('message', 'Failed to update user');
            }
            header('Location: admin.php?page=users');
            exit();
        }
        // Simple fetch by id using BaseModel
        $user = $this->userModel->findById($id);
        include __DIR__ . '/../views/admin/user_edit.php';
    }

    public function deleteUser() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=users');
            exit();
        }
        $id = intval($_GET['id']);
        // Prevent self-deletion for safety
        if ($id === Session::getUserId()) {
            Session::setFlash('message', 'You cannot delete your own account');
            header('Location: admin.php?page=users');
            exit();
        }
        if ($this->userModel->delete($id)) {
            Session::setFlash('success', 'User deleted');
        } else {
            Session::setFlash('message', 'Failed to delete user');
        }
        header('Location: admin.php?page=users');
        exit();
    }

    // Category CRUD Methods
    public function categories() {
        $pageTitle = 'Manage Categories - Admin';
        $categories = $this->categoryModel->getAll();
        include __DIR__ . '/../views/admin/categories.php';
    }

    public function createCategory() {
        $pageTitle = 'Create Category - Admin';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=categories');
                exit();
            }
            
            $categoryName = trim($_POST['category_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            if (empty($categoryName)) {
                Session::setFlash('message', 'Category name is required');
                header('Location: admin.php?page=create_category');
                exit();
            }
            
            if ($categoryId = $this->categoryModel->create($categoryName, $description)) {
                // Update is_active after creation
                $this->categoryModel->update($categoryId, $categoryName, $description, $isActive);
                Session::setFlash('success', 'Category created successfully');
                header('Location: admin.php?page=categories');
                exit();
            } else {
                Session::setFlash('message', 'Failed to create category');
            }
        }
        
        include __DIR__ . '/../views/admin/category_create.php';
    }

    public function editCategory() {
        $pageTitle = 'Edit Category - Admin';
        
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=categories');
            exit();
        }
        
        $id = intval($_GET['id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=edit_category&id=' . $id);
                exit();
            }
            
            $categoryName = trim($_POST['category_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            if (empty($categoryName)) {
                Session::setFlash('message', 'Category name is required');
                header('Location: admin.php?page=edit_category&id=' . $id);
                exit();
            }
            
            if ($this->categoryModel->update($id, $categoryName, $description, $isActive)) {
                Session::setFlash('success', 'Category updated successfully');
                header('Location: admin.php?page=categories');
                exit();
            } else {
                Session::setFlash('message', 'Failed to update category');
            }
        }
        
        $category = $this->categoryModel->findById($id);
        if (!$category) {
            Session::setFlash('message', 'Category not found');
            header('Location: admin.php?page=categories');
            exit();
        }
        
        include __DIR__ . '/../views/admin/category_edit.php';
    }

    public function deleteCategory() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=categories');
            exit();
        }
        
        $id = intval($_GET['id']);
        
        if ($this->categoryModel->delete($id)) {
            Session::setFlash('success', 'Category deleted successfully');
        } else {
            Session::setFlash('message', 'Cannot delete category. It may be in use by products.');
        }
        
        header('Location: admin.php?page=categories');
        exit();
    }
    
    public function salesReport() {
        // Prevent caching to ensure fresh data
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        // Get report type and date parameters
        $reportType = $_GET['type'] ?? 'daily';
        
        // Only use custom dates if we're in custom report mode
        $customStart = ($reportType === 'custom' && isset($_GET['start_date'])) ? $_GET['start_date'] : '';
        $customEnd = ($reportType === 'custom' && isset($_GET['end_date'])) ? $_GET['end_date'] : '';
        
        // Initialize sales data
        $salesData = null;
        $reportTitle = '';
        $reportPeriod = '';
        $startDate = '';
        $endDate = '';
        $date = null; // Initialize date variable for the view
        
        switch ($reportType) {
            case 'daily':
                // Always use current date if no specific date is provided
                $date = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                $salesData = $this->orderModel->getDailySales($date);
                $reportTitle = 'Daily Sales Report';
                $reportPeriod = date('F d, Y', strtotime($date));
                $startDate = $date . ' 00:00:00';
                $endDate = $date . ' 23:59:59';
                break;
                
            case 'weekly':
                $salesData = $this->orderModel->getWeeklySales();
                $reportTitle = 'Weekly Sales Report';
                $weekStart = date('M d, Y', strtotime('monday this week'));
                $weekEnd = date('M d, Y', strtotime('sunday this week'));
                $reportPeriod = "$weekStart - $weekEnd";
                $startDate = date('Y-m-d 00:00:00', strtotime('monday this week'));
                $endDate = date('Y-m-d 23:59:59', strtotime('sunday this week'));
                break;
                
            case 'monthly':
                $month = $_GET['month'] ?? date('m');
                $year = $_GET['year'] ?? date('Y');
                $salesData = $this->orderModel->getMonthlySales($month, $year);
                $reportTitle = 'Monthly Sales Report';
                $reportPeriod = date('F Y', strtotime("$year-$month-01"));
                $startDate = "$year-$month-01 00:00:00";
                $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
                break;
                
            case 'yearly':
                $year = $_GET['year'] ?? date('Y');
                $salesData = $this->orderModel->getYearlySales($year);
                $reportTitle = 'Yearly Sales Report';
                $reportPeriod = $year;
                $startDate = "$year-01-01 00:00:00";
                $endDate = "$year-12-31 23:59:59";
                break;
                
            case 'custom':
                if ($customStart && $customEnd) {
                    $salesData = $this->orderModel->getCustomRangeSales($customStart, $customEnd);
                    $reportTitle = 'Custom Range Sales Report';
                    $reportPeriod = date('M d, Y', strtotime($customStart)) . ' - ' . date('M d, Y', strtotime($customEnd));
                    $startDate = date('Y-m-d 00:00:00', strtotime($customStart));
                    $endDate = date('Y-m-d 23:59:59', strtotime($customEnd));
                } else {
                    // Default to today if no dates provided
                    $salesData = $this->orderModel->getDailySales();
                    $reportTitle = 'Sales Report';
                    $reportPeriod = 'Please select a date range';
                    $startDate = date('Y-m-d 00:00:00');
                    $endDate = date('Y-m-d 23:59:59');
                }
                break;
        }
        
        // Get top selling products for the period
        $topProducts = $this->orderModel->getTopSellingProducts($startDate, $endDate, 10);
        
        // Get orders list for the period
        $orders = $this->orderModel->getSalesOrdersList($startDate, $endDate);
        
        // Get expenses for the period
        $expenseData = $this->expenseModel->getExpensesByPeriod(date('Y-m-d', strtotime($startDate)), date('Y-m-d', strtotime($endDate)));
        $expensesByCategory = $this->expenseModel->getExpensesByCategoryPeriod(date('Y-m-d', strtotime($startDate)), date('Y-m-d', strtotime($endDate)));
        
        // Calculate profit
        $totalRevenue = $salesData['total_sales'] ?? 0;
        $totalExpenses = $expenseData['total_expenses'] ?? 0;
        $netProfit = $totalRevenue - $totalExpenses;
        
        include __DIR__ . '/../views/admin/sales_report.php';
    }
    
    // Expense Management Methods
    public function expenses() {
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort'] ?? 'expense_date';
        $sortOrder = $_GET['order'] ?? 'DESC';
        $categoryFilter = $_GET['category'] ?? '';
        
        $expenses = $this->expenseModel->searchAndSort($search, $sortBy, $sortOrder, $categoryFilter);
        $categories = $this->expenseModel->getCategories();
        
        include __DIR__ . '/../views/admin/expenses.php';
    }
    
    public function createExpense() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=create_expense');
                exit();
            }
            
            $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $paymentMethod = $_POST['payment_method'] ?? 'cash';
            $receiptNumber = trim($_POST['receipt_number'] ?? '');
            $vendorName = trim($_POST['vendor_name'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            $createdBy = Session::getUserId();
            
            if (empty($category) || empty($description) || $amount <= 0) {
                Session::setFlash('message', 'Please fill in all required fields with valid data');
                header('Location: admin.php?page=create_expense');
                exit();
            }
            
            if ($this->expenseModel->create($expenseDate, $category, $description, $amount, $paymentMethod, $receiptNumber, $vendorName, $notes, $createdBy)) {
                Session::setFlash('success', 'Expense recorded successfully');
                header('Location: admin.php?page=expenses');
            } else {
                Session::setFlash('message', 'Failed to record expense');
                header('Location: admin.php?page=create_expense');
            }
            exit();
        }
        
        $categories = $this->expenseModel->getCategories();
        include __DIR__ . '/../views/admin/expense_create.php';
    }
    
    public function editExpense() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=expenses');
            exit();
        }
        
        $id = intval($_GET['id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=edit_expense&id=' . $id);
                exit();
            }
            
            $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $paymentMethod = $_POST['payment_method'] ?? 'cash';
            $receiptNumber = trim($_POST['receipt_number'] ?? '');
            $vendorName = trim($_POST['vendor_name'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            if (empty($category) || empty($description) || $amount <= 0) {
                Session::setFlash('message', 'Please fill in all required fields with valid data');
                header('Location: admin.php?page=edit_expense&id=' . $id);
                exit();
            }
            
            if ($this->expenseModel->update($id, $expenseDate, $category, $description, $amount, $paymentMethod, $receiptNumber, $vendorName, $notes)) {
                Session::setFlash('success', 'Expense updated successfully');
                header('Location: admin.php?page=expenses');
            } else {
                Session::setFlash('message', 'Failed to update expense');
                header('Location: admin.php?page=edit_expense&id=' . $id);
            }
            exit();
        }
        
        $expense = $this->expenseModel->findById($id);
        $categories = $this->expenseModel->getCategories();
        
        if (!$expense) {
            Session::setFlash('message', 'Expense not found');
            header('Location: admin.php?page=expenses');
            exit();
        }
        
        include __DIR__ . '/../views/admin/expense_edit.php';
    }
    
    public function deleteExpense() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=expenses');
            exit();
        }
        
        $id = intval($_GET['id']);
        
        if ($this->expenseModel->delete($id)) {
            Session::setFlash('success', 'Expense deleted successfully');
        } else {
            Session::setFlash('message', 'Failed to delete expense');
        }
        
        header('Location: admin.php?page=expenses');
        exit();
    }
    

}