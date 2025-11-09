<?php
$pageTitle = 'Products - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="mb-0" style="margin-top: 0;">Products</h2>
    </div>
    <div class="col-md-6">
        <form action="index.php" method="GET" class="d-flex">
            <input type="hidden" name="page" value="products">
            <input class="form-control me-2 shadow-sm" type="search" placeholder="Search products" name="search" 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                   style="border-radius: 25px; border: 1px solid var(--purple-medium); padding: 0.5rem 1rem;">
            <button class="btn shadow-sm" type="submit" style="border-radius: 25px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; border: none; padding: 0.5rem 1.5rem;">
                <i class="fas fa-search me-1"></i>Search
            </button>
        </form>
    </div>
</div>

<?php if (!empty($categories)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="dropdown products-dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" 
                    type="button" 
                    id="categoryDropdown" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                    style="min-width: 200px;">
                <i class="fas fa-th-large me-2"></i>
                <?php 
                if (isset($_GET['category'])) {
                    $selectedCategory = array_filter($categories, function($cat) {
                        return $cat['id'] == $_GET['category'];
                    });
                    if (!empty($selectedCategory)) {
                        echo htmlspecialchars(reset($selectedCategory)['category_name']);
                    } else {
                        echo 'All Products';
                    }
                } else {
                    echo 'All Products';
                }
                ?>
            </button>
            <ul class="dropdown-menu products-dropdown-menu" aria-labelledby="categoryDropdown" style="max-height: 400px; overflow-y: auto;">
                <li>
                    <a class="dropdown-item <?php echo !isset($_GET['category']) ? 'active' : ''; ?>" 
                       href="index.php?page=products">
                        <i class="fas fa-th-large me-2"></i>All Products
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a class="dropdown-item <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'active' : ''; ?>" 
                           href="index.php?page=products&category=<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <?php if ($product['img_path']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($product['img_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="height: 300px; width: 100%; object-fit: contain; background: #f8f9fa; aspect-ratio: 1/1;">
                <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center position-relative" style="height: 300px; aspect-ratio: 1/1; background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(255, 159, 191, 0.15) 100%); overflow: hidden;">
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
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                    <p class="card-text"><strong>₱<?php echo number_format($product['selling_price'], 2); ?></strong></p>
                    <p class="card-text">
                        <?php if ($product['quantity_on_hand'] > 0): ?>
                            <small class="text-success">In Stock: <?php echo $product['quantity_on_hand']; ?></small>
                        <?php else: ?>
                            <small class="text-danger">Out of Stock</small>
                        <?php endif; ?>
                    </p>
                    <a href="index.php?page=product_detail&id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-md-12">
            <div class="alert alert-info text-center">
                No products found. <?php echo isset($_GET['search']) ? 'Try a different search term.' : ''; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($products) && $totalPages > 1): ?>
<style>
.modern-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 2.5rem;
    margin-bottom: 1rem;
}

.modern-pagination .page-btn {
    min-width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    background: white;
    color: var(--purple-dark);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.modern-pagination .page-btn:hover:not(.disabled):not(.active) {
    background: var(--purple-light);
    border-color: var(--purple-medium);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 95, 191, 0.2);
}

.modern-pagination .page-btn.active {
    background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%);
    color: white;
    border-color: var(--purple-dark);
    box-shadow: 0 4px 12px rgba(139, 95, 191, 0.4);
}

.modern-pagination .page-btn.disabled {
    background: #f5f5f5;
    color: #ccc;
    cursor: not-allowed;
    box-shadow: none;
}

.modern-pagination .page-btn.ellipsis {
    background: transparent;
    box-shadow: none;
    cursor: default;
    color: #999;
}

.modern-pagination .nav-btn {
    width: 40px;
    padding: 0;
}

.pagination-info {
    text-align: center;
    margin-top: 1rem;
    margin-bottom: 2rem;
}

.pagination-info .info-badge {
    display: inline-block;
    padding: 8px 20px;
    background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(255, 159, 191, 0.1) 100%);
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    color: var(--purple-dark);
    border: 1px solid var(--purple-light);
}
</style>

<nav aria-label="Product pagination">
    <div class="modern-pagination">
        <?php
        // Build query parameters for pagination links
        $queryParams = [];
        if (isset($_GET['category'])) {
            $queryParams[] = 'category=' . urlencode($_GET['category']);
        }
        if (isset($_GET['search'])) {
            $queryParams[] = 'search=' . urlencode($_GET['search']);
        }
        $baseQuery = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
        
        // Previous button
        if ($currentPage > 1):
        ?>
            <a class="page-btn nav-btn" href="index.php?page=products&pg=<?php echo ($currentPage - 1) . $baseQuery; ?>" aria-label="Previous">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php else: ?>
            <span class="page-btn nav-btn disabled">
                <i class="fas fa-chevron-left"></i>
            </span>
        <?php endif; ?>
        
        <?php
        // Calculate page range to display
        $range = 2; // Show 2 pages on each side of current page
        $startPage = max(1, $currentPage - $range);
        $endPage = min($totalPages, $currentPage + $range);
        
        // First page
        if ($startPage > 1):
        ?>
            <a class="page-btn" href="index.php?page=products&pg=1<?php echo $baseQuery; ?>">1</a>
            <?php if ($startPage > 2): ?>
                <span class="page-btn ellipsis">...</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php
        // Page numbers
        for ($i = $startPage; $i <= $endPage; $i++):
        ?>
            <a class="page-btn <?php echo ($i == $currentPage) ? 'active' : ''; ?>" 
               href="index.php?page=products&pg=<?php echo $i . $baseQuery; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php
        // Last page
        if ($endPage < $totalPages):
            if ($endPage < $totalPages - 1):
        ?>
                <span class="page-btn ellipsis">...</span>
            <?php endif; ?>
            <a class="page-btn" href="index.php?page=products&pg=<?php echo $totalPages . $baseQuery; ?>">
                <?php echo $totalPages; ?>
            </a>
        <?php endif; ?>
        
        <?php
        // Next button
        if ($currentPage < $totalPages):
        ?>
            <a class="page-btn nav-btn" href="index.php?page=products&pg=<?php echo ($currentPage + 1) . $baseQuery; ?>" aria-label="Next">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php else: ?>
            <span class="page-btn nav-btn disabled">
                <i class="fas fa-chevron-right"></i>
            </span>
        <?php endif; ?>
    </div>
</nav>

<div class="pagination-info">
    <span class="info-badge">
        <i class="fas fa-box me-2"></i>
        Showing page <?php echo $currentPage; ?> of <?php echo $totalPages; ?> 
        (<?php echo $totalProducts; ?> product<?php echo $totalProducts != 1 ? 's' : ''; ?> total)
    </span>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>