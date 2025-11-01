<?php
$pageTitle = htmlspecialchars($product['product_name']) . ' - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb" style="background: transparent; padding: 0;">
        <li class="breadcrumb-item"><a href="index.php" style="color: var(--purple-dark);">Home</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=products" style="color: var(--purple-dark);">Products</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['product_name']); ?></li>
    </ol>
</nav>

<div class="row g-4">
    <!-- Product Image Section -->
    <div class="col-lg-6">
        <div class="card shadow-sm" style="border: none; border-radius: 20px; overflow: hidden; position: sticky; top: 20px;">
            <div class="card-body p-0">
                <?php if ($product['img_path']): ?>
                    <div style="background: #f8f9fa; padding: 2rem; display: flex; align-items: center; justify-content: center; min-height: 500px;">
                        <img src="uploads/<?php echo htmlspecialchars($product['img_path']); ?>" 
                             class="img-fluid" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                             style="max-height: 500px; width: 100%; object-fit: contain; border-radius: 15px;">
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center position-relative" style="height: 500px; border-radius: 15px; background: linear-gradient(135deg, rgba(139, 95, 191, 0.15) 0%, rgba(255, 159, 191, 0.2) 100%); overflow: hidden;">
                        <!-- Decorative background circles -->
                        <div class="position-absolute" style="top: -20%; right: -10%; width: 250px; height: 250px; background: rgba(139, 95, 191, 0.15); border-radius: 50%; filter: blur(60px);"></div>
                        <div class="position-absolute" style="bottom: -20%; left: -10%; width: 200px; height: 200px; background: rgba(255, 159, 191, 0.2); border-radius: 50%; filter: blur(50px);"></div>
                        
                        <div class="position-relative text-center">
                            <div class="mb-4" style="animation: float 3s ease-in-out infinite;">
                                <i class="fas fa-box-open" style="font-size: 6rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                            </div>
                            <p class="mb-0 fw-bold h4" style="color: var(--purple-medium); letter-spacing: 1px;">No Image Available</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Product Details Section -->
    <div class="col-lg-6">
        <!-- Product Title & Price Card -->
        <div class="card shadow-sm mb-4" style="border: none; border-radius: 20px; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); padding: 2rem; border: none;">
                <h2 class="mb-3" style="color: white; font-weight: 700; font-size: 2rem;">
                    <?php echo htmlspecialchars($product['product_name']); ?>
                </h2>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0" style="color: white; font-size: 2.5rem; font-weight: 800;">
                        ₱<?php echo number_format($product['selling_price'], 2); ?>
                    </h3>
                    <?php if ($inventory > 0): ?>
                        <span class="badge" style="background: rgba(255,255,255,0.3); color: white; padding: 0.75rem 1.5rem; font-size: 1rem; border-radius: 25px;">
                            <i class="fas fa-check-circle me-2"></i><?php echo $inventory; ?> In Stock
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger" style="padding: 0.75rem 1.5rem; font-size: 1rem; border-radius: 25px;">
                            <i class="fas fa-times-circle me-2"></i>Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Description -->
            <div class="card-body p-4">
                <h5 style="color: var(--purple-dark); font-weight: 700; margin-bottom: 1rem;">
                    <i class="fas fa-info-circle me-2"></i>Product Description
                </h5>
                <p style="color: var(--text-primary); line-height: 1.8; font-size: 1.05rem;">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>
            </div>
        </div>
        
        <!-- Add to Cart Card -->
        <div class="card shadow-sm mb-4" style="border: none; border-radius: 20px; overflow: hidden;">
            <div class="card-body p-4">
                <?php if ($inventory > 0): ?>
                    <?php if (Session::isLoggedIn()): ?>
                        <form method="POST" action="index.php?page=cart&action=add">
                            <?php echo CSRF::getTokenField(); ?>
                            <input type="hidden" name="type" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="mb-4">
                                <label for="quantity" class="form-label fw-bold" style="color: var(--purple-dark); font-size: 1.1rem;">
                                    <i class="fas fa-shopping-basket me-2"></i>Quantity:
                                </label>
                                <div class="input-group" style="max-width: 200px;">
                                    <button type="button" class="btn btn-outline-secondary qty-btn" onclick="decreaseQty()" style="border: 2px solid var(--purple-medium); color: var(--purple-dark); border-radius: 15px 0 0 15px;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control text-center fw-bold" id="quantity" name="product_qty" 
                                           value="1" min="1" max="<?php echo $inventory; ?>" 
                                           style="border: 2px solid var(--purple-medium); border-left: none; border-right: none; font-size: 1.2rem; -moz-appearance: textfield;">
                                    <button type="button" class="btn btn-outline-secondary qty-btn" onclick="increaseQty()" style="border: 2px solid var(--purple-medium); color: var(--purple-dark); border-radius: 0 15px 15px 0;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Maximum: <?php echo $inventory; ?> available</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; border: none; border-radius: 25px; padding: 1rem 2rem; font-size: 1.2rem; font-weight: 700; box-shadow: 0 4px 15px rgba(139, 95, 191, 0.4);">
                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe5b4 100%); border: 2px solid var(--purple-medium); border-radius: 15px; padding: 1.5rem;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x me-3" style="color: var(--purple-dark);"></i>
                                <div>
                                    <h5 class="mb-1" style="color: var(--purple-dark); font-weight: 700;">Login Required</h5>
                                    <p class="mb-0" style="color: var(--text-primary);">
                                        Please <a href="index.php?page=login" style="color: var(--purple-dark); font-weight: 700; text-decoration: underline;">login</a> to add items to your cart
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-danger" style="border: none; border-radius: 15px; padding: 1.5rem;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-times-circle fa-2x me-3"></i>
                            <div>
                                <h5 class="mb-1 fw-bold">Out of Stock</h5>
                                <p class="mb-0">This product is currently unavailable</p>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-secondary btn-lg w-100" disabled style="border-radius: 25px; padding: 1rem;">
                        <i class="fas fa-ban me-2"></i>Out of Stock
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="text-center">
            <a href="index.php?page=products" class="btn btn-outline-secondary btn-lg back-to-products-btn" style="border: 2px solid var(--purple-medium); color: var(--purple-dark); border-radius: 25px; padding: 0.75rem 2rem; font-weight: 600;">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
        </div>
    </div>
</div>

<style>
.qty-btn {
    transition: all 0.3s ease;
}

.qty-btn:hover {
    background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%) !important;
    color: white !important;
    border-color: var(--purple-dark) !important;
    transform: scale(1.05);
}

.qty-btn:active {
    transform: scale(0.95);
}

.back-to-products-btn {
    transition: all 0.3s ease;
}

.back-to-products-btn:hover {
    background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%) !important;
    color: white !important;
    border-color: var(--purple-dark) !important;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(139, 95, 191, 0.4);
}

.back-to-products-btn:active {
    transform: translateY(-1px);
}
</style>

<script>
function increaseQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    const min = parseInt(input.min);
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>