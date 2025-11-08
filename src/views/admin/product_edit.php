<?php
$pageTitle = 'Edit Product - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-edit"></i> Edit Product</h2>
                <a href="admin.php?page=products" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <form method="POST" action="admin.php?page=edit_product&id=<?php echo $product['id']; ?>" id="productEditForm">
                <?php echo CSRF::getTokenField(); ?>

                <script>
                    // Make CSRF token available to JavaScript
                    window.csrfToken = '<?php echo CSRF::generateToken(); ?>';
                </script>

                <div class="row">
                    <!-- Left Column: Product Details -->
                    <div class="col-lg-7">
                        <div class="form-section" style="box-shadow: 0 8px 25px rgba(0,0,0,0.1), 0 4px 10px rgba(139, 95, 191, 0.1); border-radius: 15px; padding: 2rem; background: white; border: 1px solid rgba(139, 95, 191, 0.1);">
                            <h5 class="section-header"><i class="fas fa-info-circle"></i> Product Information</h5>

                            <div class="form-group mb-3">
                                <label for="product_name"><i class="fas fa-box"></i> Product Name *</label>
                                <input type="text" class="form-control" id="product_name" name="product_name"
                                       value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description"><i class="fas fa-align-left"></i> Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="category_id"><i class="fas fa-tag"></i> Category *</label>
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
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="supplier_id"><i class="fas fa-truck"></i> Supplier</label>
                                        <select class="form-control" id="supplier_id" name="supplier_id">
                                            <option value="">Select Supplier (Optional)</option>
                                            <?php foreach ($suppliers as $supplier): ?>
                                                <option value="<?php echo $supplier['id']; ?>"
                                                    <?php echo ($product['supplier_id'] == $supplier['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="cost_price"><i class="fas fa-dollar-sign"></i> Cost Price *</label>
                                        <input type="number" step="0.01" class="form-control" id="cost_price" name="cost_price"
                                               value="<?php echo $product['cost_price']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="selling_price"><i class="fas fa-money-bill-wave"></i> Selling Price *</label>
                                        <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price"
                                               value="<?php echo $product['selling_price']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="quantity"><i class="fas fa-cubes"></i> Quantity *</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity"
                                               value="<?php echo $inventory; ?>" required>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Expense recorded if increased
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="is_active"><i class="fas fa-toggle-on"></i> Status *</label>
                                        <select class="form-control" id="is_active" name="is_active" required>
                                            <option value="1" <?php echo ($product['is_active'] == 1) ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo ($product['is_active'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Images -->
                    <div class="col-lg-5">
                        <div class="form-section" style="box-shadow: 0 8px 25px rgba(0,0,0,0.1), 0 4px 10px rgba(139, 95, 191, 0.1); border-radius: 15px; padding: 2rem; background: white; border: 1px solid rgba(139, 95, 191, 0.1);">
                            <h5 class="section-header"><i class="fas fa-images"></i> Product Images</h5>
                            <hr class="section-divider">

                            <!-- Upload New Images Section -->
                            <div class="upload-area" onclick="document.getElementById('product_images').click()">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <div class="upload-text">Click to Select Images</div>
                                <div class="upload-hint">
                                    <i class="fas fa-info-circle"></i> Select multiple images (JPG, PNG, GIF, WEBP - Max 5MB each)
                                </div>
                                <input class="d-none" type="file" id="product_images"
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple>
                                <div id="fileCount" class="mt-3 fw-bold text-primary"></div>
                            </div>

                            <!-- Image Preview Section -->
                            <div id="imagePreview" class="image-preview mt-3" style="display: none;">
                                <h6 class="preview-title"><i class="fas fa-eye"></i> Selected Images Preview</h6>
                                <div id="previewContainer" class="preview-container"></div>
                                <div class="preview-actions mt-3">
                                    <button type="button" id="uploadImagesBtn" class="btn btn-success btn-sm">
                                        <i class="fas fa-upload"></i> Upload Images
                                    </button>
                                    <button type="button" id="clearSelectionBtn" class="btn btn-outline-secondary btn-sm ms-2">
                                        <i class="fas fa-times"></i> Clear Selection
                                    </button>
                                </div>
                            </div>

                            <!-- Current Images -->
                            <h6 class="mt-4 mb-3"><i class="fas fa-list"></i> Current Images (<?php echo count($productImages); ?>)</h6>

                            <?php if (!empty($productImages)): ?>
                                <div class="image-gallery">
                                    <?php foreach ($productImages as $image): ?>
                                    <div class="image-item selectable-image" 
                                         style="position: relative; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease; margin-bottom: 1rem; cursor: pointer; border: 3px solid transparent;"
                                         data-image-id="<?php echo $image['id']; ?>"
                                         data-is-primary="<?php echo $image['is_primary'] ? '1' : '0'; ?>">
                                        <?php if ($image['is_primary']): ?>
                                            <div class="primary-badge" style="position: absolute; top: 10px; left: 10px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; z-index: 10; box-shadow: 0 2px 8px rgba(139, 95, 191, 0.3);">
                                                <i class="fas fa-star"></i> Primary
                                            </div>
                                        <?php endif; ?>
                                        <div class="selection-indicator" style="position: absolute; top: 10px; right: 10px; width: 24px; height: 24px; border-radius: 50%; background: rgba(255,255,255,0.9); border: 2px solid #8b5fbf; display: flex; align-items: center; justify-content: center; z-index: 10; opacity: 0.7;">
                                            <i class="fas fa-check" style="color: #8b5fbf; font-size: 0.8rem; display: none;"></i>
                                        </div>
                                        <img src="uploads/<?php echo htmlspecialchars($image['image_path']); ?>"
                                             alt="Product Image" loading="lazy" style="width: 100%; height: 200px; object-fit: cover; transition: all 0.3s ease;">
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Static Action Buttons -->
                                <div class="image-actions mt-4" style="display: flex; justify-content: center; gap: 15px;">
                                    <button type="button" id="setPrimaryBtn" class="btn btn-warning" disabled
                                       style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border: none; color: white; padding: 12px 24px; border-radius: 25px; font-weight: 600; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4); transition: all 0.3s ease; opacity: 0.5;">
                                        <i class="fas fa-star me-2"></i> Set as Primary
                                    </button>
                                    <button type="button" id="deleteImageBtn" class="btn btn-danger" disabled
                                       style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; color: white; padding: 12px 24px; border-radius: 25px; font-weight: 600; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4); transition: all 0.3s ease; opacity: 0.5;">
                                        <i class="fas fa-trash me-2"></i> Delete Selected
                                    </button>
                                </div>

                                <div class="selection-info mt-3 text-center" style="display: none;">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <span id="selectionText">Click on an image to select it</span>
                                    </small>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Images Yet</h5>
                                    <p class="text-muted">Upload images using the zone above</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="form-section">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="admin.php?page=products" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg" name="submit">
                                    <i class="fas fa-save"></i> Update Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmationModal" class="confirmation-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; border-radius: 15px; padding: 2rem; max-width: 400px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.3); position: relative;">
        <div class="modal-header" style="border-bottom: 1px solid #e9ecef; padding-bottom: 1rem; margin-bottom: 1rem;">
            <h5 class="modal-title" id="confirmationTitle" style="margin: 0; color: var(--purple-dark); font-weight: 600;">
                <i class="fas fa-question-circle me-2"></i>Confirm Action
            </h5>
        </div>
        <div class="modal-body">
            <p id="confirmationMessage" style="margin: 0; color: #6c757d; font-size: 1rem; line-height: 1.5;">
                Are you sure you want to perform this action?
            </p>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding-top: 1rem; margin-top: 1.5rem; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" id="cancelBtn" style="border-radius: 25px; padding: 0.5rem 1.5rem;">
                <i class="fas fa-times me-1"></i> Cancel
            </button>
            <button type="button" class="btn btn-primary" id="confirmBtn" style="border-radius: 25px; padding: 0.5rem 1.5rem; background: var(--purple-dark); border: none;">
                <i class="fas fa-check me-1"></i> Confirm
            </button>
        </div>
    </div>
</div>

<script>
// Enhanced file upload with drag and drop and preview
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('product_images');
    const uploadZone = document.querySelector('.upload-area');
    const fileCount = document.getElementById('fileCount');
    const imagePreview = document.getElementById('imagePreview');
    const previewContainer = document.getElementById('previewContainer');
    const uploadImagesBtn = document.getElementById('uploadImagesBtn');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');

    // File input change handler
    fileInput.addEventListener('change', function(e) {
        const files = e.target.files;
        updateFileCount(files.length);
        showImagePreview(files);

        // Add visual feedback
        if (files.length > 0) {
            uploadZone.style.borderColor = '#28a745';
            uploadZone.style.background = 'linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.1) 100%)';
        } else {
            uploadZone.style.borderColor = '#8b5fbf';
            uploadZone.style.background = 'linear-gradient(135deg, rgba(139, 95, 191, 0.05) 0%, rgba(255, 159, 191, 0.05) 100%)';
        }
    });

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        uploadZone.classList.add('drag-over');
    }

    function unhighlight(e) {
        uploadZone.classList.remove('drag-over');
    }

    uploadZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        fileInput.files = files;
        updateFileCount(files.length);
        showImagePreview(files);

        // Trigger change event
        const event = new Event('change');
        fileInput.dispatchEvent(event);
    }

    function updateFileCount(count) {
        if (count > 0) {
            fileCount.innerHTML = `<i class="fas fa-check-circle text-success"></i> ${count} file${count > 1 ? 's' : ''} selected`;
        } else {
            fileCount.innerHTML = '';
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
                    previewItem.className = 'preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}" class="preview-image">
                        <div class="preview-info">
                            <small class="text-muted">${file.name}</small>
                            <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-preview" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
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

    // Upload images button
    uploadImagesBtn.addEventListener('click', function() {
        if (fileInput.files.length === 0) {
            alert('Please select images first.');
            return;
        }

        // Create form data for image upload only
        const formData = new FormData();
        formData.append('csrf_token', window.csrfToken);
        formData.append('upload_images_only', '1');
        formData.append('product_id', <?php echo $product['id']; ?>);

        Array.from(fileInput.files).forEach(file => {
            formData.append('product_images[]', file);
        });

        // Show loading state
        const originalText = uploadImagesBtn.innerHTML;
        uploadImagesBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        uploadImagesBtn.disabled = true;

        // Upload images via AJAX
        fetch('admin.php?page=upload_product_images', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear selection and hide preview
                fileInput.value = '';
                imagePreview.style.display = 'none';
                updateFileCount(0);
                uploadZone.style.borderColor = '#8b5fbf';
                uploadZone.style.background = 'linear-gradient(135deg, rgba(139, 95, 191, 0.05) 0%, rgba(255, 159, 191, 0.05) 100%)';

                // Reload the page to show new images
                location.reload();
            } else {
                alert('Upload failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            alert('Upload failed. Please try again.');
        })
        .finally(() => {
            // Reset button
            uploadImagesBtn.innerHTML = originalText;
            uploadImagesBtn.disabled = false;
        });
    });

    // Clear selection button
    clearSelectionBtn.addEventListener('click', function() {
        fileInput.value = '';
        imagePreview.style.display = 'none';
        updateFileCount(0);
        uploadZone.style.borderColor = '#8b5fbf';
        uploadZone.style.background = 'linear-gradient(135deg, rgba(139, 95, 191, 0.05) 0%, rgba(255, 159, 191, 0.05) 100%)';
    });

    // Form validation (only for product details)
    const form = document.getElementById('productEditForm');
    let formChanged = false;

    form.addEventListener('input', function() {
        formChanged = true;
    });

    form.addEventListener('change', function(e) {
        // Don't mark as changed for file inputs (handled separately)
        if (e.target.type !== 'file') {
            formChanged = true;
        }
    });

    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Form submission with loading state
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Add loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;

        // Reset form changed flag
        formChanged = false;

        // Re-enable button after a delay
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 3000);
    });

    // Custom Confirmation Modal Logic
    const confirmationModal = document.getElementById('confirmationModal');
    const confirmationTitle = document.getElementById('confirmationTitle');
    const confirmationMessage = document.getElementById('confirmationMessage');
    const cancelBtn = document.getElementById('cancelBtn');
    const confirmBtn = document.getElementById('confirmBtn');

    // Modal state variables
    let currentAction = null;
    let currentImageId = null;
    let currentProductId = null;
    let currentImageIds = null;

    // Image Selection System
    let selectedImages = [];
    const setPrimaryBtn = document.getElementById('setPrimaryBtn');
    const deleteImageBtn = document.getElementById('deleteImageBtn');
    const selectionInfo = document.querySelector('.selection-info');
    const selectionText = document.getElementById('selectionText');

    // Handle image selection
    document.querySelectorAll('.selectable-image').forEach(image => {
        image.addEventListener('click', function() {
            const imageId = this.dataset.imageId;

            if (selectedImages.includes(imageId)) {
                // Deselect
                selectedImages = selectedImages.filter(id => id !== imageId);
                this.style.borderColor = 'transparent';
                this.querySelector('.selection-indicator .fa-check').style.display = 'none';
                this.querySelector('.selection-indicator').style.opacity = '0.7';
            } else {
                // Select
                selectedImages.push(imageId);
                this.style.borderColor = '#8b5fbf';
                this.querySelector('.selection-indicator .fa-check').style.display = 'block';
                this.querySelector('.selection-indicator').style.opacity = '1';
            }

            updateButtonStates();
            updateSelectionInfo();
        });
    });

    function updateButtonStates() {
        const hasSelection = selectedImages.length > 0;
        const hasNonPrimarySelection = selectedImages.some(id => {
            const img = document.querySelector(`[data-image-id="${id}"]`);
            return img && img.dataset.isPrimary !== '1';
        });

        // Set Primary button: enabled if exactly one image is selected AND it's not already primary
        const selectedImageId = selectedImages.length === 1 ? selectedImages[0] : null;
        const selectedImageElement = selectedImageId ? document.querySelector(`[data-image-id="${selectedImageId}"]`) : null;
        const isSelectedImagePrimary = selectedImageElement ? selectedImageElement.dataset.isPrimary === '1' : false;
        const canSetPrimary = selectedImages.length === 1 && !isSelectedImagePrimary;

        setPrimaryBtn.disabled = !canSetPrimary;
        setPrimaryBtn.style.opacity = canSetPrimary ? '1' : '0.5';

        // Delete button: enabled if any images are selected (but not primary ones)
        deleteImageBtn.disabled = !hasNonPrimarySelection;
        deleteImageBtn.style.opacity = hasNonPrimarySelection ? '1' : '0.5';
    }

    function updateSelectionInfo() {
        if (selectedImages.length === 0) {
            selectionInfo.style.display = 'none';
        } else {
            selectionInfo.style.display = 'block';
            selectionText.textContent = `${selectedImages.length} image${selectedImages.length > 1 ? 's' : ''} selected`;
        }
    }

    // Set Primary button handler
    setPrimaryBtn.addEventListener('click', function() {
        if (selectedImages.length !== 1) return;

        currentAction = 'set_primary';
        currentImageId = selectedImages[0];
        currentProductId = <?php echo $product['id']; ?>;

        confirmationTitle.innerHTML = '<i class="fas fa-star text-warning me-2"></i>Set Primary Image';
        confirmationMessage.textContent = 'Are you sure you want to set this image as the primary image? This will replace the current primary image.';
        confirmationModal.style.display = 'flex';

        // Focus on cancel button for accessibility
        setTimeout(() => cancelBtn.focus(), 100);
    });

    // Delete button handler
    deleteImageBtn.addEventListener('click', function() {
        if (selectedImages.length === 0) return;

        currentAction = 'delete_images';
        currentImageIds = [...selectedImages];
        currentProductId = <?php echo $product['id']; ?>;

        const count = selectedImages.length;
        const message = `Are you sure you want to delete ${count} selected image${count > 1 ? 's' : ''}? This action cannot be undone.`;

        confirmationTitle.innerHTML = '<i class="fas fa-trash text-danger me-2"></i>Delete Images';
        confirmationMessage.textContent = message;
        confirmationModal.style.display = 'flex';

        // Focus on cancel button for accessibility
        setTimeout(() => cancelBtn.focus(), 100);
    });

    // Confirm Button Handler
    confirmBtn.addEventListener('click', function() {
        if (!currentAction) return;

        if (currentAction === 'set_primary' && currentImageId && currentProductId) {
            fetch('admin.php?page=set_primary_image', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `image_id=${currentImageId}&product_id=${currentProductId}&csrf_token=${window.csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                confirmationModal.style.display = 'none';
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                confirmationModal.style.display = 'none';
                alert('An error occurred while setting the primary image.');
            });
        } else if (currentAction === 'delete_images' && currentImageIds && currentProductId) {
            fetch('admin.php?page=delete_images', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `image_ids=${JSON.stringify(currentImageIds)}&product_id=${currentProductId}&csrf_token=${window.csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                confirmationModal.style.display = 'none';
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                confirmationModal.style.display = 'none';
                alert('An error occurred while deleting the images.');
            });
        }

        // Reset modal state
        currentAction = null;
        currentImageId = null;
        currentProductId = null;
        currentImageIds = null;
    });

    // Cancel Button Handler
    cancelBtn.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
        currentAction = null;
        currentImageId = null;
        currentProductId = null;
        currentImageIds = null;
    });

    // Close modal on background click
    confirmationModal.addEventListener('click', function(e) {
        if (e.target === confirmationModal) {
            confirmationModal.style.display = 'none';
            currentAction = null;
            currentImageId = null;
            currentProductId = null;
            currentImageIds = null;
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && confirmationModal.style.display === 'flex') {
            confirmationModal.style.display = 'none';
            currentAction = null;
            currentImageId = null;
            currentProductId = null;
            currentImageIds = null;
        }
    });

    // Initialize button states
    updateButtonStates();
});
</script>

