<?php
$pageTitle = 'Edit User - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<div class="row mb-4 mt-4">
    <div class="col-md-12">
        <div class="d-flex align-items-center">
            <a href="admin.php?page=users" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="mb-0" style="margin-top: 0;">
                    <i class="fas fa-user-edit me-2"></i>Edit User
                </h2>
                <p class="text-muted mb-0">Manage user information and permissions</p>
            </div>
        </div>
    </div>
</div>

<?php if ($user): ?>
<div class="row">
    <!-- User Profile Card -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm" style="border: none; border-radius: 15px;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border-radius: 15px 15px 0 0; padding: 1.5rem;">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-id-card me-2"></i>User Profile
                </h5>
            </div>
            <div class="card-body p-4">
                <!-- Profile Picture -->
                <div class="text-center mb-4">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                             alt="Profile" 
                             class="rounded-circle shadow-sm"
                             style="width: 120px; height: 120px; object-fit: cover; border: 4px solid var(--purple-medium);">
                    <?php else: ?>
                        <div class="rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center"
                             style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border: 4px solid var(--purple-medium);">
                            <i class="fas fa-user text-white" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- User Information -->
                <div class="mb-3">
                    <label class="text-muted small mb-1"><i class="fas fa-envelope me-2"></i>Email</label>
                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <hr>

                <?php if (!empty($user['first_name']) || !empty($user['last_name'])): ?>
                <div class="mb-3">
                    <label class="text-muted small mb-1"><i class="fas fa-user me-2"></i>Full Name</label>
                    <p class="mb-0 fw-bold">
                        <?php 
                        $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                        echo htmlspecialchars($fullName);
                        ?>
                    </p>
                </div>
                <?php endif; ?>

                <?php if (!empty($user['phone'])): ?>
                <div class="mb-3">
                    <label class="text-muted small mb-1"><i class="fas fa-phone me-2"></i>Phone</label>
                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($user['phone']); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($user['city']) || !empty($user['country'])): ?>
                <div class="mb-3">
                    <label class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-2"></i>Location</label>
                    <p class="mb-0 fw-bold">
                        <?php 
                        $location = [];
                        if (!empty($user['city'])) $location[] = $user['city'];
                        if (!empty($user['country'])) $location[] = $user['country'];
                        echo htmlspecialchars(implode(', ', $location));
                        ?>
                    </p>
                </div>
                <?php endif; ?>

                <hr>

                <div class="mb-3">
                    <label class="text-muted small mb-1"><i class="fas fa-calendar me-2"></i>Member Since</label>
                    <p class="mb-0 fw-bold"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                </div>

                <div>
                    <label class="text-muted small mb-1"><i class="fas fa-user-tag me-2"></i>Current Role</label>
                    <p class="mb-0">
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge bg-danger" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                <i class="fas fa-user-shield me-1"></i>Administrator
                            </span>
                        <?php else: ?>
                            <span class="badge bg-info" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                <i class="fas fa-user me-1"></i>Customer
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Role Card -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm" style="border: none; border-radius: 15px;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border-radius: 15px 15px 0 0; padding: 1.5rem;">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-user-cog me-2"></i>Role Management
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="admin.php?page=edit_user&id=<?php echo $user['id']; ?>">
                    <?php echo CSRF::getTokenField(); ?>

                    <div class="alert alert-info" style="border-radius: 10px; border-left: 4px solid #0dcaf0;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Changing a user's role will affect their permissions and access level.
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label fw-bold mb-3">
                            <i class="fas fa-shield-alt me-2"></i>Select User Role
                        </label>
                        
                        <div class="row g-3">
                            <!-- Customer Role Card -->
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="role" id="role_customer" value="customer" <?php echo ($user['role'] === 'customer') ? 'checked' : ''; ?>>
                                <label class="btn w-100 p-4 text-start h-100 role-card role-card-customer" for="role_customer" style="border-radius: 12px; border: 2px solid white; background: white;">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user text-white" style="font-size: 1.5rem;"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold text-dark">Customer</h6>
                                            <small class="text-muted d-block">
                                                Regular user with access to shop, cart, orders, and profile management.
                                            </small>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Admin Role Card -->
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="role" id="role_admin" value="admin" <?php echo ($user['role'] === 'admin') ? 'checked' : ''; ?>>
                                <label class="btn w-100 p-4 text-start h-100 role-card role-card-admin" for="role_admin" style="border-radius: 12px; border: 2px solid white; background: white;">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user-shield text-white" style="font-size: 1.5rem;"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold text-dark">Administrator</h6>
                                            <small class="text-muted d-block">
                                                Full system access including products, orders, categories, and user management.
                                            </small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="admin.php?page=users" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn text-white px-4 shadow-sm" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border: none; border-radius: 10px;">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Additional Information Card -->
        <?php if (!empty($user['address'])): ?>
        <div class="card shadow-sm mt-4" style="border: none; border-radius: 15px;">
            <div class="card-header" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border-radius: 15px 15px 0 0; padding: 1rem 1.5rem;">
                <h6 class="mb-0 text-white">
                    <i class="fas fa-address-card me-2"></i>Additional Information
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-0">
                    <label class="text-muted small mb-1"><i class="fas fa-map-marked-alt me-2"></i>Address</label>
                    <p class="mb-0"><?php echo htmlspecialchars($user['address']); ?></p>
                    <?php if (!empty($user['postal_code'])): ?>
                        <p class="mb-0 text-muted small">Postal Code: <?php echo htmlspecialchars($user['postal_code']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.role-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.role-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Customer card - selected state */
.btn-check:checked + label.role-card-customer {
    border-color: #0dcaf0 !important;
    border-width: 3px !important;
    box-shadow: 0 4px 15px rgba(13, 202, 240, 0.3) !important;
    background: linear-gradient(135deg, rgba(13, 202, 240, 0.05) 0%, rgba(10, 162, 192, 0.05) 100%) !important;
}

/* Admin card - selected state */
.btn-check:checked + label.role-card-admin {
    border-color: #dc3545 !important;
    border-width: 3px !important;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3) !important;
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, rgba(187, 45, 59, 0.05) 100%) !important;
}
</style>

<?php else: ?>
<div class="alert alert-warning" style="border-radius: 15px;">
    <i class="fas fa-exclamation-triangle me-2"></i>User not found.
</div>
<a href="admin.php?page=users" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-2"></i>Back to Users
</a>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>


