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
require_once __DIR__ . '/../models/Supplier.php';

class AdminController {
    private $productModel;
    private $categoryModel;
    private $orderModel;
    private $userModel;
    private $expenseModel;
    private $supplierModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->expenseModel = new Expense();
        $this->supplierModel = new Supplier();
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
                // Store form data for repopulation
                Session::set('form_data', $_POST);
                Session::setFlash('message', 'Please fill in all required fields');
                header('Location: admin.php?page=create_product');
                exit();
            }

            // Handle image uploads
            $uploadedImages = [];
            
            if (isset($_FILES['img_path']) && is_array($_FILES['img_path']['name']) && !empty($_FILES['img_path']['name'][0])) {
                $files = $_FILES['img_path'];
                $fileCount = count($files['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    
                    if (!empty($file['name']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                        $uploadedImages[] = $file; // Store file data for later upload
                    }
                }
            }

            $supplierId = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
            
            try {
                // Create product first without image
                $created = $this->productModel->create(
                    intval($_POST['category_id']),
                    trim($_POST['product_name']),
                    trim($_POST['description'] ?? ''),
                    floatval($_POST['cost_price']),
                    floatval($_POST['selling_price']),
                    $supplierId,
                    '' // Empty image path initially
                );

                if (!$created) {
                    throw new Exception('Failed to create product in database');
                }
                
                // Now upload images with the actual product ID
                $primaryImagePath = '';
                if (!empty($uploadedImages)) {
                    foreach ($uploadedImages as $index => $file) {
                        $uploadResult = FileUpload::uploadProductImage($file, $created);
                        if ($uploadResult['success']) {
                            $imagePath = 'products/' . $uploadResult['filename'];
                            $isPrimary = ($index === 0) ? true : false; // First image is primary
                            $this->productModel->addProductImage($created, $imagePath, $index, $isPrimary);
                            
                            // Set primary image path
                            if ($isPrimary) {
                                $primaryImagePath = $imagePath;
                            }
                        } else {
                            // If image upload fails, we still keep the product but log the error
                            ErrorHandler::log('Image upload failed for product ' . $created . ': ' . $uploadResult['error'], 'WARNING');
                        }
                    }
                    
                    // Update product's primary image path
                    if (!empty($primaryImagePath)) {
                        $this->productModel->update(
                            $created,
                            intval($_POST['category_id']),
                            trim($_POST['product_name']),
                            trim($_POST['description'] ?? ''),
                            floatval($_POST['cost_price']),
                            floatval($_POST['selling_price']),
                            $supplierId,
                            $primaryImagePath,
                            1 // is_active
                        );
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
                        
                        // Get supplier name if supplier_id is set
                        $vendorName = null;
                        if ($supplierId) {
                            $supplier = $this->supplierModel->findById($supplierId);
                            $vendorName = $supplier ? $supplier['supplier_name'] : null;
                        }
                        
                        $this->expenseModel->create(
                            date('Y-m-d'), // today's date
                            'Inventory',
                            $description,
                            $totalCost,
                            'cash',
                            null, // receipt_number
                            $vendorName, // vendor_name from supplier
                            'Auto-generated expense for initial product stock',
                            Session::get('user_id'),
                            $supplierId
                        );
                    }
                }
                Session::setFlash('success', 'Product created and expense recorded');
                header('Location: admin.php?page=products');
            } catch (Exception $e) {
                ErrorHandler::log('Product creation failed: ' . $e->getMessage(), 'ERROR');
                // Store form data for repopulation
                Session::set('form_data', $_POST);
                Session::setFlash('message', 'Failed to create product');
                header('Location: admin.php?page=create_product');
            }
            exit();
        }
        $categories = $this->categoryModel->getActive();
        $suppliers = $this->supplierModel->getAll();
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
            
            // Get current product data
            $currentProduct = $this->productModel->findById($id);
            $imgPath = $currentProduct['img_path']; // Keep existing image path
            
            try {
                $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
                $supplierId = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
                $ok = $this->productModel->update(
                    $id,
                    intval($_POST['category_id']),
                    trim($_POST['product_name']),
                    trim($_POST['description'] ?? ''),
                    floatval($_POST['cost_price']),
                    floatval($_POST['selling_price']),
                    $supplierId,
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
                        
                        // Get supplier name if supplier_id is set
                        $vendorName = null;
                        if ($supplierId) {
                            $supplier = $this->supplierModel->findById($supplierId);
                            $vendorName = $supplier ? $supplier['supplier_name'] : null;
                        }
                        
                        $this->expenseModel->create(
                            date('Y-m-d'), // today's date
                            'Inventory',
                            $description,
                            $totalCost,
                            'cash',
                            null, // receipt_number
                            $vendorName, // vendor_name from supplier
                            'Auto-generated expense for product restocking',
                            Session::get('user_id'),
                            $supplierId
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
        $suppliers = $this->supplierModel->getAll();
        $inventory = $this->productModel->getInventory($id);
        $productImages = $this->productModel->getProductImages($id);
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
        $sortBy = $_GET['sort'] ?? 'created_at';
        $sortOrder = $_GET['order'] ?? 'DESC';
        
        // Get filtered orders for display (all from main orders table)
        if (!empty($search)) {
            $orders = $this->orderModel->searchOrdersSorted($search, $status, $sortBy, $sortOrder);
        } elseif (!empty($status)) {
            $orders = $this->orderModel->getOrdersByStatusSorted($status, $sortBy, $sortOrder);
        } else {
            $orders = $this->orderModel->getAllOrdersSorted($sortBy, $sortOrder);
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
        
        // Try to get from regular orders table first
        $order = $this->orderModel->getOrderDetails($orderId);
        
        // If not found, try to get from order_history (archived orders)
        if (!$order) {
            $historyOrders = $this->orderModel->getCompletedOrdersFromHistory(1000);
            foreach ($historyOrders as $ho) {
                if ($ho['id'] == $orderId) {
                    // Parse customer name into first_name and last_name
                    $nameParts = explode(' ', $ho['customer_name'], 2);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = $nameParts[1] ?? '';
                    
                    // Convert history format to orders format
                    $order = [
                        'id' => $ho['id'],
                        'order_number' => 'HIST-' . $ho['order_id'],
                        'customer_name' => $ho['customer_name'],
                        'email' => $ho['customer_email'],  // Map customer_email to email
                        'customer_email' => $ho['customer_email'],
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => null,  // Not stored in history
                        'address' => null,  // Not stored in history
                        'city' => null,  // Not stored in history
                        'postal_code' => null,  // Not stored in history
                        'country' => null,  // Not stored in history
                        'total_amount' => $ho['total_amount'],
                        'created_at' => $ho['created_at'],
                        'order_status' => 'completed'
                    ];
                    // Parse items from JSON and calculate item_total for each
                    $parsedItems = json_decode($ho['items'], true) ?? [];
                    $orderItems = [];
                    foreach ($parsedItems as $item) {
                        $item['item_total'] = $item['quantity'] * $item['unit_price'];
                        // Add display fields for compatibility
                        $item['display_name'] = $item['product_name'] ?? 'Unknown Product';
                        $item['display_image'] = $item['img_path'] ?? '';
                        $item['is_deleted'] = 1;
                        $item['use_placeholder'] = true;
                        $orderItems[] = $item;
                    }
                    break;
                }
            }
        }
        
        // If still not found
        if (!$order) {
            Session::setFlash('message', 'Order not found');
            header('Location: admin.php?page=orders');
            exit();
        }
        
        // Get order items if not already set
        if (!isset($orderItems)) {
            $orderItems = $this->orderModel->getOrderItems($orderId);
            
            // If no items found (all products deleted), try to get from order_history backup
            if (empty($orderItems)) {
                $historyOrders = $this->orderModel->getCompletedOrdersFromHistory(1000);
                foreach ($historyOrders as $ho) {
                    if ($ho['order_id'] == $orderId) {
                        // Parse items from JSON backup
                        $parsedItems = json_decode($ho['items'], true) ?? [];
                        $orderItems = [];
                        foreach ($parsedItems as $item) {
                            $item['item_total'] = $item['quantity'] * $item['unit_price'];
                            // Add display fields for compatibility
                            $item['display_name'] = $item['product_name'] ?? 'Unknown Product';
                            $item['display_image'] = $item['img_path'] ?? '';
                            $item['is_deleted'] = 1; // Mark as deleted since we got from history
                            $item['use_placeholder'] = true; // Show unavailable
                            $orderItems[] = $item;
                        }
                        break;
                    }
                }
            }
        }
        
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

    public function generateReceipt() {
        if (!isset($_GET['id'])) {
            Session::setFlash('message', 'Invalid order');
            header('Location: admin.php?page=orders');
            exit();
        }
        
        $orderId = intval($_GET['id']);
        
        // Try to get from regular orders table first
        $order = $this->orderModel->getOrderDetails($orderId);
        
        // If not found, try to get from order_history (archived orders)
        if (!$order) {
            $historyOrders = $this->orderModel->getCompletedOrdersFromHistory(1000);
            foreach ($historyOrders as $ho) {
                if ($ho['id'] == $orderId) {
                    // Parse customer name into first_name and last_name
                    $nameParts = explode(' ', $ho['customer_name'], 2);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = $nameParts[1] ?? '';
                    
                    // Convert history format to orders format
                    $order = [
                        'id' => $ho['id'],
                        'order_number' => 'HIST-' . $ho['order_id'],
                        'customer_name' => $ho['customer_name'],
                        'email' => $ho['customer_email'],  // Map customer_email to email
                        'customer_email' => $ho['customer_email'],
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => null,  // Not stored in history
                        'address' => null,  // Not stored in history
                        'city' => null,  // Not stored in history
                        'postal_code' => null,  // Not stored in history
                        'country' => null,  // Not stored in history
                        'total_amount' => $ho['total_amount'],
                        'created_at' => $ho['created_at'],
                        'order_status' => 'completed'
                    ];
                    break;
                }
            }
        }
        
        $orderItems = $this->orderModel->getOrderItems($orderId);
        
        // If no items found (all products deleted), try to get from order_history backup
        if (empty($orderItems)) {
            $historyOrders = $this->orderModel->getCompletedOrdersFromHistory(1000);
            foreach ($historyOrders as $ho) {
                if ($ho['order_id'] == $orderId) {
                    // Parse items from JSON backup
                    $parsedItems = json_decode($ho['items'], true) ?? [];
                    $orderItems = [];
                    foreach ($parsedItems as $item) {
                        $item['item_total'] = $item['quantity'] * $item['unit_price'];
                        // Add display fields for compatibility
                        $item['display_name'] = $item['product_name'] ?? 'Unknown Product';
                        $item['display_image'] = $item['img_path'] ?? '';
                        $item['is_deleted'] = 1; // Mark as deleted since we got from history
                        $item['use_placeholder'] = true; // Show unavailable
                        $orderItems[] = $item;
                    }
                    break;
                }
            }
        }
        
        if (!$order) {
            Session::setFlash('message', 'Order not found');
            header('Location: admin.php?page=orders');
            exit();
        }
        
        // Don't generate receipts for cancelled orders
        if ($order['order_status'] === 'cancelled') {
            Session::setFlash('message', 'Cannot generate receipt for cancelled orders');
            header('Location: admin.php?page=order_detail&id=' . $orderId);
            exit();
        }
        
        include __DIR__ . '/../views/admin/receipt.php';
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
        $adminCount = $this->userModel->countAdmins();
        
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
        
        // Check if the user being deleted is an admin
        $userToDelete = $this->userModel->findById($id);
        if ($userToDelete && $userToDelete['role'] === 'admin') {
            // Check if this is the last admin
            $adminCount = $this->userModel->countAdmins();
            if ($adminCount <= 1) {
                Session::setFlash('message', 'Cannot delete the last admin account. At least one admin must remain in the system.');
                header('Location: admin.php?page=users');
                exit();
            }
        }
        
        // Check if user has pending orders
        $pendingOrders = $this->orderModel->getOrdersByCustomerAndStatus($id, ['pending', 'processing', 'shipped', 'delivered']);
        if (!empty($pendingOrders)) {
            Session::setFlash('message', 'Cannot delete user with active orders. Wait until all orders are completed or cancel them first.');
            header('Location: admin.php?page=users');
            exit();
        }
        
        if ($this->userModel->delete($id)) {
            // Force logout the deleted user by destroying their session
            $this->destroyUserSessions($id);
            
            Session::setFlash('success', 'User deleted');
        } else {
            Session::setFlash('message', 'Failed to delete user');
        }
        header('Location: admin.php?page=users');
        exit();
    }
    
    // Helper function to destroy all sessions for a specific user
    private function destroyUserSessions($userId) {
        $sessionPath = session_save_path();
        if (empty($sessionPath)) {
            $sessionPath = sys_get_temp_dir();
        }
        
        // Scan all session files
        $sessionFiles = glob($sessionPath . '/sess_*');
        if ($sessionFiles) {
            foreach ($sessionFiles as $sessionFile) {
                $sessionData = file_get_contents($sessionFile);
                // Check if this session belongs to the deleted user
                if (strpos($sessionData, 'user_id";i:' . $userId . ';') !== false) {
                    // Delete the session file
                    @unlink($sessionFile);
                }
            }
        }
    }

    // Category CRUD Methods
    public function categories() {
        $pageTitle = 'Manage Categories - Admin';
        $sortBy = $_GET['sort'] ?? 'category_name';
        $sortOrder = $_GET['order'] ?? 'ASC';
        
        $categories = $this->categoryModel->getAllSorted($sortBy, $sortOrder);
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
                // Also include archived data from order_history
                $archivedData = $this->orderModel->getSalesReportFromHistory($date, $date);
                if (!empty($archivedData)) {
                    $salesData = array_merge($salesData ?? [], $archivedData);
                }
                $reportTitle = 'Daily Sales Report';
                $reportPeriod = date('F d, Y', strtotime($date));
                $startDate = $date . ' 00:00:00';
                $endDate = $date . ' 23:59:59';
                break;
                
            case 'weekly':
                $salesData = $this->orderModel->getWeeklySales();
                // Include archived data
                $weekStart = date('Y-m-d', strtotime('monday this week'));
                $weekEnd = date('Y-m-d', strtotime('sunday this week'));
                $archivedData = $this->orderModel->getSalesReportFromHistory($weekStart, $weekEnd);
                if (!empty($archivedData)) {
                    $salesData = array_merge($salesData ?? [], $archivedData);
                }
                $reportTitle = 'Weekly Sales Report';
                $reportPeriodStart = date('M d, Y', strtotime('monday this week'));
                $reportPeriodEnd = date('M d, Y', strtotime('sunday this week'));
                $reportPeriod = "$reportPeriodStart - $reportPeriodEnd";
                $startDate = $weekStart . ' 00:00:00';
                $endDate = $weekEnd . ' 23:59:59';
                break;
                
            case 'monthly':
                $month = $_GET['month'] ?? date('m');
                $year = $_GET['year'] ?? date('Y');
                $salesData = $this->orderModel->getMonthlySales($month, $year);
                // Include archived data
                $monthStart = "$year-$month-01";
                $monthEnd = date('Y-m-t', strtotime($monthStart));
                $archivedData = $this->orderModel->getSalesReportFromHistory($monthStart, $monthEnd);
                if (!empty($archivedData)) {
                    $salesData = array_merge($salesData ?? [], $archivedData);
                }
                $reportTitle = 'Monthly Sales Report';
                $reportPeriod = date('F Y', strtotime("$year-$month-01"));
                $startDate = "$monthStart 00:00:00";
                $endDate = "$monthEnd 23:59:59";
                break;
                
            case 'yearly':
                $year = $_GET['year'] ?? date('Y');
                $salesData = $this->orderModel->getYearlySales($year);
                // Include archived data
                $archivedData = $this->orderModel->getSalesReportFromHistory("$year-01-01", "$year-12-31");
                if (!empty($archivedData)) {
                    $salesData = array_merge($salesData ?? [], $archivedData);
                }
                $reportTitle = 'Yearly Sales Report';
                $reportPeriod = $year;
                $startDate = "$year-01-01 00:00:00";
                $endDate = "$year-12-31 23:59:59";
                break;
                
            case 'custom':
                if ($customStart && $customEnd) {
                    $salesData = $this->orderModel->getCustomRangeSales($customStart, $customEnd);
                    // Include archived data
                    $archivedData = $this->orderModel->getSalesReportFromHistory($customStart, $customEnd);
                    if (!empty($archivedData)) {
                        $salesData = array_merge($salesData ?? [], $archivedData);
                    }
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

    // ========== SUPPLIER CRUD METHODS ==========
    
    public function suppliers() {
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort'] ?? 'supplier_name';
        $sortOrder = $_GET['order'] ?? 'ASC';
        
        if (!empty($search)) {
            $suppliers = $this->supplierModel->searchSorted($search, $sortBy, $sortOrder);
        } else {
            $suppliers = $this->supplierModel->getAllSorted($sortBy, $sortOrder);
        }
        
        // Add product count for each supplier
        foreach ($suppliers as &$supplier) {
            $supplier['product_count'] = $this->supplierModel->getProductCount($supplier['id']);
        }
        unset($supplier); // Important: unset reference to avoid bugs
        
        include __DIR__ . '/../views/admin/suppliers.php';
    }
    
    public function createSupplier() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=create_supplier');
                exit();
            }
            
            $validator = new Validation();
            $validator->required('supplier_name', $_POST['supplier_name'] ?? '');
            
            if ($validator->hasErrors()) {
                Session::setFlash('message', 'Supplier name is required');
                header('Location: admin.php?page=create_supplier');
                exit();
            }
            
            $supplierName = trim($_POST['supplier_name']);
            $contactPerson = trim($_POST['contact_person'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            if ($this->supplierModel->create($supplierName, $contactPerson, $phone, $email, $address)) {
                Session::setFlash('success', 'Supplier created successfully');
                header('Location: admin.php?page=suppliers');
            } else {
                Session::setFlash('message', 'Failed to create supplier. Name may already exist.');
                header('Location: admin.php?page=create_supplier');
            }
            exit();
        }
        
        include __DIR__ . '/../views/admin/supplier_create.php';
    }
    
    public function editSupplier() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=suppliers');
            exit();
        }
        
        $id = intval($_GET['id']);
        $supplier = $this->supplierModel->findById($id);
        
        if (!$supplier) {
            Session::setFlash('message', 'Supplier not found');
            header('Location: admin.php?page=suppliers');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=edit_supplier&id=' . $id);
                exit();
            }
            
            $validator = new Validation();
            $validator->required('supplier_name', $_POST['supplier_name'] ?? '');
            
            if ($validator->hasErrors()) {
                Session::setFlash('message', 'Supplier name is required');
                header('Location: admin.php?page=edit_supplier&id=' . $id);
                exit();
            }
            
            $supplierName = trim($_POST['supplier_name']);
            $contactPerson = trim($_POST['contact_person'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            if ($this->supplierModel->update($id, $supplierName, $contactPerson, $phone, $email, $address)) {
                Session::setFlash('success', 'Supplier updated successfully');
                header('Location: admin.php?page=suppliers');
            } else {
                Session::setFlash('message', 'Failed to update supplier. Name may already exist.');
                header('Location: admin.php?page=edit_supplier&id=' . $id);
            }
            exit();
        }
        
        include __DIR__ . '/../views/admin/supplier_edit.php';
    }
    
    public function deleteSupplier() {
        if (!isset($_GET['id'])) {
            header('Location: admin.php?page=suppliers');
            exit();
        }
        
        $id = intval($_GET['id']);
        $productCount = $this->supplierModel->getProductCount($id);
        
        if ($productCount > 0) {
            Session::setFlash('message', 'Cannot delete supplier with associated products');
            header('Location: admin.php?page=suppliers');
            exit();
        }
        
        if ($this->supplierModel->delete($id)) {
            Session::setFlash('success', 'Supplier deleted successfully');
        } else {
            Session::setFlash('message', 'Failed to delete supplier');
        }
        
        header('Location: admin.php?page=suppliers');
        exit();
    }
    
    // ========== Product Image Management ==========
    
    public function deleteProductImage() {
        if (!isset($_GET['image_id']) || !isset($_GET['product_id'])) {
            Session::setFlash('message', 'Invalid request - missing parameters');
            header('Location: admin.php?page=products');
            exit();
        }
        
        $imageId = intval($_GET['image_id']);
        $productId = intval($_GET['product_id']);
        
        // Check if this is the only image
        $images = $this->productModel->getProductImages($productId);
        if (count($images) <= 1) {
            Session::setFlash('message', 'Cannot delete the only image. Please add another image first.');
            header('Location: admin.php?page=edit_product&id=' . $productId);
            exit();
        }
        
        if ($this->productModel->deleteProductImage($imageId)) {
            Session::setFlash('success', 'Image deleted successfully');
        } else {
            Session::setFlash('message', 'Failed to delete image. Please try again.');
        }
        
        header('Location: admin.php?page=edit_product&id=' . $productId);
        exit();
    }
    
    public function setPrimaryImage() {
        // Check if this is an AJAX request (POST) or regular request (GET)
        $isAjax = $_SERVER['REQUEST_METHOD'] === 'POST';

        if ($isAjax) {
            header('Content-Type: application/json');

            if (!isset($_POST['csrf_token']) || !CSRF::validateToken($_POST['csrf_token'])) {
                echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
                exit();
            }
        }

        $imageId = intval($_POST['image_id'] ?? $_GET['image_id'] ?? 0);
        $productId = intval($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

        if (!$imageId || !$productId) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'error' => 'Invalid request parameters']);
                exit();
            } else {
                Session::setFlash('message', 'Invalid request');
                header('Location: admin.php?page=products');
                exit();
            }
        }

        // Check if product exists
        $product = $this->productModel->findById($productId);
        if (!$product) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit();
            } else {
                Session::setFlash('message', 'Product not found');
                header('Location: admin.php?page=products');
                exit();
            }
        }

        if ($this->productModel->setPrimaryImage($imageId)) {
            // Also update the legacy img_path field
            $images = $this->productModel->getProductImages($productId);
            foreach ($images as $img) {
                if ($img['id'] == $imageId) {
                    $this->productModel->update(
                        $productId,
                        $product['category_id'],
                        $product['product_name'],
                        $product['description'],
                        $product['cost_price'],
                        $product['selling_price'],
                        $product['supplier_id'],
                        $img['image_path'],
                        $product['is_active']
                    );
                    break;
                }
            }

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Primary image updated successfully']);
            } else {
                Session::setFlash('success', 'Primary image updated successfully');
                header('Location: admin.php?page=edit_product&id=' . $productId);
            }
        } else {
            if ($isAjax) {
                echo json_encode(['success' => false, 'error' => 'Failed to set primary image']);
            } else {
                Session::setFlash('message', 'Failed to set primary image');
                header('Location: admin.php?page=edit_product&id=' . $productId);
            }
        }

        if (!$isAjax) {
            exit();
        }
    }
    
    /**
     * Handle AJAX image upload for products
     */
    public function uploadProductImages() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit();
        }
        
        if (!isset($_POST['csrf_token']) || !CSRF::validateToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit();
        }
        
        $productId = intval($_POST['product_id'] ?? 0);
        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            exit();
        }
        
        // Check if product exists
        $product = $this->productModel->findById($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            exit();
        }
        
        $uploadedCount = 0;
        $errors = [];
        
        if (isset($_FILES['product_images']) && is_array($_FILES['product_images']['name']) && !empty($_FILES['product_images']['name'][0])) {
            $files = $_FILES['product_images'];
            $fileCount = count($files['name']);
            $existingImages = $this->productModel->getProductImages($productId);
            $nextOrder = count($existingImages);
            $uploadedPaths = []; // Track uploaded image paths to prevent duplicates
            
            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                if (!empty($file['name']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploadResult = FileUpload::uploadProductImage($file, $productId);
                    if ($uploadResult['success']) {
                        $imagePath = 'products/' . $uploadResult['filename'];
                        
                        // Check if this image path was already uploaded in this session
                        if (!in_array($imagePath, $uploadedPaths)) {
                            $uploadedPaths[] = $imagePath;
                            
                            // Set as primary if no existing images and this is the first upload
                            $isPrimary = (count($existingImages) === 0 && count($uploadedPaths) === 1);
                            $this->productModel->addProductImage($productId, $imagePath, $nextOrder + count($uploadedPaths) - 1, $isPrimary);
                            
                            // Update legacy img_path if this is the first image
                            if ($isPrimary) {
                                $this->productModel->update(
                                    $productId,
                                    $product['category_id'],
                                    $product['product_name'],
                                    $product['description'],
                                    $product['cost_price'],
                                    $product['selling_price'],
                                    $product['supplier_id'],
                                    $imagePath,
                                    $product['is_active']
                                );
                            }
                            
                            $uploadedCount++;
                        }
                    } else {
                        $errors[] = "Failed to upload {$file['name']}: " . $uploadResult['error'];
                    }
                }
            }
        }
        
        if ($uploadedCount > 0) {
            echo json_encode([
                'success' => true, 
                'message' => "Successfully uploaded {$uploadedCount} image(s)",
                'uploaded_count' => $uploadedCount,
                'errors' => $errors
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'error' => 'No images were uploaded successfully',
                'errors' => $errors
            ]);
        }
        exit();
    }

    public function deleteProductImages() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit();
        }

        if (!isset($_POST['csrf_token']) || !CSRF::validateToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit();
        }

        $productId = intval($_POST['product_id'] ?? 0);
        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            exit();
        }

        $imageIds = json_decode($_POST['image_ids'] ?? '[]', true);
        if (!is_array($imageIds) || empty($imageIds)) {
            echo json_encode(['success' => false, 'error' => 'No images selected for deletion']);
            exit();
        }

        // Check if product exists
        $product = $this->productModel->findById($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            exit();
        }

        // Get current images to check constraints
        $currentImages = $this->productModel->getProductImages($productId);
        $primaryImage = null;
        foreach ($currentImages as $img) {
            if ($img['is_primary']) {
                $primaryImage = $img;
                break;
            }
        }

        // Check if trying to delete the primary image
        $deletingPrimary = false;
        foreach ($imageIds as $imageId) {
            if ($primaryImage && $primaryImage['id'] == $imageId) {
                $deletingPrimary = true;
                break;
            }
        }

        // If deleting primary and there are other images, we need to set another as primary
        if ($deletingPrimary && count($currentImages) > count($imageIds)) {
            // Find the first non-primary image to set as primary
            $newPrimaryImage = null;
            foreach ($currentImages as $img) {
                if ($img['is_primary'] == 0 && !in_array($img['id'], $imageIds)) {
                    $newPrimaryImage = $img;
                    break;
                }
            }

            if ($newPrimaryImage) {
                $this->productModel->setPrimaryImage($newPrimaryImage['id']);
                // Update legacy img_path
                $this->productModel->update(
                    $productId,
                    $product['category_id'],
                    $product['product_name'],
                    $product['description'],
                    $product['cost_price'],
                    $product['selling_price'],
                    $product['supplier_id'],
                    $newPrimaryImage['image_path'],
                    $product['is_active']
                );
            }
        }

        // Check if we're deleting all images
        if (count($currentImages) <= count($imageIds)) {
            echo json_encode(['success' => false, 'error' => 'Cannot delete all images. At least one image must remain.']);
            exit();
        }

        $deletedCount = 0;
        $errors = [];

        foreach ($imageIds as $imageId) {
            if ($this->productModel->deleteProductImage($imageId)) {
                $deletedCount++;
            } else {
                $errors[] = "Failed to delete image ID: {$imageId}";
            }
        }

        if ($deletedCount > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} image(s)",
                'deleted_count' => $deletedCount,
                'errors' => $errors
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'No images were deleted successfully',
                'errors' => $errors
            ]);
        }
        exit();
    }

}
