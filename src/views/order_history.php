<?php
$pageTitle = 'Order History - Online Shop';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
?>

<h2 class="mb-4">Order History</h2>

<?php if (!empty($orders)): ?>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Order Number</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
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
                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                    <a href="index.php?page=order_detail&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
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
    <h4>No orders yet</h4>
    <p>You haven't placed any orders yet.</p>
    <a href="index.php?page=products" class="btn btn-primary">Start Shopping</a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>