<style>
/* Image Gallery Hover Effects */
.image-item:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15), 0 4px 15px rgba(139, 95, 191, 0.2);
}

.image-item:hover .image-actions-overlay {
    background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.8) 70%, rgba(0,0,0,0.5) 90%, transparent 100%);
}

.image-item img:hover {
    filter: brightness(0.8);
}

/* Selection-based Image Management */
.selectable-image {
    transition: all 0.3s ease;
    cursor: pointer;
}

.selectable-image.selected {
    border-color: #8b5fbf !important;
    box-shadow: 0 0 0 3px rgba(139, 95, 191, 0.3), 0 8px 25px rgba(0,0,0,0.15) !important;
}

.selection-indicator {
    transition: all 0.3s ease;
}

.selection-indicator:hover {
    opacity: 1 !important;
}

/* Static Action Buttons */
.image-actions .btn {
    transition: all 0.3s ease;
    min-width: 140px;
}

.image-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3) !important;
}

.image-actions .btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
}

.image-actions .btn:disabled {
    cursor: not-allowed;
    transform: none !important;
}

/* Selection Info */
.selection-info {
    background: linear-gradient(135deg, rgba(139, 95, 191, 0.1) 0%, rgba(255, 159, 191, 0.1) 100%);
    border: 1px solid rgba(139, 95, 191, 0.2);
    border-radius: 20px;
    padding: 8px 16px;
    margin-top: 1rem;
    animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Action Button Hover Effects */
.action-btn:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.4) !important;
    border-color: rgba(255,255,255,0.6) !important;
}

.action-btn:active {
    transform: translateY(0) scale(0.98);
}

/* Custom Confirmation Modal */
.confirmation-modal {
    animation: fadeIn 0.3s ease;
}

.confirmation-modal .modal-content {
    animation: slideIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(-20px) scale(0.95); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

.confirmation-modal .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Ensure both form sections align at the top */
.form-section {
    margin-top: 0 !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>
