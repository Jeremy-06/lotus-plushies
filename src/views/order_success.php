<?php
$pageTitle = 'Order Success - Online Shop';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card text-center">
            <div class="card-body">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                </div>
                <h2 class="card-title text-success mb-4">Order Placed Successfully!</h2>
                <p class="card-text">Thank you for your order.</p>
                
                <?php if (isset($order)): ?>
                <div class="alert alert-info">
                    <p class="mb-1"><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                    <p class="mb-1"><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                    <p class="mb-0"><strong>Status:</strong> <?php echo ucfirst($order['order_status']); ?></p>
                </div>
                <?php endif; ?>
                
                <p>Your order has been received and is being processed. We'll send you a confirmation email shortly.</p>
                
                <div class="mt-4">
                    <a href="index.php?page=order_history" class="btn btn-primary me-2">View Order History</a>
                    <a href="index.php?page=products" class="btn btn-outline-secondary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>