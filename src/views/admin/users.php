<?php
$pageTitle = 'User Management - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<div class="row mb-4 mt-4">
    <div class="col-md-12">
        <h2 class="mb-0" style="margin-top: 0;">
            <i class="fas fa-users me-2"></i>User Management
        </h2>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
            <!-- Search Form -->
            <form action="admin.php" method="GET" class="d-flex align-items-center gap-2">
                <input type="hidden" name="page" value="users">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? 'created_at'); ?>">
                <input type="hidden" name="order" value="<?php echo htmlspecialchars($_GET['order'] ?? 'DESC'); ?>">
                <?php if (isset($_GET['role'])): ?>
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($_GET['role']); ?>">
                <?php endif; ?>
                
                <div class="input-group" style="max-width: 400px;">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search users..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                           style="border: 2px solid #8b5fbf; border-radius: 25px 0 0 25px; padding: 10px 20px;">
                    <button type="submit" class="btn" style="background: #8b5fbf; color: white; border: 2px solid #8b5fbf; border-radius: 0 25px 25px 0; padding: 10px 20px;">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
                
                <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                    <a href="admin.php?page=users&sort=<?php echo htmlspecialchars($_GET['sort'] ?? 'created_at'); ?>&order=<?php echo htmlspecialchars($_GET['order'] ?? 'DESC'); ?><?php echo isset($_GET['role']) ? '&role=' . htmlspecialchars($_GET['role']) : ''; ?>" 
                       class="btn" style="background: #6c757d; color: white; border-radius: 25px; padding: 10px 20px; text-decoration: none;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
            
            <!-- Role Filter Buttons -->
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <a href="admin.php?page=users&sort=<?php echo htmlspecialchars($_GET['sort'] ?? 'created_at'); ?>&order=<?php echo htmlspecialchars($_GET['order'] ?? 'DESC'); ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>" 
                   class="d-flex align-items-center" 
                   style="border-radius: 20px; padding: 10px 20px; text-decoration: none; <?php echo !isset($_GET['role']) ? 'background: white; color: #8b5fbf !important; border: 2px solid #8b5fbf;' : 'background: #8b5fbf; color: white !important; border: 2px solid #8b5fbf;'; ?>">
                    <i class="fas fa-users me-2" style="color: inherit;"></i>
                    <span style="color: inherit;">All Users</span>
                </a>
                <a href="admin.php?page=users&role=customer&sort=<?php echo htmlspecialchars($_GET['sort'] ?? 'created_at'); ?>&order=<?php echo htmlspecialchars($_GET['order'] ?? 'DESC'); ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>" 
                   class="d-flex align-items-center" 
                   style="border-radius: 20px; padding: 10px 20px; text-decoration: none; <?php echo (isset($_GET['role']) && $_GET['role'] === 'customer') ? 'background: white; color: #8b5fbf !important; border: 2px solid #8b5fbf;' : 'background: #8b5fbf; color: white !important; border: 2px solid #8b5fbf;'; ?>">
                    <i class="fas fa-user me-2" style="color: inherit;"></i>
                    <span style="color: inherit;">Customers</span>
                </a>
                <a href="admin.php?page=users&role=admin&sort=<?php echo htmlspecialchars($_GET['sort'] ?? 'created_at'); ?>&order=<?php echo htmlspecialchars($_GET['order'] ?? 'DESC'); ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>" 
                   class="d-flex align-items-center" 
                   style="border-radius: 20px; padding: 10px 20px; text-decoration: none; <?php echo (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'background: white; color: #8b5fbf !important; border: 2px solid #8b5fbf;' : 'background: #8b5fbf; color: white !important; border: 2px solid #8b5fbf;'; ?>">
                    <i class="fas fa-user-shield me-2" style="color: inherit;"></i>
                    <span style="color: inherit;">Admins</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($users)): ?>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>
                    <a href="admin.php?page=users&sort=id&order=<?php echo ($_GET['sort'] ?? '') === 'id' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . htmlspecialchars($_GET['role']) : ''; ?>" class="text-white text-decoration-none">
                        ID <?php if (($_GET['sort'] ?? '') === 'id') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=users&sort=email&order=<?php echo ($_GET['sort'] ?? '') === 'email' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . htmlspecialchars($_GET['role']) : ''; ?>" class="text-white text-decoration-none">
                        Email <?php if (($_GET['sort'] ?? '') === 'email') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>Full Name</th>
                <th>Phone</th>
                <th>City</th>
                <th>
                    <a href="admin.php?page=users&sort=role&order=<?php echo ($_GET['sort'] ?? '') === 'role' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . htmlspecialchars($_GET['role']) : ''; ?>" class="text-white text-decoration-none">
                        Role <?php if (($_GET['sort'] ?? '') === 'role') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=users&sort=created_at&order=<?php echo ($_GET['sort'] ?? 'created_at') === 'created_at' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . htmlspecialchars($_GET['role']) : ''; ?>" class="text-white text-decoration-none">
                        Registered <?php if (($_GET['sort'] ?? 'created_at') === 'created_at') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <?php 
                    $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                    echo $fullName ? htmlspecialchars($fullName) : '<span class="text-muted">-</span>';
                    ?>
                </td>
                <td><?php echo $u['phone'] ? htmlspecialchars($u['phone']) : '<span class="text-muted">-</span>'; ?></td>
                <td><?php echo $u['city'] ? htmlspecialchars($u['city']) : '<span class="text-muted">-</span>'; ?></td>
                <td>
                    <?php if ($u['role'] === 'admin'): ?>
                        <span class="badge bg-danger"><i class="fas fa-user-shield me-1"></i>Admin</span>
                    <?php else: ?>
                        <span class="badge bg-info"><i class="fas fa-user me-1"></i>Customer</span>
                    <?php endif; ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                <td>
                    <?php if ($u['id'] !== Session::getUserId()): ?>
                    <div class="btn-group" role="group">
                        <a href="admin.php?page=edit_user&id=<?php echo $u['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: linear-gradient(135deg, #b19cd9 0%, #d6b6ff 100%); color: white; border: none; border-radius: 20px 0 0 20px; padding: 8px 16px;"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="admin.php?page=delete_user&id=<?php echo $u['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: #dc3545; color: white; border: none; border-radius: 0 20px 20px 0; padding: 8px 16px;"
                           title="Delete"
                           onclick="return confirm('Delete this user? This cannot be undone.');">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete yourself" style="border-radius: 20px; padding: 8px 16px;">
                        <i class="fas fa-lock"></i>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>No users found.
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>


