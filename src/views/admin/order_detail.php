<?php
$pageTitle = 'Order Details - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Order Details</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="admin.php?page=orders" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Order Information</h5>
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Order Number:</strong></td>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Customer Email:</strong></td>
                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Order Date:</strong></td>
                        <td><?php echo date('F d, Y H:i:s', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
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
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Order Items</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['img_path']): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($item['img_path']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td>₱<?php echo number_format($item['item_total'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Order Summary</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end">₱<?php echo number_format($order['subtotal'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Shipping:</td>
                        <td class="text-end">₱<?php echo number_format($order['shipping_cost'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Tax:</td>
                        <td class="text-end">₱<?php echo number_format($order['tax_amount'], 2); ?></td>
                    </tr>
                    <tr class="table-active">
                        <td><strong>Total:</strong></td>
                        <td class="text-end"><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($order['order_status'] !== 'delivered' && $order['order_status'] !== 'cancelled'): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Update Order Status</h5>
                <form method="POST" action="admin.php?page=update_order">
                    <?php echo CSRF::getTokenField(); ?>
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    
                    <div class="form-group mb-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending" <?php echo ($order['order_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo ($order['order_status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo ($order['order_status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo ($order['order_status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo ($order['order_status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>