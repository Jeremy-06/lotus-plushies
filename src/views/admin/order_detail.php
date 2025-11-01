<?php
$pageTitle = 'Order Details - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<!-- Header Section -->
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <div class="d-flex align-items-center">
            <div class="me-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-receipt" style="font-size: 1.8rem; color: white;"></i>
            </div>
            <div>
                <h2 class="mb-1" style="color: var(--purple-dark);">Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                <p class="mb-0 text-muted">
                    <i class="far fa-calendar me-1"></i>
                    <?php echo date('F d, Y \a\t H:i', strtotime($order['created_at'])); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <a href="admin.php?page=orders" class="btn btn-outline-secondary" style="border-radius: 20px; padding: 10px 25px;">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<!-- Status Badge -->
<div class="row mb-4">
    <div class="col-md-12">
        <?php
        $statusConfig = [
            'pending' => ['color' => '#ffc107', 'icon' => 'fa-clock', 'label' => 'Pending'],
            'shipped' => ['color' => '#007bff', 'icon' => 'fa-shipping-fast', 'label' => 'Shipped'],
            'completed' => ['color' => '#28a745', 'icon' => 'fa-check-double', 'label' => 'Completed'],
            'cancelled' => ['color' => '#dc3545', 'icon' => 'fa-times-circle', 'label' => 'Cancelled']
        ];
        $config = $statusConfig[$order['order_status']] ?? ['color' => '#6c757d', 'icon' => 'fa-question', 'label' => 'Unknown'];
        ?>
        <div class="alert d-inline-flex align-items-center" style="background: <?php echo $config['color']; ?>15; border: 2px solid <?php echo $config['color']; ?>; border-radius: 15px; padding: 15px 25px;">
            <i class="fas <?php echo $config['icon']; ?> me-2" style="color: <?php echo $config['color']; ?>; font-size: 1.3rem;"></i>
            <span style="color: <?php echo $config['color']; ?>; font-weight: 600; font-size: 1.1rem;">
                Order Status: <?php echo $config['label']; ?>
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-md-8">
        <!-- Customer Information -->
        <div class="card shadow-sm mb-4" style="border-radius: 20px; border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border: none; padding: 20px;">
                <h5 class="mb-0" style="color: white;"><i class="fas fa-user me-2"></i>Customer Information</h5>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                            <small class="text-muted d-block mb-1">Email Address</small>
                            <strong style="color: var(--purple-dark);">
                                <i class="fas fa-envelope me-2" style="color: var(--purple-medium);"></i>
                                <?php echo htmlspecialchars($order['email']); ?>
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3" style="background: #f8f9fa; border-radius: 12px;">
                            <small class="text-muted d-block mb-1">Order Date</small>
                            <strong style="color: var(--purple-dark);">
                                <i class="far fa-calendar-alt me-2" style="color: var(--purple-medium);"></i>
                                <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="card shadow-sm" style="border-radius: 20px; border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border: none; padding: 20px;">
                <h5 class="mb-0" style="color: white;"><i class="fas fa-shopping-bag me-2"></i>Order Items</h5>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="border: none; padding: 15px; color: var(--purple-dark); font-weight: 600;">Product</th>
                                <th style="border: none; padding: 15px; color: var(--purple-dark); font-weight: 600; text-align: center;">Quantity</th>
                                <th style="border: none; padding: 15px; color: var(--purple-dark); font-weight: 600; text-align: right;">Unit Price</th>
                                <th style="border: none; padding: 15px; color: var(--purple-dark); font-weight: 600; text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 20px; vertical-align: middle;">
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['img_path']): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($item['img_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" 
                                                 class="me-3">
                                        <?php else: ?>
                                            <div class="me-3 d-flex align-items-center justify-content-center position-relative" style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(139, 95, 191, 0.15) 0%, rgba(255, 159, 191, 0.2) 100%); border-radius: 12px; overflow: hidden;">
                                                <div class="position-absolute" style="top: -10px; right: -10px; width: 40px; height: 40px; background: rgba(139, 95, 191, 0.1); border-radius: 50%; filter: blur(15px);"></div>
                                                <div class="position-absolute" style="bottom: -10px; left: -10px; width: 35px; height: 35px; background: rgba(255, 159, 191, 0.15); border-radius: 50%; filter: blur(12px);"></div>
                                                <div class="position-relative" style="animation: float 3s ease-in-out infinite;">
                                                    <i class="fas fa-box-open" style="font-size: 1.8rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <span style="font-weight: 500; color: #333;"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                    </div>
                                </td>
                                <td style="padding: 20px; text-align: center; vertical-align: middle;">
                                    <span class="badge" style="background: var(--purple-medium); padding: 8px 16px; border-radius: 20px; font-size: 0.95rem;">
                                        ×<?php echo $item['quantity']; ?>
                                    </span>
                                </td>
                                <td style="padding: 20px; text-align: right; vertical-align: middle; color: #666;">
                                    ₱<?php echo number_format($item['unit_price'], 2); ?>
                                </td>
                                <td style="padding: 20px; text-align: right; vertical-align: middle;">
                                    <strong style="color: var(--purple-dark); font-size: 1.05rem;">
                                        ₱<?php echo number_format($item['item_total'], 2); ?>
                                    </strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="col-md-4">
        <!-- Order Summary -->
        <div class="card shadow-sm mb-4" style="border-radius: 20px; border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border: none; padding: 20px;">
                <h5 class="mb-0" style="color: white;"><i class="fas fa-calculator me-2"></i>Order Summary</h5>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div class="mb-3 pb-3" style="border-bottom: 2px dashed #e0e0e0;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span style="color: #333; font-weight: 500;">₱<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping:</span>
                        <span style="color: #333; font-weight: 500;">₱<?php echo number_format($order['shipping_cost'], 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Tax:</span>
                        <span style="color: #333; font-weight: 500;">₱<?php echo number_format($order['tax_amount'], 2); ?></span>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(255, 159, 191, 0.15) 100%); padding: 15px; border-radius: 12px;">
                    <span style="font-weight: 600; color: var(--purple-dark); font-size: 1.1rem;">Total Amount:</span>
                    <span style="font-weight: 700; color: var(--purple-dark); font-size: 1.4rem;">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Update Status -->
        <?php if ($order['order_status'] !== 'completed' && $order['order_status'] !== 'cancelled'): ?>
        <div class="card shadow-sm" style="border-radius: 20px; border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border: none; padding: 20px;">
                <h5 class="mb-0" style="color: white;"><i class="fas fa-edit me-2"></i>Update Status</h5>
            </div>
            <div class="card-body" style="padding: 25px;">
                <form method="POST" action="admin.php?page=update_order">
                    <?php echo CSRF::getTokenField(); ?>
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label" style="color: var(--purple-dark); font-weight: 600; margin-bottom: 15px;">Change Status</label>
                        
                        <select class="form-select custom-status-select" id="status" name="status" required 
                                style="border: 3px solid var(--purple-medium); 
                                       border-radius: 15px; 
                                       padding: 15px 20px; 
                                       font-size: 1.05rem; 
                                       font-weight: 600;
                                       background: white;
                                       cursor: pointer;
                                       transition: all 0.3s;
                                       box-shadow: 0 2px 8px rgba(139, 95, 191, 0.1);"
                                onchange="updateSelectColor(this)"
                                onfocus="this.style.borderColor='var(--purple-dark)'; this.style.boxShadow='0 4px 12px rgba(139, 95, 191, 0.2)';"
                                onblur="this.style.borderColor='var(--purple-medium)'; this.style.boxShadow='0 2px 8px rgba(139, 95, 191, 0.1)';">
                            <option value="pending" data-color="#f57f17" data-bg="#fff8e1" <?php echo ($order['order_status'] == 'pending') ? 'selected' : ''; ?>>
                                ⏱️ Pending - Order awaiting processing
                            </option>
                            <option value="shipped" data-color="#0277bd" data-bg="#e3f2fd" <?php echo ($order['order_status'] == 'shipped') ? 'selected' : ''; ?>>
                                🚚 Shipped - Order is on the way
                            </option>
                            <option value="cancelled" data-color="#c62828" data-bg="#ffebee" <?php echo ($order['order_status'] == 'cancelled') ? 'selected' : ''; ?>>
                                ❌ Cancelled - Order has been cancelled
                            </option>
                        </select>
                        
                        <script>
                        function updateSelectColor(select) {
                            const selectedOption = select.options[select.selectedIndex];
                            const color = selectedOption.getAttribute('data-color');
                            const bg = selectedOption.getAttribute('data-bg');
                            
                            select.style.color = color;
                            select.style.background = bg;
                        }
                        
                        // Initialize color on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            const statusSelect = document.getElementById('status');
                            if (statusSelect) {
                                updateSelectColor(statusSelect);
                            }
                        });
                        </script>
                        
                        <small class="text-muted mt-3 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            Customers can mark shipped orders as completed.
                        </small>
                    </div>
                    
                    <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; border: none; border-radius: 12px; padding: 12px; font-weight: 600;">
                        <i class="fas fa-save me-2"></i>Update Order Status
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