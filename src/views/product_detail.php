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
                <?php 
                // Determine which images to display
                $hasImages = !empty($productImages);
                $displayImages = $hasImages ? $productImages : [];
                
                // Fallback to old img_path if no images in product_images table
                if (!$hasImages && !empty($product['img_path'])) {
                    $displayImages = [['image_path' => $product['img_path'], 'is_primary' => 1]];
                    $hasImages = true;
                }
                ?>
                
                <?php if ($hasImages): ?>
                    <!-- Simple Product Image Gallery -->
                    <div class="simple-image-gallery">
                        <!-- Main Image Display -->
                        <div class="main-image-container" style="position: relative; background: #f8f9fa; border-radius: 15px; overflow: hidden; min-height: 500px; border: 2px solid #e9ecef;">
                            <!-- Navigation Arrows -->
                            <?php if (count($displayImages) > 1): ?>
                            <button class="nav-arrow nav-prev" onclick="navigateImage(-1)" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; background: rgba(139, 95, 191, 0.9); color: white; border: none; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="nav-arrow nav-next" onclick="navigateImage(1)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; background: rgba(139, 95, 191, 0.9); color: white; border: none; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <?php endif; ?>

                            <!-- Image Counter -->
                            <div class="image-counter" style="position: absolute; top: 15px; right: 15px; background: var(--purple-dark); color: white; padding: 8px 12px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; z-index: 10; box-shadow: 0 2px 8px rgba(139, 95, 191, 0.3);">
                                <span id="currentImageIndex">1</span> / <?php echo count($displayImages); ?>
                            </div>

                            <!-- Main Image -->
                            <div class="image-wrapper" style="width: 100%; height: 500px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                                <img id="mainProductImage"
                                     src="uploads/<?php echo htmlspecialchars($displayImages[0]['image_path']); ?>"
                                     class="main-image"
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                     style="max-width: 100%; max-height: 100%; object-fit: contain; transition: all 0.5s ease;">
                            </div>

                            <!-- Loading Indicator -->
                            <div class="image-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none; z-index: 5;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Simple Thumbnail Gallery -->
                        <?php if (count($displayImages) > 1): ?>
                        <div class="thumbnail-gallery-container" style="margin-top: 20px;">
                            <div class="thumbnail-gallery" id="thumbnailGallery" style="display: flex; gap: 12px; overflow-x: auto; padding: 10px 5px; scrollbar-width: thin; scroll-behavior: smooth;">
                                <?php foreach ($displayImages as $index => $image): ?>
                                    <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>"
                                         onclick="changeMainImage(<?php echo $index; ?>, this)"
                                         data-index="<?php echo $index; ?>"
                                         style="min-width: 90px; height: 90px; border: 3px solid <?php echo $index === 0 ? 'var(--purple-dark)' : 'transparent'; ?>; border-radius: 12px; overflow: hidden; cursor: pointer; transition: all 0.3s ease; position: relative; flex-shrink: 0;">
                                        <img src="uploads/<?php echo htmlspecialchars($image['image_path']); ?>"
                                             alt="Thumbnail <?php echo $index + 1; ?>"
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="no-image-showcase" style="height: 550px; border-radius: 25px; background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.2); display: flex; align-items-center; justify-content: center; position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.1), inset 0 1px 0 rgba(255,255,255,0.2);">
                        <!-- Enhanced Decorative Elements -->
                        <div class="no-image-bg-1" style="position: absolute; top: -30%; right: -20%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(139, 95, 191, 0.1) 0%, transparent 70%); border-radius: 50%; animation: float-slow 6s ease-in-out infinite;"></div>
                        <div class="no-image-bg-2" style="position: absolute; bottom: -30%; left: -20%; width: 250px; height: 250px; background: radial-gradient(circle, rgba(255, 159, 191, 0.1) 0%, transparent 70%); border-radius: 50%; animation: float-slow 8s ease-in-out infinite reverse;"></div>
                        <div class="no-image-bg-3" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 400px; height: 400px; background: radial-gradient(circle, rgba(255, 215, 0, 0.05) 0%, transparent 70%); border-radius: 50%; animation: pulse-glow 4s ease-in-out infinite;"></div>

                        <div class="no-image-content" style="position: relative; text-align: center; z-index: 2;">
                            <div class="no-image-icon" style="margin-bottom: 2rem; animation: bounce-in 1s ease-out;">
                                <i class="fas fa-images" style="font-size: 5rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; filter: drop-shadow(0 10px 30px rgba(139, 95, 191, 0.3));"></i>
                            </div>
                            <h3 class="no-image-title" style="color: var(--purple-medium); font-weight: 700; font-size: 2rem; margin-bottom: 1rem; letter-spacing: 1px;">No Images Available</h3>
                            <p class="no-image-subtitle" style="color: var(--text-secondary); font-size: 1.1rem; max-width: 300px; margin: 0 auto; line-height: 1.6;">Beautiful product images will be displayed here once uploaded</p>
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
                
                <!-- Product Details (Category & Supplier) -->
                <hr style="border-top: 2px solid var(--purple-light); margin: 1.5rem 0;">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3" style="background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(139, 95, 191, 0.05) 100%); border-radius: 15px;">
                            <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-tag" style="color: white; font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <small style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Category</small>
                                <div style="color: var(--purple-dark); font-weight: 700; font-size: 1.1rem;">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3" style="background: linear-gradient(135deg, rgba(255, 159, 191, 0.15) 0%, rgba(255, 159, 191, 0.08) 100%); border-radius: 15px;">
                            <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--pink-medium) 0%, var(--pink-light) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-truck" style="color: white; font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <small style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Supplier</small>
                                <div style="color: var(--purple-dark); font-weight: 700; font-size: 1.1rem;">
                                    <?php echo $product['supplier_name'] ? htmlspecialchars($product['supplier_name']) : 'N/A'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
