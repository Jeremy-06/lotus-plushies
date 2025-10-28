<?php
$pageTitle = 'Manage Orders - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Manage Orders</h2>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="btn-group" role="group">
            <a href="admin.php?page=orders" class="btn btn-outline-primary <?php echo !isset($_GET['status']) ? 'active' : ''; ?>">
                All Orders
            </a>
            <a href="admin.php?page=orders&status=pending" class="btn btn-outline-warning <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'active' : ''; ?>">
                Pending
            </a>
            <a href="admin.php?page=orders&status=processing" class="btn btn-outline-info <?php echo (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'active' : ''; ?>">
                Processing
            </a>
            <a href="admin.php?page=orders&status=shipped" class="btn btn-outline-primary <?php echo (isset($_GET['status']) && $_GET['status'] == 'shipped') ? 'active' : ''; ?>">
                Shipped
            </a>
            <a href="admin.php?page=orders&status=delivered" class="btn btn-outline-success <?php echo (isset($_GET['status']) && $_GET['status'] == 'delivered') ? 'active' : ''; ?>">
                Delivered
            </a>
        </div>
    </div>
</div>

<?php if (!empty($orders)): ?>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Order Number</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Items</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                <td><?php echo htmlspecialchars($order['email']); ?></td>
                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                <td><?php echo $order['item_count']; ?></td>
                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                    <?php
                    $statusClass = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $class = $statusClass[$order['order_status']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo $class; ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </td>
                <td>
                    <a href="admin.php?page=order_detail&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info text-center">
    No orders found.
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>