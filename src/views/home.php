<?php
$pageTitle = 'Home - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

if (!isset($products)) {
    $productModel = new Product();
    $products = $productModel->getActiveProducts();
}

if (!isset($categories)) {
    $categoryModel = new Category();
    $categories = $categoryModel->getActive();
}
?>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-md-12">
        <div class="hero-section shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 50%, var(--pink-medium) 100%); border-radius: 25px; padding: 5rem 3rem; text-align: center;">
            <!-- Decorative Elements -->
            <div class="position-absolute top-0 start-0 w-100 h-100" style="opacity: 0.1; pointer-events: none;">
                <div class="position-absolute" style="top: 20%; left: 10%; width: 80px; height: 80px; background: white; border-radius: 50%; filter: blur(40px);"></div>
                <div class="position-absolute" style="bottom: 20%; right: 15%; width: 100px; height: 100px; background: white; border-radius: 50%; filter: blur(50px);"></div>
            </div>
            
            <div class="position-relative">
                <h1 class="display-2 fw-bold text-white mb-3" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.15); letter-spacing: -1px;">
                    Welcome to Lotus Plushies
                </h1>
                <p class="lead text-white mb-4" style="font-size: 1.4rem; text-shadow: 1px 1px 3px rgba(0,0,0,0.15); white-space: nowrap;">
                    Discover adorable plushies that bring joy and comfort to your life
                </p>
                <a href="index.php?page=products" class="btn btn-light btn-lg shadow-sm" style="border-radius: 30px; padding: 1rem 3rem; font-weight: 700; border: 3px solid rgba(255,255,255,0.3);">
                    <i class="fas fa-shopping-bag me-2"></i>Shop Now
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<div class="row mb-5">
    <div class="col-md-12 mb-4 text-center">
        <div class="section-badge mb-3" style="display: inline-block; background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(255, 159, 191, 0.1) 100%); padding: 0.5rem 1.5rem; border-radius: 50px; border: 2px solid rgba(139, 95, 191, 0.2);">
            <i class="fas fa-th-large me-2" style="color: var(--purple-dark);"></i>
            <span style="color: var(--purple-dark); font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Browse Categories</span>
        </div>
        <h2 class="mb-2" style="color: var(--purple-dark); font-weight: 800; font-size: 2.5rem;">
            Shop by Category
        </h2>
        <p class="text-muted" style="font-size: 1.1rem;">Find exactly what you're looking for</p>
    </div>
    
    <div class="col-md-12 position-relative">
        <!-- Navigation Buttons Outside -->
        <?php 
        $allCategories = array_merge([['id' => 'all', 'category_name' => 'All Products']], $categories);
        $totalCategories = count($allCategories);
        $itemsPerSlide = 4;
        $totalSlides = ceil($totalCategories / $itemsPerSlide);
        ?>
        
        <?php if ($totalSlides > 1): ?>
            <button class="carousel-control-prev position-absolute start-0 top-50 translate-middle-y" type="button" data-bs-target="#categoryCarousel" data-bs-slide="prev" style="width: 50px; height: 50px; z-index: 10; left: -25px !important;">
                <span class="d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); width: 50px; height: 50px; border-radius: 50%; box-shadow: 0 4px 15px rgba(139, 95, 191, 0.3);">
                    <i class="fas fa-chevron-left text-white"></i>
                </span>
            </button>
        <?php endif; ?>
        
        <!-- Carousel Container -->
        <div id="categoryCarousel" class="carousel slide mx-5" data-bs-ride="false">
            <div class="carousel-inner">
                <?php 
                for ($slide = 0; $slide < $totalSlides; $slide++):
                    $startIndex = $slide * $itemsPerSlide;
                    $slideCategories = array_slice($allCategories, $startIndex, $itemsPerSlide);
                    
                    // Pad with empty slots if less than 4 items
                    $emptySlots = $itemsPerSlide - count($slideCategories);
                ?>
                    <div class="carousel-item <?php echo $slide === 0 ? 'active' : ''; ?>">
                        <div class="row g-3">
                            <?php foreach ($slideCategories as $category): ?>
                                <div class="col-md-3 col-sm-6">
                                    <a href="index.php?page=products<?php echo $category['id'] !== 'all' ? '&category=' . $category['id'] : ''; ?>" class="text-decoration-none">
                                        <div class="category-card shadow-sm h-100 position-relative" style="background: white; border-radius: 20px; padding: 2.5rem 1.5rem; text-align: center; border: 3px solid transparent; transition: all 0.3s ease; min-height: 150px; overflow: hidden;">
                                            <div class="category-icon mb-3" style="width: 60px; height: 60px; margin: 0 auto; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); border-radius: 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(139, 95, 191, 0.3); transition: all 0.3s ease;">
                                                <i class="fas fa-box-open" style="color: white; font-size: 1.5rem;"></i>
                                            </div>
                                            <h5 class="mb-2 fw-bold" style="color: var(--purple-dark); font-size: 1.1rem;"><?php echo htmlspecialchars($category['category_name']); ?></h5>
                                            <small class="text-muted" style="font-weight: 600;"><?php echo $category['id'] === 'all' ? 'View Everything' : 'Explore Items'; ?></small>
                                            <div class="category-arrow position-absolute" style="bottom: 15px; right: 15px; opacity: 0; transition: all 0.3s ease;">
                                                <i class="fas fa-arrow-right" style="color: var(--purple-dark); font-size: 1.2rem;"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Empty placeholder cards to maintain 4-column layout -->
                            <?php for ($i = 0; $i < $emptySlots; $i++): ?>
                                <div class="col-md-3 col-sm-6">
                                    <div style="min-height: 120px;"></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <?php if ($totalSlides > 1): ?>
            <button class="carousel-control-next position-absolute end-0 top-50 translate-middle-y" type="button" data-bs-target="#categoryCarousel" data-bs-slide="next" style="width: 50px; height: 50px; z-index: 10; right: -25px !important;">
                <span class="d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); width: 50px; height: 50px; border-radius: 50%; box-shadow: 0 4px 15px rgba(139, 95, 191, 0.3);">
                    <i class="fas fa-chevron-right text-white"></i>
                </span>
            </button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Featured Products Section -->
