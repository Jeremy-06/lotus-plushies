<?php
$pageTitle = 'Shopping Cart - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-0" style="margin-top: 0;">
            <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
        </h2>
    </div>
</div>

<?php if (!empty($cartItems)): ?>
<form method="POST" action="index.php?page=cart&action=update">
    <?php echo CSRF::getTokenField(); ?>
    
    <div class="row">
        <div class="col-md-8">
            <?php foreach ($cartItems as $item): ?>
                <?php $itemSubtotal = $item['selling_price'] * $item['quantity']; ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">
                            <input type="checkbox" name="checkout_items[]" value="<?php echo $item['product_id']; ?>" class="form-check-input custom-checkbox" style="width: 24px; height: 24px; cursor: pointer; border: 2px solid var(--purple-medium); border-radius: 6px;">
                        </div>
                        <div class="col-md-2">
                            <?php if ($item['img_path']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['img_path']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="img-fluid rounded" style="max-height: 100px; object-fit: contain;">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center rounded position-relative" style="height: 100px; background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(255, 159, 191, 0.15) 100%); overflow: hidden;">
                                    <!-- Decorative background circles -->
                                    <div class="position-absolute" style="top: -20%; right: -10%; width: 60px; height: 60px; background: rgba(139, 95, 191, 0.1); border-radius: 50%; filter: blur(20px);"></div>
                                    <div class="position-absolute" style="bottom: -20%; left: -10%; width: 50px; height: 50px; background: rgba(255, 159, 191, 0.15); border-radius: 50%; filter: blur(18px);"></div>
                                    
                                    <div class="position-relative text-center">
                                        <div class="mb-1" style="animation: float 3s ease-in-out infinite;">
                                            <i class="fas fa-box-open" style="font-size: 2rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                        </div>
                                        <p class="mb-0 fw-bold" style="color: var(--purple-medium); font-size: 0.7rem; letter-spacing: 0.5px;">No Image</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                            <small class="text-muted">
                                <i class="fas fa-box me-1"></i>Available: <?php echo $item['quantity_on_hand']; ?>
                            </small>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-0 text-muted small">Price</p>
                            <h5 class="mb-0 text-primary cart-price">₱<?php echo number_format($item['selling_price'], 2); ?></h5>
                        </div>
                        <div class="col-md-2">
                            <p class="mb-1 text-muted small">Quantity</p>
                            <div class="input-group input-group-sm" style="flex-wrap: nowrap; max-width: 120px;">
                                <button type="button" class="btn btn-sm qty-btn-cart" onclick="decreaseCartQty(<?php echo $item['product_id']; ?>)" style="border: 1px solid var(--purple-medium); color: var(--purple-dark); border-radius: 8px 0 0 8px; padding: 0.25rem 0.5rem; background: white;">
                                    <i class="fas fa-minus" style="font-size: 0.75rem;"></i>
                                </button>
                                <input type="number" name="product_qty[<?php echo $item['product_id']; ?>]" 
                                       id="qty_<?php echo $item['product_id']; ?>"
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['quantity_on_hand']; ?>" 
                                       class="form-control form-control-sm text-center fw-bold" 
                                       style="border: 1px solid var(--purple-medium); border-left: none; border-right: none; -moz-appearance: textfield; padding: 0.25rem;">
                                <button type="button" class="btn btn-sm qty-btn-cart" onclick="increaseCartQty(<?php echo $item['product_id']; ?>, <?php echo $item['quantity_on_hand']; ?>)" style="border: 1px solid var(--purple-medium); color: var(--purple-dark); border-radius: 0 8px 8px 0; padding: 0.25rem 0.5rem; background: white;">
                                    <i class="fas fa-plus" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <p class="mb-1 text-muted small" style="visibility: hidden;">.</p>
                            <a href="index.php?page=cart&action=remove&product_id=<?php echo $item['product_id']; ?>" 
                               class="btn btn-outline-danger remove-item-btn" 
                               style="border-radius: 50%; width: 45px; height: 45px; display: inline-flex; align-items: center; justify-content: center; padding: 0;"
                               data-product-name="<?php echo htmlspecialchars($item['product_name']); ?>"
                               onclick="return confirmRemove(event, this)">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3 pt-3 border-top">
                        <div class="col-md-12 text-end">
                            <h5 class="mb-0"><span class="text-muted">Subtotal:</span> <span class="text-primary cart-price">₱<?php echo number_format($itemSubtotal, 2); ?></span></h5>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-receipt me-2"></i>Order Summary
                    </h4>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Items:</span>
                        <span><?php echo count($cartItems); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal:</span>
                        <span class="cart-price">₱<?php echo number_format($cartTotal, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <h5 class="mb-0">Total:</h5>
                        <h5 class="mb-0 text-primary cart-price">₱<?php echo number_format($cartTotal, 2); ?></h5>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" formaction="index.php?page=checkout" formmethod="POST" class="btn btn-success btn-lg">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                        </button>
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt me-2"></i>Update Cart
                        </button>
                        <a href="index.php?page=products" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php else: ?>
<div class="alert alert-info text-center">
    <h4>Your cart is empty</h4>
    <p>Start shopping to add items to your cart</p>
    <a href="index.php?page=products" class="btn btn-primary">Browse Products</a>
</div>
<?php endif; ?>

<style>
.qty-btn-cart {
    transition: all 0.3s ease;
}

.qty-btn-cart:hover,
.qty-btn-cart:active {
    background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%) !important;
    color: white !important;
    border-color: var(--purple-dark) !important;
    transform: scale(1.05);
}

.qty-btn-cart:active {
    transform: scale(0.95);
}

/* Prevent price text from wrapping */
.cart-price {
    white-space: nowrap;
    font-size: 1.2rem !important;
}

/* Floating animation for no-image placeholder */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}
</style>

<script>
function increaseCartQty(productId, max) {
    const input = document.getElementById('qty_' + productId);
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseCartQty(productId) {
    const input = document.getElementById('qty_' + productId);
    const min = parseInt(input.min);
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}

function confirmRemove(event, element) {
    event.preventDefault();
    const productName = element.getAttribute('data-product-name');
    const removeUrl = element.getAttribute('href');
    
    // Create custom confirmation modal
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div class="modal fade show" id="removeConfirmModal" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-sm" style="border: none; border-radius: 15px; overflow: hidden;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none;">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-exclamation-triangle me-2"></i>Remove Item
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeRemoveModal()"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="fas fa-trash-alt" style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;"></i>
                        <h5 class="mb-3">Are you sure?</h5>
                        <p class="text-muted mb-0">Do you want to remove <strong>"${productName}"</strong> from your cart?</p>
                        <p class="text-muted small mb-0">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-secondary px-4" onclick="closeRemoveModal()">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <a href="${removeUrl}" class="btn btn-danger px-4">
                            <i class="fas fa-trash me-2"></i>Remove
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    return false;
}

function closeRemoveModal() {
    const modal = document.getElementById('removeConfirmModal');
    if (modal && modal.parentElement) {
        modal.parentElement.remove();
        document.body.style.overflow = '';
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeRemoveModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>