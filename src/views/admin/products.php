<?php
$pageTitle = 'Manage Products - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
?>

<div class="row mb-4 mt-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0" style="margin-top: 0;">Manage Products</h2>
            <div class="d-flex gap-2">
                <a href="admin.php?page=create_product" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
                <a href="admin.php?page=categories" class="btn btn-info">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="admin.php?page=suppliers" class="btn btn-warning">
                    <i class="fas fa-building"></i> Suppliers
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <form action="admin.php" method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? 'created_at'); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($_GET['order'] ?? 'DESC'); ?>">
            
            <div class="input-group" style="max-width: 400px;">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                       style="border: 2px solid #8b5fbf; border-radius: 25px 0 0 25px; padding: 10px 20px;">
                <button type="submit" class="btn" style="background: #8b5fbf; color: white; border: 2px solid #8b5fbf; border-radius: 0 25px 25px 0; padding: 10px 20px;">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            
            <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                <a href="admin.php?page=products&sort=<?php echo htmlspecialchars($_GET['sort'] ?? 'created_at'); ?>&order=<?php echo htmlspecialchars($_GET['order'] ?? 'DESC'); ?>" 
                   class="btn" style="background: #6c757d; color: white; border-radius: 25px; padding: 10px 20px; text-decoration: none;">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (!empty($products)): ?>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Image</th>
                <th>
                    <a href="admin.php?page=products&sort=product_name&order=<?php echo ($_GET['sort'] ?? '') === 'product_name' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="text-white text-decoration-none">
                        Product Name <?php if (($_GET['sort'] ?? '') === 'product_name') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=products&sort=category_name&order=<?php echo ($_GET['sort'] ?? '') === 'category_name' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="text-white text-decoration-none">
                        Category <?php if (($_GET['sort'] ?? '') === 'category_name') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=products&sort=supplier_name&order=<?php echo ($_GET['sort'] ?? '') === 'supplier_name' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="text-white text-decoration-none">
                        Supplier <?php if (($_GET['sort'] ?? '') === 'supplier_name') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=products&sort=cost_price&order=<?php echo ($_GET['sort'] ?? '') === 'cost_price' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="text-white text-decoration-none">
                        Cost Price <?php if (($_GET['sort'] ?? '') === 'cost_price') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=products&sort=selling_price&order=<?php echo ($_GET['sort'] ?? '') === 'selling_price' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="text-white text-decoration-none">
                        Selling Price <?php if (($_GET['sort'] ?? '') === 'selling_price') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=products&sort=quantity_on_hand&order=<?php echo ($_GET['sort'] ?? '') === 'quantity_on_hand' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="text-white text-decoration-none">
                        Stock <?php if (($_GET['sort'] ?? '') === 'quantity_on_hand') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=products&sort=is_active&order=<?php echo ($_GET['sort'] ?? '') === 'is_active' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="text-white text-decoration-none">
                        Status <?php if (($_GET['sort'] ?? '') === 'is_active') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td>
                    <?php if ($product['img_path']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['img_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center position-relative" style="width: 50px; height: 50px; border-radius: 8px; background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(255, 159, 191, 0.15) 100%); overflow: hidden;">
                            <!-- Decorative background circles -->
                            <div class="position-absolute" style="top: -20%; right: -10%; width: 30px; height: 30px; background: rgba(139, 95, 191, 0.1); border-radius: 50%; filter: blur(10px);"></div>
                            <div class="position-absolute" style="bottom: -20%; left: -10%; width: 25px; height: 25px; background: rgba(255, 159, 191, 0.15); border-radius: 50%; filter: blur(8px);"></div>
                            
                            <div class="position-relative text-center">
                                <div class="mb-1" style="animation: float 3s ease-in-out infinite;">
                                    <i class="fas fa-box-open" style="font-size: 1.2rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                </div>
                                <p class="mb-0 fw-bold" style="color: var(--purple-medium); font-size: 0.6rem; letter-spacing: 0.5px;">No Image</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                <td><?php echo $product['supplier_name'] ? htmlspecialchars($product['supplier_name']) : '<span class="text-muted">N/A</span>'; ?></td>
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
                    <div class="btn-group" role="group">
                        <a href="admin.php?page=edit_product&id=<?php echo $product['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: linear-gradient(135deg, #b19cd9 0%, #d6b6ff 100%); color: white; border: none; border-radius: 20px 0 0 20px; padding: 8px 16px;"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button"
                           class="btn btn-sm delete-product-btn"
                           data-product-id="<?php echo $product['id']; ?>"
                           style="background: #dc3545; color: white; border: none; border-radius: 0 20px 20px 0; padding: 8px 16px;"
                           title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
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

<style>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete product button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-product-btn') || e.target.closest('.delete-product-btn')) {
            e.preventDefault();
            const button = e.target.classList.contains('delete-product-btn') ? e.target : e.target.closest('.delete-product-btn');
            const productId = button.getAttribute('data-product-id');
            
            showConfirmation(
                '<i class="fas fa-trash text-danger me-2"></i>Delete Product',
                'Are you sure you want to delete this product? This action cannot be undone.',
                `admin.php?page=delete_product&id=${productId}`
            );
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>