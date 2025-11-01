<?php
$pageTitle = 'Order History - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/UIHelper.php';
?>

<style>
.btn-view-details:hover {
    background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%) !important;
    color: white !important;
    border-color: var(--purple-dark) !important;
}
</style>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-4" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
            <i class="fas fa-history me-2"></i>Order History
        </h2>
    </div>
</div>

<!-- Status Filter Pills -->
<div class="row mb-4">
    <div class="col-12">
        <?php
        // Calculate status counts using UIHelper
        $statusCounts = UIHelper::calculateStatusCounts($allOrders, true);
        
        $currentFilter = $_GET['filter'] ?? 'all';
        ?>
        <ul class="nav nav-pills d-flex flex-wrap" style="gap: 10px;">
            <li class="nav-item">
                <a href="index.php?page=order_history" 
                   class="nav-link d-flex align-items-center <?php echo ($currentFilter == 'all') ? 'active' : ''; ?>" 
                   style="border-radius: 20px; padding: 10px 20px; <?php echo ($currentFilter == 'all') ? 'background: linear-gradient(135deg, #8b5fbf 0%, #b19cd9 100%);' : ''; ?>">
                    <i class="fas fa-list me-2"></i> 
                    <span class="me-2">All Orders</span>
                    <span class="badge bg-white" style="color: var(--purple-dark);"><?php echo count($allOrders); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=order_history&filter=pending" 
                   class="nav-link d-flex align-items-center <?php echo ($currentFilter == 'pending') ? 'active' : ''; ?>" 
                   style="border-radius: 20px; padding: 10px 20px;">
                    <i class="fas fa-clock me-2"></i> 
                    <span class="me-2">Pending</span>
                    <?php if ($statusCounts['pending'] > 0): ?>
                        <span class="badge bg-white" style="color: var(--purple-dark);"><?php echo $statusCounts['pending']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=order_history&filter=shipped" 
                   class="nav-link d-flex align-items-center <?php echo ($currentFilter == 'shipped') ? 'active' : ''; ?>" 
                   style="border-radius: 20px; padding: 10px 20px;">
                    <i class="fas fa-shipping-fast me-2"></i> 
                    <span class="me-2">Shipped</span>
                    <?php if ($statusCounts['shipped'] > 0): ?>
                        <span class="badge bg-white" style="color: var(--purple-dark);"><?php echo $statusCounts['shipped']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=order_history&filter=completed" 
                   class="nav-link d-flex align-items-center <?php echo ($currentFilter == 'completed') ? 'active' : ''; ?>" 
                   style="border-radius: 20px; padding: 10px 20px;">
                    <i class="fas fa-check-circle me-2"></i> 
                    <span class="me-2">Completed</span>
                    <?php if ($statusCounts['completed'] > 0): ?>
                        <span class="badge bg-white" style="color: var(--purple-dark);"><?php echo $statusCounts['completed']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=order_history&filter=cancelled" 
                   class="nav-link d-flex align-items-center <?php echo ($currentFilter == 'cancelled') ? 'active' : ''; ?>" 
                   style="border-radius: 20px; padding: 10px 20px;">
                    <i class="fas fa-times-circle me-2"></i> 
                    <span class="me-2">Cancelled</span>
                    <?php if ($statusCounts['cancelled'] > 0): ?>
                        <span class="badge bg-white" style="color: var(--purple-dark);"><?php echo $statusCounts['cancelled']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </div>
</div>

<?php if (!empty($orders)): ?>
<div class="row g-3">
    <?php foreach ($orders as $order): ?>
    <div class="col-12">
        <div class="card shadow-sm" style="border: none; border-radius: 15px; overflow: hidden; transition: transform 0.2s;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <!-- Order Number & Date -->
                    <div class="col-md-3">
                        <h6 class="text-muted small mb-1">Order Number</h6>
                        <p class="mb-2 fw-bold" style="color: var(--purple-dark);"><?php echo htmlspecialchars($order['order_number']); ?></p>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-calendar-alt me-1"></i><?php echo UIHelper::formatDate($order['created_at']); ?>
                        </p>
                    </div>
                    
                    <!-- Status -->
                    <div class="col-md-2 text-center">
                        <h6 class="text-muted small mb-2">Status</h6>
                        <?php echo UIHelper::renderOrderStatusBadge($order['order_status']); ?>
                    </div>
                    
                    <!-- Total Amount -->
                    <div class="col-md-3 text-center">
                        <h6 class="text-muted small mb-2">Total Amount</h6>
                        <h4 class="mb-0 fw-bold" style="color: var(--purple-dark);">
                            <?php echo UIHelper::formatCurrency($order['total_amount']); ?>
                        </h4>
                    </div>
                    
                    <!-- Actions -->
                    <div class="col-md-4 text-end">
                        <a href="index.php?page=order_detail&id=<?php echo $order['id']; ?>" 
                           class="btn btn-outline-primary btn-view-details me-2" 
                           style="border-radius: 20px; border: 2px solid var(--purple-medium); color: var(--purple-dark);">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <?php if ($order['order_status'] === 'pending'): ?>
                            <a href="index.php?page=order&action=cancel&id=<?php echo $order['id']; ?>" 
                               class="btn btn-danger"
                               style="border-radius: 20px;"
                               onclick="return confirm('Are you sure you want to cancel this order?');">
                                <i class="fas fa-times-circle"></i> Cancel Order
                            </a>
                        <?php elseif (in_array($order['order_status'], ['delivered', 'shipped'])): ?>
                            <a href="index.php?page=order&action=confirm_receipt&id=<?php echo $order['id']; ?>" 
                               class="btn btn-success"
                               style="border-radius: 20px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none;"
                               onclick="return confirm('Confirm that you have received this order?');">
                                <i class="fas fa-check-circle"></i> Order Received
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<?php echo UIHelper::renderEmptyState(
    'No orders yet',
    "You haven't placed any orders yet. Start shopping now!",
    'fa-shopping-bag',
    'index.php?page=products',
    'Start Shopping'
); ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>