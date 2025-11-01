<?php
$pageTitle = 'Create Category - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<div class="row mb-4">
    <div class="col-md-12 text-center">
        <h2>Create Category</h2>
        <p class="text-muted">Add a new product category</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="admin.php?page=create_category">
                    <?php echo CSRF::getTokenField(); ?>

                    <div class="mb-4">
                        <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="category_name" 
                               placeholder="e.g., Teddy Bears" 
                               value="<?php echo htmlspecialchars($_POST['category_name'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Brief description of this category..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input custom-checkbox" type="checkbox" id="is_active" name="is_active" value="1" 
                                   <?php echo (!isset($_POST['is_active']) || $_POST['is_active'] ?? '') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                <strong>Active</strong>
                                <small class="text-muted d-block">Category will be visible to customers</small>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="admin.php?page=categories" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Create Category
                        </button>
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

