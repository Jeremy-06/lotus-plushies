<?php
$pageTitle = htmlspecialchars($product['product_name']) . ' - Online Shop';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';
?>

<div class="row">
    <div class="col-md-6">
        <?php if ($product['img_path']): ?>
            <img src="uploads/<?php echo htmlspecialchars($product['img_path']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        <?php else: ?>
            <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 400px;">
                <span class="text-white h1">No Image</span>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
        <hr>
        <h3 class="text-primary">₱<?php echo number_format($product['selling_price'], 2); ?></h3>
        
        <p class="mt-3"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        
        <div class="mt-4">
            <?php if ($inventory > 0): ?>
                <p class="text-success"><strong>In Stock: <?php echo $inventory; ?> available</strong></p>
                
                <?php if (Session::isLoggedIn()): ?>
                    <form method="POST" action="index.php?page=cart&action=add">
                        <?php echo CSRF::getTokenField(); ?>
                        <input type="hidden" name="type" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="form-group mb-3">
                            <label for="quantity">Quantity:</label>
                            <input type="number" class="form-control" id="quantity" name="product_qty" value="1" min="1" max="<?php echo $inventory; ?>" style="width: 100px;">
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Please <a href="index.php?page=login">login</a> to add items to cart
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-danger"><strong>Out of Stock</strong></p>
                <button class="btn btn-secondary btn-lg" disabled>Add to Cart</button>
            <?php endif; ?>
        </div>
        
        <div class="mt-4">
            <a href="index.php?page=products" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>