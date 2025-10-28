<?php
$pageTitle = 'Products - Online Shop';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Products</h2>
    </div>
    <div class="col-md-6">
        <form action="index.php" method="GET" class="d-flex">
            <input type="hidden" name="page" value="products">
            <input class="form-control me-2" type="search" placeholder="Search products" name="search" 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
    </div>
</div>

<?php if (!empty($categories)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="btn-group" role="group">
            <a href="index.php?page=products" class="btn btn-outline-primary <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                All Products
            </a>
            <?php foreach ($categories as $category): ?>
                <a href="index.php?page=products&category=<?php echo $category['id']; ?>" 
                   class="btn btn-outline-primary <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                </a>
            <?php endforeach; ?>
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
                    <img src="uploads/<?php echo htmlspecialchars($product['img_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="height: 250px; object-fit: cover;">
                <?php else: ?>
                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 250px;">
                        <span class="text-white">No Image</span>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>