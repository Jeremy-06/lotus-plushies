<?php
$pageTitle = 'Manage Orders - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/UIHelper.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-box me-2"></i>Manage Orders</h2>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <?php
        // Calculate badge counts using UIHelper
        $statusCounts = UIHelper::calculateStatusCounts($allOrders);
        ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
            <!-- Search Form -->
            <form method="GET" action="admin.php" class="d-flex align-items-center gap-2">
                <input type="hidden" name="page" value="orders">
                <?php if (isset($_GET['status'])): ?>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status']); ?>">
                <?php endif; ?>
                
                <div class="input-group" style="max-width: 250px;">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search orders..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                           style="border: 2px solid #8b5fbf; border-radius: 25px 0 0 25px; padding: 10px 20px; outline: none; box-shadow: none;">
                    <button type="submit" class="btn" style="background: #8b5fbf; color: white; border: 2px solid #8b5fbf; border-radius: 0 25px 25px 0; padding: 10px 20px; outline: none; box-shadow: none;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                    <a href="admin.php?page=orders<?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?>" 
                       class="btn" style="background: #6c757d; color: white; border-radius: 25px; padding: 10px 20px; text-decoration: none; outline: none; box-shadow: none;">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </form>
            
            <!-- Status Filter Buttons -->
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <a href="admin.php?page=orders&status=pending<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" 
                   class="d-flex align-items-center" 
                   style="border-radius: 20px; padding: 10px 20px; text-decoration: none; <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'background: white; color: #8b5fbf !important; border: 2px solid #8b5fbf;' : 'background: #8b5fbf; color: white !important; border: 2px solid #8b5fbf;'; ?>">
                    <i class="fas fa-clock me-2" style="color: inherit;"></i> 
                    <span class="me-2" style="color: inherit;">Pending</span>
                    <?php if ($statusCounts['pending'] > 0): ?>
                        <span class="badge" style="background: <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? '#8b5fbf' : 'white'; ?>; color: <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'white' : '#8b5fbf'; ?>;"><?php echo $statusCounts['pending']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin.php?page=orders&status=shipped<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" 
                   class="d-flex align-items-center" 
                   style="border-radius: 20px; padding: 10px 20px; text-decoration: none; <?php echo (isset($_GET['status']) && $_GET['status'] == 'shipped') ? 'background: white; color: #8b5fbf !important; border: 2px solid #8b5fbf;' : 'background: #8b5fbf; color: white !important; border: 2px solid #8b5fbf;'; ?>">
                    <i class="fas fa-shipping-fast me-2" style="color: inherit;"></i> 
                    <span class="me-2" style="color: inherit;">Shipped</span>
                    <?php if ($statusCounts['shipped'] > 0): ?>
                        <span class="badge" style="background: <?php echo (isset($_GET['status']) && $_GET['status'] == 'shipped') ? '#8b5fbf' : 'white'; ?>; color: <?php echo (isset($_GET['status']) && $_GET['status'] == 'shipped') ? 'white' : '#8b5fbf'; ?>;"><?php echo $statusCounts['shipped']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin.php?page=orders&status=completed<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" 
                   class="d-flex align-items-center" 
                   style="border-radius: 20px; padding: 10px 20px; text-decoration: none; <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'background: white; color: #8b5fbf !important; border: 2px solid #8b5fbf;' : 'background: #8b5fbf; color: white !important; border: 2px solid #8b5fbf;'; ?>">
                    <i class="fas fa-check-circle me-2" style="color: inherit;"></i> 
                    <span class="me-2" style="color: inherit;">Completed</span>
                    <?php if ($statusCounts['completed'] > 0): ?>
                        <span class="badge" style="background: <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? '#8b5fbf' : 'white'; ?>; color: <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'white' : '#8b5fbf'; ?>;"><?php echo $statusCounts['completed']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin.php?page=orders&status=cancelled<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" 
                   class="d-flex align-items-center" 
                   style="border-radius: 20px; padding: 10px 20px; text-decoration: none; <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'background: white; color: #8b5fbf !important; border: 2px solid #8b5fbf;' : 'background: #8b5fbf; color: white !important; border: 2px solid #8b5fbf;'; ?>">
                    <i class="fas fa-times-circle me-2" style="color: inherit;"></i> 
                    <span class="me-2" style="color: inherit;">Cancelled</span>
                    <?php if ($statusCounts['cancelled'] > 0): ?>
                        <span class="badge" style="background: <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? '#8b5fbf' : 'white'; ?>; color: <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'white' : '#8b5fbf'; ?>;"><?php echo $statusCounts['cancelled']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin.php?page=orders" 
                   class="d-flex align-items-center" 
                   style="border-radius: 20px; padding: 10px 20px; text-decoration: none; <?php echo (!isset($_GET['status'])) ? 'background: linear-gradient(135deg, #8b5fbf 0%, #b19cd9 100%); color: white !important; border: 2px solid #8b5fbf;' : 'background: #8b5fbf; color: white !important; border: 2px solid #8b5fbf;'; ?>">
                    <i class="fas fa-list me-2" style="color: inherit;"></i> 
                    <span class="me-2" style="color: inherit;">All Orders</span>
                    <span class="badge" style="background: white; color: #8b5fbf;"><?php echo count($allOrders); ?></span>
                </a>
            </div>
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
                <td><?php echo UIHelper::formatDate($order['created_at']); ?></td>
                <td><?php echo $order['item_count']; ?></td>
                <td><?php echo UIHelper::formatCurrency($order['total_amount']); ?></td>
                <td>
                    <?php echo UIHelper::renderSimpleStatusBadge($order['order_status']); ?>
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