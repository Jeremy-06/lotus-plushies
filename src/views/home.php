<?php
$pageTitle = 'Home - Online Shop';
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

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Welcome to Our Online Shop</h1>
        <p class="lead">Browse our collection of quality products</p>
    </div>
</div>

<?php if (!empty($categories)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <h3>Categories</h3>
        <div class="btn-group" role="group">
            <a href="index.php?page=products" class="btn btn-outline-primary">All Products</a>
            <?php foreach ($categories as $category): ?>
                <a href="index.php?page=products&category=<?php echo $category['id']; ?>" class="btn btn-outline-primary">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <?php if (!empty($products)): ?>
        <?php foreach (array_slice($products, 0, 8) as $product): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <?php if ($product['img_path']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($product['img_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                        <span class="text-white">No Image</span>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                    <p class="card-text"><strong>₱<?php echo number_format($product['selling_price'], 2); ?></strong></p>
                    <p class="card-text"><small class="text-muted">Stock: <?php echo $product['quantity_on_hand']; ?></small></p>
                    <a href="index.php?page=product_detail&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-md-12">
            <p class="text-center">No products available at the moment.</p>
        </div>
    <?php endif; ?>
</div>

<div class="row mt-4">
    <div class="col-md-12 text-center">
        <a href="index.php?page=products" class="btn btn-primary btn-lg">View All Products</a>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>