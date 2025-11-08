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
                <th>
                    <a href="admin.php?page=categories&sort=id&order=<?php echo ($_GET['sort'] ?? '') === 'id' && ($_GET['order'] ?? 'ASC') === 'ASC' ? 'DESC' : 'ASC'; ?>" class="text-white text-decoration-none">
                        ID <?php if (($_GET['sort'] ?? '') === 'id') echo ($_GET['order'] ?? 'ASC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=categories&sort=category_name&order=<?php echo ($_GET['sort'] ?? '') === 'category_name' && ($_GET['order'] ?? 'ASC') === 'ASC' ? 'DESC' : 'ASC'; ?>" class="text-white text-decoration-none">
                        Category Name <?php if (($_GET['sort'] ?? 'category_name') === 'category_name') echo ($_GET['order'] ?? 'ASC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>Description</th>
                <th>Status</th>
                <th>
                    <a href="admin.php?page=categories&sort=created_at&order=<?php echo ($_GET['sort'] ?? '') === 'created_at' && ($_GET['order'] ?? 'ASC') === 'ASC' ? 'DESC' : 'ASC'; ?>" class="text-white text-decoration-none">
                        Created <?php if (($_GET['sort'] ?? '') === 'created_at') echo ($_GET['order'] ?? 'ASC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
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
                        <button type="button"
                           class="btn btn-sm delete-category-btn"
                           data-category-id="<?php echo $cat['id']; ?>"
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
<div class="alert alert-info">No categories found.</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete category button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-category-btn') || e.target.closest('.delete-category-btn')) {
            e.preventDefault();
            const button = e.target.classList.contains('delete-category-btn') ? e.target : e.target.closest('.delete-category-btn');
            const categoryId = button.getAttribute('data-category-id');
            
            showConfirmation(
                '<i class="fas fa-trash text-danger me-2"></i>Delete Category',
                'Delete this category? This cannot be undone if it has associated products.',
                `admin.php?page=delete_category&id=${categoryId}`
            );
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>

