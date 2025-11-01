# Developer Quick Reference Guide

## Error Handling (Simplified for Localhost)

### ErrorHandler Class

```php
require_once __DIR__ . '/helpers/ErrorHandler.php';

// Log an error (goes to XAMPP error log: C:\xampp\apache\logs\error.log)
ErrorHandler::log('User login failed', 'ERROR', ['email' => $email]);

// Log a warning
ErrorHandler::log('Deprecated function used', 'WARNING');

// Log info
ErrorHandler::log('User registered successfully', 'INFO', ['user_id' => $userId]);

// Handle file upload errors
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $error = ErrorHandler::getFileUploadError($_FILES['file']['error']);
    Session::setFlash('message', $error);
}

// Show error page (this exits the script)
ErrorHandler::showErrorPage('Not Found', 'The requested page was not found.', 404);

// Assert operation success
if (!ErrorHandler::assertSuccess($result, 'delete user', 'Failed to delete user')) {
    // Handle failure
    return false;
}
```

> **Note**: Errors are logged to XAMPP's error log at `C:\xampp\apache\logs\error.log`

## UI Components

### UIHelper Class

```php
require_once __DIR__ . '/helpers/UIHelper.php';

// Render order status badge (sizes: 'sm', 'md', 'lg')
echo UIHelper::renderOrderStatusBadge('pending', 'md');
echo UIHelper::renderOrderStatusBadge('completed', 'lg');

// Render simple status badge (for tables)
echo UIHelper::renderSimpleStatusBadge('shipped');

// Get status configuration
$config = UIHelper::getStatusConfig('processing');
// Returns: ['class' => 'info', 'icon' => 'fa-spinner', 'bg' => '#d1ecf1', ...]

// Calculate status counts
$statusCounts = UIHelper::calculateStatusCounts($orders);
// For customer view (groups processing/delivered as shipped):
$statusCounts = UIHelper::calculateStatusCounts($orders, true);

// Format currency
echo UIHelper::formatCurrency(1234.56); // ₱1,234.56
echo UIHelper::formatCurrency(1234.56, '$'); // $1,234.56

// Format date
echo UIHelper::formatDate('2025-11-02'); // Nov 02, 2025
echo UIHelper::formatDate('2025-11-02 14:30:00', 'F j, Y g:i A'); // November 2, 2025 2:30 PM

// Render empty state
echo UIHelper::renderEmptyState(
    'No Products Found',
    'There are no products matching your criteria.',
    'fa-box-open',
    'index.php?page=products',
    'View All Products'
);
```

## Database Operations with Error Handling

### In Models

```php
class MyModel extends BaseModel {

    public function customQuery() {
        try {
            $sql = "SELECT * FROM my_table WHERE status = ?";
            $stmt = mysqli_prepare($this->conn, $sql);

            if ($stmt === false) {
                ErrorHandler::log("Prepare failed: " . mysqli_error($this->conn), 'ERROR');
                return [];
            }

            $status = 'active';
            mysqli_stmt_bind_param($stmt, 's', $status);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            return mysqli_fetch_all($result, MYSQLI_ASSOC);

        } catch (Exception $e) {
            ErrorHandler::log("Query exception: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }

    // Using the helper method from BaseModel
    public function simpleQuery() {
        $sql = "SELECT * FROM my_table";
        return $this->executeQuery($sql, 'fetch all records');
    }
}
```

### Transactions

```php
try {
    $this->db->beginTransaction();

    // Perform multiple operations
    $orderId = $this->orderModel->create($data);
    $this->inventoryModel->reduce($productId, $quantity);
    $this->paymentModel->process($orderId, $amount);

    $this->db->commit();
    return $orderId;

} catch (Exception $e) {
    $this->db->rollback();
    ErrorHandler::log("Transaction failed: " . $e->getMessage(), 'ERROR');
    return false;
}
```

## File Upload Handling

### In Controllers

