<?php
$pageTitle = 'Add New Product - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';

$formData = Session::get('form_data', []);
Session::remove('form_data');
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-4">Add New Product</h3>
                
                <form method="POST" action="admin.php?page=create_product" enctype="multipart/form-data">
                    <?php echo CSRF::getTokenField(); ?>
                    
                    <div class="form-group mb-3">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo (isset($formData['category_id']) && $formData['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="product_name">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" 
                               placeholder="Enter product name" 
                               value="<?php echo $formData['product_name'] ?? ''; ?>" required>
                        <?php if (Session::getFlash('product_nameError')): ?>
                            <small class="text-danger"><?php echo Session::getFlash('product_nameError'); ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Enter product description" required><?php echo $formData['description'] ?? ''; ?></textarea>
                        <?php if (Session::getFlash('descriptionError')): ?>
                            <small class="text-danger"><?php echo Session::getFlash('descriptionError'); ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cost_price">Cost Price</label>
                                <input type="number" step="0.01" class="form-control" id="cost_price" name="cost_price" 
                                       placeholder="0.00" 
                                       value="<?php echo $formData['cost_price'] ?? ''; ?>" required>
                                <?php if (Session::getFlash('cost_priceError')): ?>
                                    <small class="text-danger"><?php echo Session::getFlash('cost_priceError'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="selling_price">Selling Price</label>
                                <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price" 
                                       placeholder="0.00" 
                                       value="<?php echo $formData['selling_price'] ?? ''; ?>" required>
                                <?php if (Session::getFlash('selling_priceError')): ?>
                                    <small class="text-danger"><?php echo Session::getFlash('selling_priceError'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               placeholder="0" 
                               value="<?php echo $formData['quantity'] ?? '0'; ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="img_path">Product Image</label>
                        <input class="form-control" type="file" id="img_path" name="img_path" accept="image/*">
                        <small class="form-text text-muted">Allowed: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                        <?php if (Session::getFlash('imageError')): ?>
                            <small class="text-danger d-block"><?php echo Session::getFlash('imageError'); ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" name="submit">
                            <i class="fas fa-save"></i> Create Product
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