/* Simple Product Image Gallery Styles */

/* Navigation Arrows */
.nav-arrow {
    transition: all 0.3s ease;
}

.nav-arrow:hover:not(:disabled) {
    background: rgba(139, 95, 191, 1) !important;
    transform: translateY(-50%) scale(1.1) !important;
    box-shadow: 0 6px 20px rgba(0,0,0,0.3) !important;
}

.nav-arrow:active:not(:disabled) {
    transform: translateY(-50%) scale(0.95) !important;
}

/* Thumbnails */
.thumbnail-item {
    transition: all 0.3s ease;
    cursor: pointer;
}

.thumbnail-item:hover {
    border-color: var(--purple-medium) !important;
    transform: translateY(-2px) !important;
}

.thumbnail-item.active {
    border-color: var(--purple-dark) !important;
    box-shadow: 0 4px 15px rgba(139, 95, 191, 0.3) !important;
}

/* Hide scrollbars for thumbnail container */
#thumbnailGallery::-webkit-scrollbar {
    display: none;
}

#thumbnailGallery {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .main-image-container {
        min-height: 400px !important;
    }

    .image-wrapper {
        height: 400px !important;
    }

    .nav-arrow {
        width: 45px !important;
        height: 45px !important;
    }

    .thumbnail-item {
        min-width: 80px !important;
        height: 80px !important;
    }
}

@media (max-width: 576px) {
    .main-image-container {
        min-height: 350px !important;
        border-radius: 12px !important;
    }

    .image-wrapper {
        height: 350px !important;
    }
}

/* Focus states for accessibility */
.nav-arrow:focus,
.thumbnail-item:focus {
    outline: 2px solid var(--purple-dark);
    outline-offset: 2px;
}

/* Back to Products Button Hover Effect */
.back-to-products-btn:hover {
    background: var(--purple-dark) !important;
    color: white !important;
    border-color: var(--purple-dark) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(139, 95, 191, 0.3);
    transition: all 0.3s ease;
}
</style>

<script>
// Simple Product Image Gallery
let currentImageIndex = 0;

<?php
// Pass image data to JavaScript
echo "const productImages = " . json_encode(array_map(function($img) {
    return ['path' => 'uploads/' . $img['image_path']];
}, $displayImages)) . ";";
?>

document.addEventListener('DOMContentLoaded', function() {
    initializeSimpleGallery();
    setupKeyboardNavigation();
});

function initializeSimpleGallery() {
    updateNavigationButtons();
    preloadImages();
}

function preloadImages() {
    productImages.forEach((image, index) => {
        const img = new Image();
        img.src = image.path;
    });
}

function changeMainImage(index, thumbnailElement = null) {
    if (index < 0 || index >= productImages.length) return;

    const mainImage = document.getElementById('mainProductImage');
    const loadingIndicator = document.querySelector('.image-loading');

    // Show loading
    loadingIndicator.style.display = 'block';
    mainImage.style.opacity = '0.3';

    // Update current index
    currentImageIndex = index;

    // Load new image
    const newImage = new Image();
    newImage.onload = function() {
        mainImage.src = productImages[index].path;
        mainImage.style.opacity = '1';
        loadingIndicator.style.display = 'none';
    };
    newImage.src = productImages[index].path;

    // Update UI elements
    updateActiveThumbnail(index);
    updateImageCounter();
    updateNavigationButtons();
}

function updateActiveThumbnail(activeIndex) {
    // Update thumbnails
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    thumbnails.forEach((thumb, index) => {
        if (index === activeIndex) {
            thumb.classList.add('active');
            thumb.style.borderColor = 'var(--purple-dark)';
        } else {
            thumb.classList.remove('active');
            thumb.style.borderColor = 'transparent';
        }
    });
}

function updateImageCounter() {
    const counter = document.getElementById('currentImageIndex');
    if (counter) {
        counter.textContent = currentImageIndex + 1;
    }
}

function updateNavigationButtons() {
    const prevBtn = document.querySelector('.nav-prev');
    const nextBtn = document.querySelector('.nav-next');

    if (prevBtn && nextBtn) {
        const isAtStart = currentImageIndex === 0;
        const isAtEnd = currentImageIndex === productImages.length - 1;

        prevBtn.style.opacity = isAtStart ? '0.4' : '1';
        nextBtn.style.opacity = isAtEnd ? '0.4' : '1';
        prevBtn.disabled = isAtStart;
        nextBtn.disabled = isAtEnd;
    }
}

function navigateImage(direction) {
    const newIndex = currentImageIndex + direction;
    if (newIndex >= 0 && newIndex < productImages.length) {
        changeMainImage(newIndex);
    }
}

function setupKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        // Only work when not typing in inputs
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        switch(e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                navigateImage(-1);
                break;
            case 'ArrowRight':
                e.preventDefault();
                navigateImage(1);
                break;
        }
    });
}

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