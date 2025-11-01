<?php
$pageTitle = 'Edit Product - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-4">Edit Product</h3>
                
                <form method="POST" action="admin.php?page=edit_product&id=<?php echo $product['id']; ?>" enctype="multipart/form-data">
                    <?php echo CSRF::getTokenField(); ?>
                    
                    <div class="form-group mb-3">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="product_name">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" 
                               value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cost_price">Cost Price</label>
                                <input type="number" step="0.01" class="form-control" id="cost_price" name="cost_price" 
                                       value="<?php echo $product['cost_price']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="selling_price">Selling Price</label>
                                <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price" 
                                       value="<?php echo $product['selling_price']; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               value="<?php echo $inventory; ?>" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> If you increase the quantity, an expense will be automatically recorded 
                            (Cost Price × Additional Stock) under the "Inventory" category.
                        </small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="is_active">Status</label>
                        <select class="form-control" id="is_active" name="is_active" required>
                            <option value="1" <?php echo ($product['is_active'] == 1) ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo ($product['is_active'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <small class="form-text text-muted">Inactive products won't be visible to customers</small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="img_path">Product Image</label>
                        <?php if ($product['img_path']): ?>
                            <div class="mb-2">
                                <img src="uploads/<?php echo htmlspecialchars($product['img_path']); ?>" alt="Current Image" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                        <input class="form-control" type="file" id="img_path" name="img_path" accept="image/*">
                        <small class="form-text text-muted">Leave empty to keep current image</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" name="submit">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="admin.php?page=products" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>