```php
class MyController {

    private function handleFileUpload($file, $subdir = '', $allowedTypes = ['image/jpeg', 'image/png'], $maxSize = 5242880) {
        try {
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = ErrorHandler::getFileUploadError($file['error']);
                ErrorHandler::log("File upload error: {$error}", 'WARNING', ['file' => $file['name']]);
                return ['success' => false, 'error' => $error];
            }

            // Check file size
            if ($file['size'] > $maxSize) {
                return ['success' => false, 'error' => 'File too large'];
            }

            // Check MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                return ['success' => false, 'error' => 'Invalid file type'];
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

            // Prepare upload directory
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!empty($subdir)) {
                $uploadDir .= $subdir . '/';
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                ErrorHandler::log('Failed to move uploaded file', 'ERROR');
                return ['success' => false, 'error' => 'Failed to save file'];
            }

            return ['success' => true, 'filename' => (!empty($subdir) ? $subdir . '/' : '') . $filename];

        } catch (Exception $e) {
            ErrorHandler::log('File upload exception: ' . $e->getMessage(), 'ERROR');
            return ['success' => false, 'error' => 'Upload failed'];
        }
    }

    public function uploadImage() {
        if (!empty($_FILES['image']['name'])) {
            $result = $this->handleFileUpload($_FILES['image'], 'products');

            if ($result['success']) {
                $imagePath = $result['filename'];
                // Use $imagePath in your database
            } else {
                Session::setFlash('message', $result['error']);
                return false;
            }
        }
    }
}
```

## Common Patterns

### Controller Method with Full Error Handling

```php
public function createItem() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // CSRF validation
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('message', 'Invalid request');
            header('Location: admin.php?page=items');
            exit();
        }

        // Input validation
        $validator = new Validation();
        $validator->required('name', $_POST['name'] ?? '')
                  ->required('price', $_POST['price'] ?? '');

        if ($validator->hasErrors()) {
            Session::setFlash('message', 'Please fill in all required fields');
            header('Location: admin.php?page=create_item');
            exit();
        }

        try {
            // Handle file upload
            $imagePath = '';
            if (!empty($_FILES['image']['name'])) {
                $uploadResult = $this->handleFileUpload($_FILES['image'], 'items');
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['error']);
                }
                $imagePath = $uploadResult['filename'];
            }

            // Create item
            $itemId = $this->itemModel->create([
                'name' => trim($_POST['name']),
                'price' => floatval($_POST['price']),
                'image' => $imagePath
            ]);

            if (!$itemId) {
                throw new Exception('Failed to create item');
            }

            Session::setFlash('success', 'Item created successfully');
            header('Location: admin.php?page=items');
            exit();

        } catch (Exception $e) {
            ErrorHandler::log('Item creation failed: ' . $e->getMessage(), 'ERROR', [
                'name' => $_POST['name'] ?? '',
                'user_id' => Session::get('user_id')
            ]);
            Session::setFlash('message', 'Failed to create item');
            header('Location: admin.php?page=create_item');
            exit();
        }
    }

    // Show create form
    include __DIR__ . '/../views/admin/item_create.php';
}
```

## View Pattern with UI Helpers

```php
<?php
$pageTitle = 'My Page';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/UIHelper.php';
?>

<div class="container">
    <h1>Orders</h1>

    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo UIHelper::formatDate($order['created_at']); ?></td>
                        <td><?php echo UIHelper::formatCurrency($order['total']); ?></td>
                        <td><?php echo UIHelper::renderSimpleStatusBadge($order['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <?php echo UIHelper::renderEmptyState(
            'No Orders',
            'You have no orders yet.',
            'fa-shopping-cart',
            'index.php?page=shop',
            'Start Shopping'
        ); ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
```

## Testing Error Handling

```php
// Test database error handling
try {
    // Intentionally cause an error
    $result = $this->db->query("SELECT * FROM non_existent_table");
    // Should log error and return false
} catch (Exception $e) {
    // Error is logged, app continues
}

// Test file upload error handling
$_FILES['test'] = [
    'error' => UPLOAD_ERR_INI_SIZE,
    'name' => 'test.jpg'
];
$error = ErrorHandler::getFileUploadError($_FILES['test']['error']);
// Returns: "File is too large"
```

## Best Practices for Localhost Development

1. **Always use try-catch for critical operations**
2. **Log errors with context** (user ID, parameters, etc.) - they'll appear in XAMPP error log
3. **Return graceful defaults** (empty arrays, false, null) instead of throwing exceptions
4. **Use UIHelper for all UI components** to maintain consistency
5. **Validate file uploads** with MIME type checking
6. **Use prepared statements** for all database queries
7. **Check XAMPP error logs** when debugging issues: `C:\xampp\apache\logs\error.log`

## Viewing Errors

For localhost development, check your XAMPP error log:

- **Location**: `C:\xampp\apache\logs\error.log`
- **Open with**: Any text editor (Notepad++, VS Code, etc.)
- **Tip**: Keep it open while developing to see errors in real-time
