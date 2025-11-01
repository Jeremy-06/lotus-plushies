<?php
$pageTitle = 'Manage Categories - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<div class="row mb-4 mt-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0" style="margin-top: 0;">Manage Categories</h2>
            <a href="admin.php?page=create_category" class="btn btn-success">
                <i class="fas fa-plus"></i> Add New Category
            </a>
        </div>
    </div>
</div>

<?php if (!empty($categories)): ?>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?php echo $cat['id']; ?></td>
                <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                <td><?php echo htmlspecialchars($cat['description'] ?? 'No description'); ?></td>
                <td>
                    <?php if ($cat['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </td>
                <td><?php echo date('Y-m-d H:i', strtotime($cat['created_at'])); ?></td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="admin.php?page=edit_category&id=<?php echo $cat['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: linear-gradient(135deg, #b19cd9 0%, #d6b6ff 100%); color: white; border: none; border-radius: 20px 0 0 20px; padding: 8px 16px;"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="admin.php?page=delete_category&id=<?php echo $cat['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: #dc3545; color: white; border: none; border-radius: 0 20px 20px 0; padding: 8px 16px;"
                           title="Delete"
                           onclick="return confirm('Delete this category? This cannot be undone if it has associated products.');">
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
<div class="alert alert-info">No categories found.</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>

