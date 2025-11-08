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
                        <label for="supplier_id">Supplier</label>
                        <select class="form-control" id="supplier_id" name="supplier_id">
                            <option value="">Select Supplier (Optional)</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['id']; ?>" 
                                    <?php echo (isset($formData['supplier_id']) && $formData['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supplier['supplier_name']); ?>
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
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> An expense will be automatically recorded if quantity > 0 
                            (Cost Price × Quantity) under the "Inventory" category.
                        </small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="img_path">Product Images</label>
                        <input class="form-control" type="file" id="img_path" name="img_path[]" multiple accept="image/*">
                        <small class="form-text text-muted">Allowed: JPG, JPEG, PNG, GIF, WEBP (Max 5MB each). You can select multiple images.</small>
                        <?php if (Session::getFlash('imageError')): ?>
                            <small class="text-danger d-block"><?php echo Session::getFlash('imageError'); ?></small>
                        <?php endif; ?>
                        
                        <!-- Image Preview Section -->
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <div class="preview-header d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="fas fa-images"></i> Selected Images Preview</h6>
                                <small class="text-muted" id="fileCount"></small>
                            </div>
                            <div id="previewContainer" class="row g-3"></div>
                        </div>
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

<script>
// Enhanced file upload with preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('img_path');
    const fileCount = document.getElementById('fileCount');
    const imagePreview = document.getElementById('imagePreview');
    const previewContainer = document.getElementById('previewContainer');

    // File input change handler
    fileInput.addEventListener('change', function(e) {
        const files = e.target.files;
        updateFileCount(files.length);
        showImagePreview(files);
    });

    function updateFileCount(count) {
        if (count > 0) {
            fileCount.innerHTML = `<i class="fas fa-check-circle text-success"></i> ${count} file${count > 1 ? 's' : ''} selected`;
            imagePreview.style.display = 'block';
        } else {
            fileCount.innerHTML = '';
            imagePreview.style.display = 'none';
        }
    }

    function showImagePreview(files) {
        if (files.length === 0) {
            imagePreview.style.display = 'none';
            return;
        }

        previewContainer.innerHTML = '';
        imagePreview.style.display = 'block';

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'col-md-3 col-sm-6';
                    previewItem.innerHTML = `
                        <div class="preview-item position-relative" style="border: 2px solid #e9ecef; border-radius: 10px; overflow: hidden; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <img src="${e.target.result}" alt="Preview ${index + 1}" class="preview-image" style="width: 100%; height: 150px; object-fit: cover;">
                            <div class="preview-info p-2" style="background: rgba(0,0,0,0.7); color: white; font-size: 0.8rem;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>${file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name}</span>
                                    <span>${(file.size / 1024 / 1024).toFixed(1)}MB</span>
                                </div>
                            </div>
                            ${index === 0 ? '<div class="primary-badge" style="position: absolute; top: 8px; left: 8px; background: #28a745; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: bold;">PRIMARY</div>' : ''}
                            <button type="button" class="btn btn-outline-danger btn-sm remove-preview position-absolute" data-index="${index}" style="top: 8px; right: 8px; border-radius: 50%; width: 24px; height: 24px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    previewContainer.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Remove preview item
    previewContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-preview') || e.target.closest('.remove-preview')) {
            const button = e.target.classList.contains('remove-preview') ? e.target : e.target.closest('.remove-preview');
            const index = parseInt(button.getAttribute('data-index'));

            // Remove from FileList (create new FileList without the removed file)
            const dt = new DataTransfer();
            const files = Array.from(fileInput.files);
            files.splice(index, 1);
            files.forEach(file => dt.items.add(file));
            fileInput.files = dt.files;

            updateFileCount(fileInput.files.length);
            showImagePreview(fileInput.files);
        }
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const files = fileInput.files;
        if (files.length > 10) {
            e.preventDefault();
            alert('Maximum 10 images allowed per product.');
            return;
        }

        // Check file sizes
        for (let file of files) {
            if (file.size > 5 * 1024 * 1024) { // 5MB
                e.preventDefault();
                alert(`File "${file.name}" is too large. Maximum size is 5MB.`);
                return;
            }
        }
    });
});
</script>

<style>
.preview-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.preview-image {
    transition: transform 0.3s ease;
}

.preview-item:hover .preview-image {
    transform: scale(1.05);
}

.primary-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.remove-preview {
    transition: all 0.3s ease;
}

.remove-preview:hover {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: white !important;
    transform: scale(1.1);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>