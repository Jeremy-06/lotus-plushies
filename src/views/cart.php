<?php
$pageTitle = 'Shopping Cart - Online Shop';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';
?>

<h2 class="mb-4">Shopping Cart</h2>

<?php if (!empty($cartItems)): ?>
<form method="POST" action="index.php?page=cart&action=update">
    <?php echo CSRF::getTokenField(); ?>
    
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <?php $itemSubtotal = $item['selling_price'] * $item['quantity']; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <?php if ($item['img_path']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['img_path']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 60px; height: 60px; object-fit: cover;" class="me-3">
                            <?php endif; ?>
                            <div>
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                                <small class="text-muted">Available: <?php echo $item['quantity_on_hand']; ?></small>
                            </div>
                        </div>
                    </td>
                    <td>₱<?php echo number_format($item['selling_price'], 2); ?></td>
                    <td>
                        <input type="number" name="product_qty[<?php echo $item['product_id']; ?>]" 
                               value="<?php echo $item['quantity']; ?>" 
                               min="1" max="<?php echo $item['quantity_on_hand']; ?>" 
                               class="form-control" style="width: 80px;">
                    </td>
                    <td><strong>₱<?php echo number_format($itemSubtotal, 2); ?></strong></td>
                    <td class="text-center">
                        <input type="checkbox" name="remove_code[]" value="<?php echo $item['product_id']; ?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td colspan="2"><strong>₱<?php echo number_format($cartTotal, 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="d-flex justify-content-between mt-3">
        <a href="index.php?page=products" class="btn btn-secondary">Continue Shopping</a>
        <div>
            <button type="submit" class="btn btn-primary">Update Cart</button>
            <a href="index.php?page=checkout" class="btn btn-success">Proceed to Checkout</a>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>