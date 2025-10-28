<?php
$pageTitle = 'Checkout - Online Shop';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';
?>

<h2 class="mb-4">Checkout</h2>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Order Summary</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₱<?php echo number_format($item['selling_price'], 2); ?></td>
                            <td>₱<?php echo number_format($item['selling_price'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Order Total</h5>
                <table class="table table-sm">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end">₱<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Shipping:</td>
                        <td class="text-end">₱<?php echo number_format($shippingCost, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Tax (12%):</td>
                        <td class="text-end">₱<?php echo number_format($taxAmount, 2); ?></td>
                    </tr>
                    <tr class="table-active">
                        <td><strong>Total:</strong></td>
                        <td class="text-end"><strong>₱<?php echo number_format($totalAmount, 2); ?></strong></td>
                    </tr>
                </table>
                
                <form method="POST" action="index.php?page=checkout&action=process">
                    <?php echo CSRF::getTokenField(); ?>
                    <button type="submit" name="place_order" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                </form>
                
                <a href="index.php?page=cart" class="btn btn-outline-secondary w-100">Back to Cart</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>