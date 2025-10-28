<?php
$pageTitle = 'Manage Products - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2>Manage Products</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="admin.php?page=create_product" class="btn btn-success">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <form action="admin.php" method="GET" class="d-flex">
            <input type="hidden" name="page" value="products">
            <input class="form-control me-2" type="search" placeholder="Search products" name="search" 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
    </div>
</div>

<?php if (!empty($products)): ?>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Cost Price</th>
                <th>Selling Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td>
                    <?php if ($product['img_path']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['img_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 10px;">
                            No Image
                        </div>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                <td>₱<?php echo number_format($product['cost_price'], 2); ?></td>
                <td>₱<?php echo number_format($product['selling_price'], 2); ?></td>
                <td>
                    <?php if ($product['quantity_on_hand'] > 10): ?>
                        <span class="badge bg-success"><?php echo $product['quantity_on_hand']; ?></span>
                    <?php elseif ($product['quantity_on_hand'] > 0): ?>
                        <span class="badge bg-warning"><?php echo $product['quantity_on_hand']; ?></span>
                    <?php else: ?>
                        <span class="badge bg-danger"><?php echo $product['quantity_on_hand']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($product['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="admin.php?page=edit_product&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="admin.php?page=delete_product&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info text-center">
    No products found. <a href="admin.php?page=create_product">Add your first product</a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>