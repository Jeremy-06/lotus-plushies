<?php
$pageTitle = 'Checkout - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-0" style="margin-top: 0;">
            <i class="fas fa-credit-card me-2"></i>Checkout
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%);">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-list-alt me-2"></i>Order Summary
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($cartItems as $item): ?>
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="me-3">
                        <?php if ($item['img_path']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($item['img_path']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="rounded" style="width: 80px; height: 80px; object-fit: contain;">
                        <?php else: ?>
                            <div class="rounded d-flex align-items-center justify-content-center position-relative" style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(139, 95, 191, 0.15) 0%, rgba(255, 159, 191, 0.2) 100%); overflow: hidden;">
                                <div class="position-absolute" style="top: -30%; right: -20%; width: 50px; height: 50px; background: rgba(139, 95, 191, 0.2); border-radius: 50%; filter: blur(15px);"></div>
                                <i class="fas fa-box-open" style="font-size: 2rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: float 3s ease-in-out infinite;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-tag me-1"></i>₱<?php echo number_format($item['selling_price'], 2); ?> × <?php echo $item['quantity']; ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <h5 class="mb-0 text-primary">₱<?php echo number_format($item['selling_price'] * $item['quantity'], 2); ?></h5>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%);">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-receipt me-2"></i>Order Total
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-bold">₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted">
                        <i class="fas fa-store me-1"></i>Shipping:
                    </span>
                    <span class="fw-bold text-success">
                        <?php if ($shippingCost == 0): ?>
                            FREE (Walk-in/Pickup)
                        <?php else: ?>
                            ₱<?php echo number_format($shippingCost, 2); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-4 p-3 rounded" style="background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(177, 156, 217, 0.1) 100%);">
                    <h5 class="mb-0">Total:</h5>
                    <h4 class="mb-0 text-primary">₱<?php echo number_format($totalAmount, 2); ?></h4>
                </div>
                
                <div class="mb-4">
                    <h6 class="mb-3">
                        <i class="fas fa-money-bill-wave me-2"></i>Payment Method
                    </h6>
                    <div class="p-3 border rounded position-relative" style="background: linear-gradient(135deg, rgba(139, 95, 191, 0.08) 0%, rgba(177, 156, 217, 0.08) 100%); border-color: var(--purple-medium) !important;">
                        <input type="radio" name="payment_method" id="cash" value="cash" checked style="display: none;">
                        <label for="cash" class="m-0 w-100" style="cursor: pointer;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-money-bill-wave text-white"></i>
                                    </div>
                                    <div>
                                        <strong style="color: var(--purple-dark);">Cash Payment</strong><br>
                                        <small class="text-muted">Pay with cash at pickup/walk-in</small>
                                    </div>
                                </div>
                                <div style="width: 24px; height: 24px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-check text-white" style="font-size: 12px;"></i>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <form method="POST" action="index.php?page=checkout&action=process">
                    <?php echo CSRF::getTokenField(); ?>
                    <div class="d-grid gap-2">
                        <button type="submit" name="place_order" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Place Order
                        </button>
                        <a href="index.php?page=cart" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Cart
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>