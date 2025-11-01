<?php
$pageTitle = 'Manage Products - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
?>

<div class="row mb-4 mt-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0" style="margin-top: 0;">Manage Products</h2>
            <a href="admin.php?page=create_product" class="btn btn-success">
                <i class="fas fa-plus"></i> Add New Product
            </a>
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
                <th>Status</th>
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
                        <div class="d-flex align-items-center justify-content-center position-relative" style="width: 50px; height: 50px; border-radius: 8px; background: linear-gradient(135deg, rgba(139, 95, 191, 0.15) 0%, rgba(255, 159, 191, 0.2) 100%); overflow: hidden;">
                            <div class="position-absolute" style="top: -30%; right: -20%; width: 30px; height: 30px; background: rgba(139, 95, 191, 0.2); border-radius: 50%; filter: blur(10px);"></div>
                            <i class="fas fa-box-open" style="font-size: 1.5rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
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
                    <div class="btn-group" role="group">
                        <a href="admin.php?page=edit_product&id=<?php echo $product['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: linear-gradient(135deg, #b19cd9 0%, #d6b6ff 100%); color: white; border: none; border-radius: 20px 0 0 20px; padding: 8px 16px;"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="admin.php?page=delete_product&id=<?php echo $product['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: #dc3545; color: white; border: none; border-radius: 0 20px 20px 0; padding: 8px 16px;"
                           title="Delete"
                           onclick="return confirm('Are you sure you want to delete this product?')">
                            <i class="fas fa-trash"></i>
                        </a>
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>