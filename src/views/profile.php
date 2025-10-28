<?php
$pageTitle = 'Profile - Online Shop';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
?>

<h2 class="mb-4">My Profile</h2>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-user-circle" style="font-size: 100px; color: #6c757d;"></i>
                <h4 class="mt-3"><?php echo htmlspecialchars($user['email']); ?></h4>
                <p class="text-muted">Customer</p>
                <p><small>Member since: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></small></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Account Information</h5>
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Account Type:</strong></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Member Since:</strong></td>
                        <td><?php echo date('F d, Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Recent Orders</h5>
                <?php if (!empty($recentOrders)): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($recentOrders, 0, 5) as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><span class="badge bg-info"><?php echo ucfirst($order['order_status']); ?></span></td>
                                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="index.php?page=order_history" class="btn btn-sm btn-primary">View All Orders</a>
                <?php else: ?>
                <p>No orders yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>