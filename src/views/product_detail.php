<?php
$pageTitle = htmlspecialchars($product['product_name']) . ' - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';

// Helper function to render star ratings
function render_stars($rating, $totalReviews = 0) {
    $rating = floatval($rating);
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    
    $starsHtml = '<div class="d-flex align-items-center">';
    $starsHtml .= '<div class="stars-outer" style="font-size: 1.2rem; color: #d3d3d3; position: relative; display: inline-block;">';
    $starsHtml .= '<div class="stars-inner" style="color: #ffc107; position: absolute; top: 0; left: 0; white-space: nowrap; overflow: hidden; width: ' . ($rating / 5 * 100) . '%;">';
    for ($i = 0; $i < 5; $i++) {
        $starsHtml .= '<i class="fas fa-star"></i>';
    }
    $starsHtml .= '</div>';
    for ($i = 0; $i < 5; $i++) {
        $starsHtml .= '<i class="far fa-star"></i>';
    }
    $starsHtml .= '</div>';
    
    if ($totalReviews > 0) {
        $starsHtml .= '<span class="ms-2" style="color: #F8C8DC; font-weight: 600;">' . number_format($rating, 1) . ' (' . $totalReviews . ' ' . ($totalReviews === 1 ? 'review' : 'reviews') . ')</span>';
    } elseif ($rating > 0) {
        $starsHtml .= '<span class="ms-2" style="color: #F8C8DC; font-weight: 600;">' . number_format($rating, 1) . '</span>';
    }
    
    $starsHtml .= '</div>';
    return $starsHtml;
}
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
        <!-- Image gallery code remains the same -->
        <div class="card shadow-sm" style="border: none; border-radius: 20px; overflow: hidden;">
            <div class="card-body p-0">
                <?php 
                $hasImages = !empty($productImages);
                $displayImages = $hasImages ? $productImages : [];
                if (!$hasImages && !empty($product['img_path'])) {
                    $displayImages = [['image_path' => $product['img_path'], 'is_primary' => 1]];
                    $hasImages = true;
                }
                ?>
                
                <?php if ($hasImages): ?>
                    <div class="simple-image-gallery">
                        <div class="main-image-container" style="position: relative; background: #f8f9fa; border-radius: 15px; overflow: hidden; min-height: 500px; border: 2px solid #e9ecef;">
                            <?php if (count($displayImages) > 1): ?>
                            <button class="nav-arrow nav-prev" onclick="navigateImage(-1)" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; background: rgba(139, 95, 191, 0.9); color: white; border: none; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="nav-arrow nav-next" onclick="navigateImage(1)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; background: rgba(139, 95, 191, 0.9); color: white; border: none; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <?php endif; ?>
                            <div class="image-counter" style="position: absolute; top: 15px; right: 15px; background: var(--purple-dark); color: white; padding: 8px 12px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; z-index: 10; box-shadow: 0 2px 8px rgba(139, 95, 191, 0.3);">
                                <span id="currentImageIndex">1</span> / <?php echo count($displayImages); ?>
                            </div>
                            <div class="image-wrapper" style="width: 100%; height: 500px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                                <img id="mainProductImage" src="uploads/<?php echo htmlspecialchars($displayImages[0]['image_path']); ?>" class="main-image" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; transition: all 0.5s ease;">
                            </div>
                            <div class="image-loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none; z-index: 5;">
                                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                            </div>
                        </div>
                        <?php if (count($displayImages) > 1): ?>
                        <div class="thumbnail-gallery-container" style="margin-top: 20px;">
                            <div class="thumbnail-gallery" id="thumbnailGallery" style="display: flex; gap: 12px; overflow-x: auto; padding: 10px 5px; scrollbar-width: thin; scroll-behavior: smooth;">
                                <?php foreach ($displayImages as $index => $image): ?>
                                    <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeMainImage(<?php echo $index; ?>, this)" data-index="<?php echo $index; ?>" style="min-width: 90px; height: 90px; border: 3px solid <?php echo $index === 0 ? 'var(--purple-dark)' : 'transparent'; ?>; border-radius: 12px; overflow: hidden; cursor: pointer; transition: all 0.3s ease; position: relative; flex-shrink: 0;">
                                        <img src="uploads/<?php echo htmlspecialchars($image['image_path']); ?>" alt="Thumbnail <?php echo $index + 1; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="no-image-showcase" style="height: 550px; border-radius: 25px; background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(255, 159, 191, 0.15) 100%); display: flex; align-items: center; justify-content: center; flex-direction: column; position: relative; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
                        <div class="position-absolute" style="top: -20%; right: -10%; width: 300px; height: 300px; background: rgba(139, 95, 191, 0.1); border-radius: 50%; filter: blur(30px);"></div>
                        <div class="position-absolute" style="bottom: -20%; left: -10%; width: 250px; height: 250px; background: rgba(255, 159, 191, 0.15); border-radius: 50%; filter: blur(25px);"></div>
                        <div class="text-center">
                            <div class="mb-3" style="animation: float 3s ease-in-out infinite;"><i class="fas fa-box-open" style="font-size: 6rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i></div>
                            <p class="mb-0 fw-bold" style="color: var(--purple-medium); font-size: 1.2rem; letter-spacing: 0.5px;">No Image</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Product Details Section -->
    <div class="col-lg-6">
        <div class="card shadow-sm mb-4" style="border: none; border-radius: 20px; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); padding: 2rem; border: none;">
                <h2 class="mb-2" style="color: white; font-weight: 700; font-size: 2rem;"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                
                <!-- Star Rating Display -->
                <div class="mb-3">
                    <?php echo render_stars($product['average_rating'], $product['review_count']); ?>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0" style="color: white; font-size: 2.5rem; font-weight: 800;">₱<?php echo number_format($product['selling_price'], 2); ?></h3>
                    <?php if ($inventory > 0): ?>
                        <span class="badge" style="background: rgba(255,255,255,0.3); color: white; padding: 0.75rem 1.5rem; font-size: 1rem; border-radius: 25px;"><i class="fas fa-check-circle me-2"></i><?php echo $inventory; ?> In Stock</span>
                    <?php else: ?>
                        <span class="badge bg-danger" style="padding: 0.75rem 1.5rem; font-size: 1rem; border-radius: 25px;"><i class="fas fa-times-circle me-2"></i>Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-body p-4">
                <h5 style="color: var(--purple-dark); font-weight: 700; margin-bottom: 1rem;"><i class="fas fa-info-circle me-2"></i>Product Description</h5>
                <p style="color: var(--text-primary); line-height: 1.8; font-size: 1.05rem;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <hr style="border-top: 2px solid var(--purple-light); margin: 1.5rem 0;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3" style="background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(139, 95, 191, 0.05) 100%); border-radius: 15px;">
                            <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-tag" style="color: white; font-size: 1.3rem;"></i></div>
                            <div>
                                <small style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Category</small>
                                <div style="color: var(--purple-dark); font-weight: 700; font-size: 1.1rem;"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3" style="background: linear-gradient(135deg, rgba(255, 159, 191, 0.15) 0%, rgba(255, 159, 191, 0.08) 100%); border-radius: 15px;">
                            <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255, 159, 191, 0.3); box-shadow: 0 4px 12px rgba(255, 159, 191, 0.2);"><i class="fas fa-truck" style="color: var(--pink-medium); font-size: 1.3rem;"></i></div>
                            <div>
                                <small style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Supplier</small>
                                <div style="color: var(--purple-dark); font-weight: 700; font-size: 1.1rem;"><?php echo $product['supplier_name'] ? htmlspecialchars($product['supplier_name']) : 'N/A'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm mb-4" style="border: none; border-radius: 20px; overflow: hidden;">
            <div class="card-body p-4">
                <?php if ($inventory > 0): ?>
                    <?php if (Session::isLoggedIn()): ?>
                        <form method="POST" action="index.php?page=cart&action=add">
                            <?php echo CSRF::getTokenField(); ?>
                            <input type="hidden" name="type" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="mb-4">
                                <label for="quantity" class="form-label fw-bold" style="color: var(--purple-dark); font-size: 1.1rem;"><i class="fas fa-shopping-basket me-2"></i>Quantity:</label>
                                <div class="input-group" style="max-width: 200px; border-radius: 8px; overflow: hidden;">
                                    <button type="button" class="btn qty-btn" onclick="decreaseQty()"><i class="fas fa-minus"></i></button>
                                    <input type="number" class="form-control text-center fw-bold" id="quantity" name="product_qty" value="1" min="1" max="<?php echo $inventory; ?>" style="border-left: none; border-right: none; font-size: 1.2rem; -moz-appearance: textfield;">
                                    <button type="button" class="btn qty-btn" onclick="increaseQty()"><i class="fas fa-plus"></i></button>
                                </div>
                                <small class="text-muted">Maximum: <?php echo $inventory; ?> available</small>
                            </div>
                            <div class="d-grid gap-2"><button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; border-radius: 25px; padding: 1rem 2rem; font-size: 1.2rem; font-weight: 700;"><i class="fas fa-shopping-cart me-2"></i>Add to Cart</button></div>
                        </form>
                    <?php else: ?>
                        <!-- Keep this alert visible until the user acts (no auto-hide) -->
                        <div class="alert no-autohide" data-autohide="false" style="background: linear-gradient(135deg, #fff3cd 0%, #ffe5b4 100%); border-radius: 15px; padding: 1.5rem;"><div class="d-flex align-items-center"><i class="fas fa-exclamation-triangle fa-2x me-3" style="color: var(--purple-dark);"></i><div><h5 class="mb-1" style="color: var(--purple-dark); font-weight: 700;">Login Required</h5><p class="mb-0" style="color: var(--text-primary);">Please <a href="index.php?page=login" style="color: var(--purple-dark); font-weight: 700; text-decoration: underline;">login</a> to add items to your cart</p></div></div></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-danger" style="border-radius: 15px; padding: 1.5rem;"><div class="d-flex align-items-center"><i class="fas fa-times-circle fa-2x me-3"></i><div><h5 class="mb-1 fw-bold">Out of Stock</h5><p class="mb-0">This product is currently unavailable</p></div></div></div>
                    <button class="btn btn-secondary btn-lg w-100" disabled style="border-radius: 25px; padding: 1rem;"><i class="fas fa-ban me-2"></i>Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-center"><a href="index.php?page=products" class="btn btn-outline-secondary btn-lg" style="border-radius: 25px; padding: 0.75rem 2rem; font-weight: 600;"><i class="fas fa-arrow-left me-2"></i>Back to Products</a></div>
    </div>