<div class="row mb-5">
    <div class="col-md-12 mb-4 text-center">
        <div class="section-badge mb-3" style="display: inline-block; background: linear-gradient(135deg, rgba(255, 159, 191, 0.1) 0%, rgba(139, 95, 191, 0.1) 100%); padding: 0.5rem 1.5rem; border-radius: 50px; border: 2px solid rgba(255, 159, 191, 0.2);">
            <i class="fas fa-star me-2" style="color: var(--pink-medium);"></i>
            <span style="color: var(--purple-dark); font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Customer Favorites</span>
        </div>
        <h2 class="mb-2" style="color: var(--purple-dark); font-weight: 800; font-size: 2.5rem;">
            Featured Collection
        </h2>
        <p class="text-muted" style="font-size: 1.1rem;">Our most loved plushies picked just for you</p>
    </div>
</div>

<div class="row g-4">
    <?php if (!empty($products)): ?>
        <?php foreach (array_slice($products, 0, 8) as $product): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card h-100 shadow-sm product-card" style="border: 3px solid transparent; border-radius: 20px; overflow: hidden; transition: all 0.3s ease;">
                <div class="position-relative">
                    <?php if ($product['img_path']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['img_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="height: 250px; width: 100%; object-fit: contain; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); aspect-ratio: 1/1; transition: transform 0.3s ease;">
                    <?php else: ?>
                        <div class="card-img-top d-flex align-items-center justify-content-center position-relative" style="height: 250px; aspect-ratio: 1/1; background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(255, 159, 191, 0.15) 100%); overflow: hidden;">
                            <!-- Decorative background circles -->
                            <div class="position-absolute" style="top: -20%; right: -10%; width: 150px; height: 150px; background: rgba(139, 95, 191, 0.1); border-radius: 50%; filter: blur(30px);"></div>
                            <div class="position-absolute" style="bottom: -20%; left: -10%; width: 120px; height: 120px; background: rgba(255, 159, 191, 0.15); border-radius: 50%; filter: blur(25px);"></div>
                            
                            <div class="position-relative text-center">
                                <div class="mb-3" style="animation: float 3s ease-in-out infinite;">
                                    <i class="fas fa-box-open" style="font-size: 4rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                </div>
                                <p class="mb-0 fw-bold" style="color: var(--purple-medium); font-size: 0.9rem; letter-spacing: 0.5px;">No Image</p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($product['quantity_on_hand'] > 0): ?>
                        <span class="badge position-absolute top-0 end-0 m-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 0.6rem 1rem; border-radius: 15px; font-weight: 700; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
                            <i class="fas fa-check-circle me-1"></i>In Stock
                        </span>
                    <?php else: ?>
                        <span class="badge position-absolute top-0 end-0 m-3" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 0.6rem 1rem; border-radius: 15px; font-weight: 700; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);">
                            <i class="fas fa-times-circle me-1"></i>Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="card-body d-flex flex-column" style="padding: 1.5rem;">
                    <h5 class="card-title mb-2" style="color: var(--purple-dark); font-weight: 700; font-size: 1.1rem; min-height: 2.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?php echo htmlspecialchars($product['product_name']); ?>
                    </h5>
                    <p class="card-text text-muted small mb-3" style="min-height: 3rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...
                    </p>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="mb-0 fw-bold" style="color: var(--purple-dark); font-size: 1.5rem;">₱<?php echo number_format($product['selling_price'], 2); ?></h4>
                                <small class="text-muted d-flex align-items-center mt-1" style="font-weight: 600;">
                                    <i class="fas fa-box me-1" style="color: var(--purple-medium);"></i><?php echo $product['quantity_on_hand']; ?> available
                                </small>
                            </div>
                        </div>
                        
                        <a href="index.php?page=product_detail&id=<?php echo $product['id']; ?>" class="btn w-100 shadow-sm" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); color: white; border: none; border-radius: 15px; padding: 0.8rem; font-weight: 700; transition: all 0.3s ease;">
                            <i class="fas fa-eye me-2"></i>View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-md-12">
            <div class="alert alert-info text-center" style="border-radius: 15px; border: none; background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(177, 156, 217, 0.1) 100%);">
                <i class="fas fa-info-circle me-2"></i>No products available at the moment.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- View All Button -->