</div>

<!-- Reviews Section -->
<div class="mt-5">
    <div class="card shadow-sm" id="reviewsCard" style="border: none; border-radius: 20px;">
        <div class="card-header" style="background: linear-gradient(135deg, var(--purple-light) 0%, var(--pink-light) 100%); border-bottom: none; padding: 1.5rem; border-radius: 20px 20px 0 0;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0" style="color: var(--purple-dark); font-weight: 700;"><i class="fas fa-star-half-alt me-2"></i>Customer Reviews</h4>
                <?php if (!empty($reviews)): ?>
                <div class="d-flex align-items-center gap-3">
                        <!-- Star Filter Buttons -->
                        <div class="btn-group btn-group-sm me-3" role="group" aria-label="Star rating filter">
                            <button type="button" class="btn btn-outline-warning active star-filter-btn" data-rating="all" style="border-radius: 20px; font-weight: 600;">All</button>
                            <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                <button type="button" class="btn btn-outline-warning star-filter-btn" data-rating="<?php echo $rating; ?>" style="border-radius: 20px; font-weight: 600; margin-left: 0.25rem;">
                                    <i class="fas fa-star"></i> <?php echo $rating; ?>
                                </button>
                            <?php endfor; ?>
                        </div>                    <!-- Date Sort Toggle Button -->
                    <button type="button" id="dateSortToggle" class="btn btn-outline-primary btn-sm" style="border-radius: 20px; font-weight: 600;">
                        <i class="fas fa-clock me-1"></i><span id="sortText">Newest First</span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if (Session::hasFlash('success')): ?>
                <div class="alert alert-success"><?php echo Session::getFlash('success'); ?></div>
            <?php endif; ?>
            <?php if (Session::hasFlash('error')): ?>
                <div class="alert alert-danger"><?php echo Session::getFlash('error'); ?></div>
            <?php endif; ?>

            <!-- Review Form -->
            <?php if ($canReviewOrderItem): ?>
                <div class="mb-4 p-4" style="background-color: #f8f9fa; border-radius: 15px;">
                    <h5 class="mb-3" style="color: var(--purple-dark); font-weight: 700;">Write a Review</h5>
                    <form action="index.php?page=add_review" method="POST" id="reviewForm">
                        <?php echo CSRF::getTokenField(); ?>
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="order_item_id" value="<?php echo $orderItemId; ?>">
                        <div class="mb-3">
                            <label for="rating" class="form-label">Your Rating</label>
                            <div class="rating-stars">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                    <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Your Review</label>
                            <textarea name="comment" id="comment" rows="4" class="form-control" placeholder="Tell us what you think about this plushie..."></textarea>
                        </div>
                        <button type="submit" class="btn" style="background-color: var(--purple-dark); color: white;">Submit Review</button>
                    </form>
                </div>
            <?php elseif (Session::isLoggedIn() && isset($_GET['review']) && $_GET['review'] === 'true' && !empty($orderItemId) && !$canReviewOrderItem): ?>
                <div class="alert alert-info">You have already reviewed this specific purchase.</div>
            <?php elseif (Session::isLoggedIn() && !$hasReviewedProduct && !$hasPurchasedProduct): ?>
                <div class="alert alert-light">You can review products after you've purchased them and they are marked as completed.</div>
            <?php elseif (!Session::isLoggedIn()): ?>
                <div class="alert alert-info">Please log in to leave a review.</div>
            <?php endif; ?>

            <!-- Existing Reviews -->
            <?php if (empty($reviews)): ?>
                <p>There are no reviews for this product yet. Be the first to leave one!</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="d-flex mb-4 review-item" data-rating="<?php echo $review['rating']; ?>" data-date="<?php echo strtotime($review['created_at']); ?>">
                        <div class="flex-shrink-0 me-3">
                            <div style="width: 50px; height: 50px; background-color: var(--purple-medium); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                <?php 
                                    $initial = '?';
                                    if (!empty($review['first_name'])) {
                                        $initial = strtoupper(substr($review['first_name'], 0, 1));
                                    }
                                    echo $initial;
                                ?>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <?php
                                $displayName = 'Anonymous';
                                if (!empty($review['first_name'])) {
                                    $displayName = htmlspecialchars($review['first_name']);
                                    if (!empty($review['last_name'])) {
                                        $lastNameString = (string) $review['last_name'];
                                        $displayName .= ' ' . htmlspecialchars(substr($lastNameString, 0, 1)) . '.';
                                    }
                                }
                            ?>
                            <h6 class="mt-0 mb-1 fw-bold" style="color: var(--purple-dark);"><?php echo $displayName; ?></h6>
                            <div class="d-flex align-items-center mb-2">
                                <?php echo render_stars($review['rating']); ?>
                                <?php if (Session::isLoggedIn() && $review['user_id'] == $userId): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-3" data-bs-toggle="modal" data-bs-target="#editReviewModal" 
                                            data-review-id="<?php echo $review['id']; ?>" 
                                            data-review-rating="<?php echo $review['rating']; ?>" 
                                            data-review-comment="<?php echo htmlspecialchars($review['comment']); ?>">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                <?php endif; ?>
                            </div>
                            <p class="mt-2"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                            <small class="text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>

                            <?php if (!empty($review['admin_reply'])): ?>
                                <div class="admin-reply mt-3 p-3 rounded" style="background-color: #f0f2f5; border-left: 4px solid var(--purple-medium);">
                                    <h6 class="fw-bold mb-1" style="color: var(--purple-dark);">Store Reply:</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['admin_reply'])); ?></p>
                                    <small class="text-muted">Replied: <?php echo date('F j, Y', strtotime($review['admin_reply_at'])); ?></small>
                                </div>
                            <?php elseif (Session::isAdmin()): ?>
                                <div class="admin-reply-form mt-3 p-3 rounded" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">
                                    <h6 class="fw-bold mb-2" style="color: var(--purple-dark);">Admin Reply:</h6>
                                    <form action="index.php?page=admin_reply_review" method="POST" class="mb-0">
                                        <?php echo CSRF::getTokenField(); ?>
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <div class="mb-2">
                                            <textarea name="admin_reply" rows="2" class="form-control" placeholder="Write your reply..." required style="border-radius: 10px; border: 1px solid #ffc107;"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-sm" style="background-color: var(--purple-dark); color: white; border-radius: 20px; padding: 0.375rem 1rem;">
                                            <i class="fas fa-reply me-1"></i>Reply
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Review Modal -->
<div class="modal fade" id="editReviewModal" tabindex="-1" aria-labelledby="editReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editReviewModalLabel">Edit Your Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=edit_review" method="POST">
                <div class="modal-body">
                    <?php echo CSRF::getTokenField(); ?>
                    <input type="hidden" name="review_id" id="editReviewId">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="mb-3">
                        <label for="editRating" class="form-label">Your Rating</label>
                        <div class="rating-stars">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="editStar<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                <label for="editStar<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editComment" class="form-label">Your Review</label>
                        <textarea name="comment" id="editComment" rows="4" class="form-control" placeholder="Edit your review..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reviewItems = Array.from(document.querySelectorAll('.review-item'));
        const starFilterButtons = document.querySelectorAll('.star-filter-btn');
        const dateSortToggle = document.getElementById('dateSortToggle');
        const sortText = document.getElementById('sortText');
        let currentSort = 'newest'; // 'newest' or 'oldest'

        // Collect review blocks (review + hr)
        const reviewsContainer = document.querySelector('#reviewsCard .card-body');
        const allChildren = Array.from(reviewsContainer.children);
        const reviewBlocks = [];
        let currentBlock = null;
        allChildren.forEach(child => {
            if (child.classList.contains('review-item')) {
                currentBlock = { review: child, hr: null };
                reviewBlocks.push(currentBlock);
            } else if (child.tagName === 'HR' && currentBlock && !currentBlock.hr) {
                currentBlock.hr = child;
            }
        });

        function filterAndSortReviews() {
            const activeRatingFilter = document.querySelector('.star-filter-btn.active').dataset.rating;
            
            // Filter blocks
            const filteredBlocks = reviewBlocks.filter(block => {
                const itemRating = block.review.dataset.rating;
                return activeRatingFilter === 'all' || itemRating === activeRatingFilter;
            });

            // Sort filtered blocks
            const sortedBlocks = filteredBlocks.slice().sort((a, b) => {
                const dateA = parseInt(a.review.dataset.date);
                const dateB = parseInt(b.review.dataset.date);
                return currentSort === 'newest' ? dateB - dateA : dateA - dateB;
            });

            // Remove all review blocks from DOM
            reviewBlocks.forEach(block => {
                if (block.review.parentNode) reviewsContainer.removeChild(block.review);
                if (block.hr && block.hr.parentNode) reviewsContainer.removeChild(block.hr);
            });

            // Find insertion point - after the last non-review element
            let insertAfter = null;
            for (let i = reviewsContainer.children.length - 1; i >= 0; i--) {
                const child = reviewsContainer.children[i];
                if (!child.classList.contains('review-item') && child.tagName !== 'HR') {
                    insertAfter = child;
                    break;
                }
            }

            // Insert sorted blocks
            sortedBlocks.forEach(block => {
                if (insertAfter) {
                    insertAfter.insertAdjacentElement('afterend', block.review);
                    if (block.hr) block.review.insertAdjacentElement('afterend', block.hr);
                    insertAfter = block.hr || block.review;
                } else {
                    reviewsContainer.appendChild(block.review);
                    if (block.hr) reviewsContainer.appendChild(block.hr);
                    insertAfter = block.hr || block.review;
                }
            });

            // Handle no reviews message
            let noMatchMessage = document.querySelector('.no-reviews-match');
            if (sortedBlocks.length === 0) {
                if (!noMatchMessage) {
                    noMatchMessage = document.createElement('p');
                    noMatchMessage.className = 'text-center text-muted no-reviews-match';
                    noMatchMessage.textContent = 'No reviews match the selected filter.';
                    // Insert after the last inserted block or at the end
                    if (insertAfter) {
                        insertAfter.insertAdjacentElement('afterend', noMatchMessage);
                    } else {
                        reviewsContainer.appendChild(noMatchMessage);
                    }
                }
            } else if (noMatchMessage) {
                noMatchMessage.remove();
            }
        }

        starFilterButtons.forEach(button => {
            button.addEventListener('click', function() {
                starFilterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                filterAndSortReviews();
            });
        });

        dateSortToggle.addEventListener('click', function() {
            currentSort = currentSort === 'newest' ? 'oldest' : 'newest';
            sortText.textContent = currentSort === 'newest' ? 'Newest First' : 'Oldest First';
            this.querySelector('i').classList.toggle('fa-clock');
            this.querySelector('i').classList.toggle('fa-history');
            filterAndSortReviews();
        });

        // Initial sort and filter
        filterAndSortReviews();

        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(reviewForm);
                
                fetch('index.php?page=add_review', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Create new review element
                        const newReview = document.createElement('div');
                        newReview.className = 'd-flex mb-4 review-item';
                        newReview.dataset.rating = data.review.rating;
                        newReview.dataset.date = Math.floor(Date.now() / 1000);
                        
                        const initial = data.review.first_name ? data.review.first_name.charAt(0).toUpperCase() : '?';
                        const displayName = data.review.first_name ? data.review.first_name + (data.review.last_name ? ' ' + data.review.last_name.charAt(0) + '.' : '') : 'Anonymous';

                        newReview.innerHTML = `
                            <div class="flex-shrink-0 me-3">
                                <div style="width: 50px; height: 50px; background-color: var(--purple-medium); color: white; border-radius: 50%; display: flex; align-items-center; justify-content: center; font-weight: bold;">
                                    ${initial}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mt-0 mb-1 fw-bold" style="color: var(--purple-dark);">${displayName}</h6>
                                <div class="d-flex align-items-center mb-2">
                                    ${renderStarsJS(data.review.rating)}
                                </div>
                                <p class="mt-2">${data.review.comment}</p>
                                <small class="text-muted">Just now</small>
                            </div>
                        `;

                        // Add the new review to the page
                        const reviewsContainer = document.querySelector('#reviewsCard .card-body');
                        const noReviewsMessage = reviewsContainer.querySelector('p');
                        if (noReviewsMessage && noReviewsMessage.textContent.includes('no reviews')) {
                            noReviewsMessage.remove();
                        }
                        reviewsContainer.insertBefore(newReview, reviewsContainer.children[4]); // Adjust index if needed

                        // Update reviewItems and re-filter/sort
                        reviewItems.push(newReview);
                        filterAndSortReviews();

                        // Reset form
                        reviewForm.reset();
                        // Hide the form after successful submission
                        reviewForm.parentElement.style.display = 'none';

                        // Show a success message
                        const successAlert = document.createElement('div');
                        successAlert.className = 'alert alert-success';
                        successAlert.textContent = 'Your review has been submitted!';
                        reviewsContainer.insertBefore(successAlert, reviewsContainer.firstChild);

                    } else {
                        // Handle error
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger';
                        errorAlert.textContent = data.message || 'An error occurred.';
                        reviewForm.parentElement.insertBefore(errorAlert, reviewForm);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }

        function renderStarsJS(rating) {
            let starsHtml = '<div class="d-flex align-items-center">';
            starsHtml += '<div class="stars-outer" style="font-size: 1.2rem; color: #d3d3d3; position: relative; display: inline-block;">';
            starsHtml += '<div class="stars-inner" style="color: #ffc107; position: absolute; top: 0; left: 0; white-space: nowrap; overflow: hidden; width: ' + (rating / 5 * 100) + '%;">';
            for (let i = 0; i < 5; i++) {
                starsHtml += '<i class="fas fa-star"></i>';
            }
            starsHtml += '</div>';
            for (let i = 0; i < 5; i++) {
                starsHtml += '<i class="far fa-star"></i>';
            }
            starsHtml += '</div>';
            starsHtml += '</div>';
            return starsHtml;
        }
    });
    // JavaScript to handle modal population
    var editReviewModal = document.getElementById('editReviewModal');
    editReviewModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var reviewId = button.getAttribute('data-review-id');
        var reviewRating = button.getAttribute('data-review-rating');
        var reviewComment = button.getAttribute('data-review-comment');

        var modalReviewId = editReviewModal.querySelector('#editReviewId');
        var modalEditComment = editReviewModal.querySelector('#editComment');
        
        modalReviewId.value = reviewId;
        modalEditComment.value = reviewComment;

        // Set the rating
        editReviewModal.querySelectorAll('input[name="rating"]').forEach(radio => {
            if (radio.value == reviewRating) {
                radio.checked = true;
            }
        });
    });

    // Scripts from original file remain the same
    let currentImageIndex = 0;
    <?php
    echo "const productImages = " . json_encode(array_map(function($img) {
        return ['path' => 'uploads/' . $img['image_path']];
    }, $displayImages)) . ";";
    ?>
    document.addEventListener('DOMContentLoaded', function() {
        initializeSimpleGallery();
        setupKeyboardNavigation();
    });
    function initializeSimpleGallery() { updateNavigationButtons(); preloadImages(); }
    function preloadImages() { productImages.forEach((image, index) => { const img = new Image(); img.src = image.path; }); }
    function changeMainImage(index, thumbnailElement = null) {
        if (index < 0 || index >= productImages.length) return;
        const mainImage = document.getElementById('mainProductImage');
        const loadingIndicator = document.querySelector('.image-loading');
        loadingIndicator.style.display = 'block';
        mainImage.style.opacity = '0.3';
        currentImageIndex = index;
        const newImage = new Image();
        newImage.onload = function() {
            mainImage.src = productImages[index].path;
            mainImage.style.opacity = '1';
            loadingIndicator.style.display = 'none';
        };
        newImage.src = productImages[index].path;
        updateActiveThumbnail(index);
        updateImageCounter();
        updateNavigationButtons();
    }
    function updateActiveThumbnail(activeIndex) {
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
        if (counter) { counter.textContent = currentImageIndex + 1; }
    }
    function updateNavigationButtons() {
        const prevBtn = document.querySelector('.nav-prev');
        const nextBtn = document.querySelector('.nav-next');
        if (prevBtn && nextBtn) {
            prevBtn.style.opacity = '1';
            nextBtn.style.opacity = '1';
            prevBtn.disabled = false;
            nextBtn.disabled = false;
        }
    }
    function navigateImage(direction) {
        let newIndex = currentImageIndex + direction;
        if (newIndex < 0) {
            newIndex = productImages.length - 1;
        } else if (newIndex >= productImages.length) {
            newIndex = 0;
        }
        changeMainImage(newIndex);
    }
    function setupKeyboardNavigation() {
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (e.key === 'ArrowLeft') { e.preventDefault(); navigateImage(-1); }
            if (e.key === 'ArrowRight') { e.preventDefault(); navigateImage(1); }
        });
    }
    function increaseQty() {
        const input = document.getElementById('quantity');
        const max = parseInt(input.max);
        const current = parseInt(input.value);
        if (current < max) { input.value = current + 1; }
    }
    function decreaseQty() {
        const input = document.getElementById('quantity');
        const min = parseInt(input.min);
        const current = parseInt(input.value);
        if (current > min) { input.value = current - 1; }
    }
</script>

<style>
.qty-btn {
    transition: all 0.3s ease;
    background: var(--purple-medium) !important;
    color: white !important;
    border-color: var(--purple-medium) !important;
    border-radius: 0 !important;
}

.qty-btn:hover,
.qty-btn:active {
    background: var(--purple-dark) !important;
    color: white !important;
    border-color: var(--purple-dark) !important;
}

.qty-btn:active {
    transform: scale(0.95);
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

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