<div class="row mt-5 mb-5">
    <div class="col-md-12 text-center">
        <a href="index.php?page=products" class="btn btn-lg shadow-lg" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); color: white; border: none; border-radius: 50px; padding: 1.2rem 4rem; font-weight: 700; font-size: 1.1rem; transition: all 0.3s ease;">
            <i class="fas fa-grid-2 me-2"></i>Explore All Products
        </a>
    </div>
</div>

<style>
.category-card {
    transition: all 0.3s ease;
}

.category-card:hover {
    border-color: var(--purple-medium) !important;
    transform: translateY(-8px);
    box-shadow: 0 12px 35px rgba(139, 95, 191, 0.35) !important;
    background: linear-gradient(135deg, rgba(139, 95, 191, 0.05) 0%, rgba(255, 159, 191, 0.05) 100%) !important;
}

.category-card:hover .category-icon {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 6px 20px rgba(139, 95, 191, 0.5);
}

.category-card:hover .category-arrow {
    opacity: 1 !important;
    transform: translateX(5px);
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 35px rgba(139, 95, 191, 0.35) !important;
    border-color: rgba(139, 95, 191, 0.3) !important;
}

.product-card:hover .card-img-top {
    transform: scale(1.08);
}

.product-card .btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(139, 95, 191, 0.5) !important;
}

/* Carousel navigation styling */
.carousel-control-prev:hover span,
.carousel-control-next:hover span {
    transform: scale(1.15);
    box-shadow: 0 6px 20px rgba(139, 95, 191, 0.5) !important;
}

/* Fix carousel slide padding to prevent cutoff */
#categoryCarousel .carousel-item {
    padding-top: 10px;
    padding-bottom: 10px;
}

/* Section Badge Animation */
.section-badge {
    transition: all 0.3s ease;
    animation: fadeInDown 0.6s ease;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

/* Enhanced View All Button Hover */
.row .btn-lg:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 35px rgba(139, 95, 191, 0.5) !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>