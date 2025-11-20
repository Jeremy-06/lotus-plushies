<?php
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
            <i class="fas fa-user-circle me-2"></i>My Profile
        </h2>
    </div>
</div>

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card shadow-sm" style="border: none; border-radius: 15px; overflow: hidden;">
            <div class="card-header text-center" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; padding: 2rem;">
                <div class="mb-3 position-relative" style="display: inline-block;">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                             alt="Profile Picture" 
                             class="rounded-circle"
                             style="width: 120px; height: 120px; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    <?php else: ?>
                        <i class="fas fa-user-circle" style="font-size: 120px; color: white;"></i>
                    <?php endif; ?>
                </div>
                <h4 class="mb-1" style="color: white;"><?php echo htmlspecialchars($user['first_name'] ?? 'Admin'); ?> <?php echo htmlspecialchars($user['last_name'] ?? ''); ?></h4>
                <p class="mb-0" style="color: rgba(255,255,255,0.9);"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3 pb-3 border-bottom">
                    <p class="text-muted small mb-1"><i class="fas fa-shield-alt me-2" style="color: var(--purple-dark);"></i>Account Type</p>
                    <p class="mb-0 fw-bold" style="color: var(--purple-dark);"><?php echo ucfirst($user['role']); ?></p>
                </div>
                <div class="mb-3 pb-3 border-bottom">
                    <p class="text-muted small mb-1"><i class="fas fa-calendar-check me-2" style="color: var(--purple-dark);"></i>Member Since</p>
                    <p class="mb-0 fw-bold" style="color: var(--purple-dark);"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                </div>
                <div>
                    <p class="text-muted small mb-1"><i class="fas fa-shopping-bag me-2" style="color: var(--purple-dark);"></i>Total Orders</p>
                    <p class="mb-0 fw-bold" style="color: var(--purple-dark);"><?php echo count($recentOrders); ?></p>
                </div>
            </div>
        </div>
        
        <?php if ($user['role'] === 'admin' && $adminCount <= 1): ?>
        <!-- Admin Deletion Protection Notice -->
        <div class="alert alert-info d-flex align-items-start mt-3" style="border-radius: 15px; border: none; background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 202, 240, 0.1) 100%); border-left: 4px solid #0d6efd;">
            <i class="fas fa-shield-alt me-3" style="font-size: 1.5rem; color: #0d6efd; margin-top: 2px;"></i>
            <div>
                <h6 class="mb-1" style="color: #0d6efd; font-weight: 600;">
                    <i class="fas fa-info-circle me-1"></i>Protected Administrator Account
                </h6>
                <p class="mb-0" style="font-size: 0.9rem; color: #495057;">
                    You are currently the only administrator in the system. Your account is protected and cannot be deleted to ensure continuous system management.
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Profile Information Form -->
    <div class="col-md-8">
        <div class="card shadow-sm mb-4" style="border: none; border-radius: 15px; overflow: hidden;">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; padding: 1rem 1.5rem;">
                <h5 class="mb-0 d-flex align-items-center" style="color: white;">
                    <i class="fas fa-edit me-2"></i>Personal Information
                </h5>
                <button type="button" id="editButton" class="btn btn-sm btn-light" style="border-radius: 15px;" onclick="toggleEditMode()">
                    <i class="fas fa-lock me-1"></i>Edit Profile
                </button>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="index.php?page=profile&action=update" enctype="multipart/form-data" id="profileForm">
                    <?php echo CSRF::getTokenField(); ?>
                    
                    <!-- Profile Picture Upload -->
                    <div class="mb-4 text-center">
                        <label class="form-label fw-bold d-block" style="color: var(--purple-dark);">
                            <i class="fas fa-camera me-1"></i>Profile Picture
                        </label>
                        <div class="mb-3">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                     alt="Current Profile" 
                                     class="rounded-circle mb-2"
                                     id="preview-image"
                                     style="width: 100px; height: 100px; object-fit: cover; border: 3px solid var(--purple-medium);">
                            <?php else: ?>
                                <div class="rounded-circle mb-2 d-inline-flex align-items-center justify-content-center" 
                                     id="preview-placeholder"
                                     style="width: 100px; height: 100px; background: var(--purple-medium); color: white; font-size: 40px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <img src="" alt="Preview" class="rounded-circle mb-2 d-none" 
                                     id="preview-image"
                                     style="width: 100px; height: 100px; object-fit: cover; border: 3px solid var(--purple-medium);">
                            <?php endif; ?>
                        </div>
                        <input type="file" name="profile_picture" id="profile_picture" class="form-control" 
                               accept="image/jpeg,image/jpg,image/png,image/gif"
                               style="border: 2px solid var(--purple-medium); border-radius: 10px;"
                               disabled>
                        <small class="text-muted">JPG, PNG, or GIF (Max 5MB)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                <i class="fas fa-user me-1"></i>First Name
                            </label>
                            <input type="text" name="first_name" class="form-control profile-input" 
                                   value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>"
                                   style="border: 2px solid var(--purple-medium); border-radius: 10px; background: #f5f5f5;"
                                   placeholder="Enter first name" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                <i class="fas fa-user me-1"></i>Last Name
                            </label>
                            <input type="text" name="last_name" class="form-control profile-input" 
                                   value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>"
                                   style="border: 2px solid var(--purple-medium); border-radius: 10px; background: #f5f5f5;"
                                   placeholder="Enter last name" disabled>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                <i class="fas fa-envelope me-1"></i>Email
                            </label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   style="border: 2px solid var(--purple-medium); border-radius: 10px; background: #f5f5f5;"
                                   disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                <i class="fas fa-phone me-1"></i>Phone
                            </label>
                            <input type="tel" name="phone" class="form-control profile-input" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                   style="border: 2px solid var(--purple-medium); border-radius: 10px; background: #f5f5f5;"
                                   placeholder="Enter phone number" disabled>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: var(--purple-dark);">
                            <i class="fas fa-map-marker-alt me-1"></i>Address
                        </label>
                        <textarea name="address" class="form-control profile-input" rows="2"
                                  style="border: 2px solid var(--purple-medium); border-radius: 10px; background: #f5f5f5;"
                                  placeholder="Enter street address" disabled><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                <i class="fas fa-city me-1"></i>City
                            </label>
                            <input type="text" name="city" class="form-control profile-input" 
                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>"
                                   style="border: 2px solid var(--purple-medium); border-radius: 10px; background: #f5f5f5;"
                                   placeholder="Enter city" disabled>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                <i class="fas fa-mail-bulk me-1"></i>Postal Code
                            </label>
                            <input type="text" name="postal_code" class="form-control profile-input" 
                                   value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>"
                                   style="border: 2px solid var(--purple-medium); border-radius: 10px; background: #f5f5f5;"
                                   placeholder="Enter postal code" disabled>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                <i class="fas fa-globe me-1"></i>Country
                            </label>
                            <input type="text" name="country" class="form-control profile-input" 
                                   value="<?php echo htmlspecialchars($user['country'] ?? 'Philippines'); ?>"
                                   style="border: 2px solid var(--purple-medium); border-radius: 10px; background: #f5f5f5;"
                                   placeholder="Enter country" disabled>
                        </div>
                    </div>
                    
                    <div class="text-end" id="saveButtonContainer" style="display: none;">
                        <button type="button" class="btn btn-lg me-2" style="background: #6c757d; color: white; border: none; border-radius: 25px; padding: 12px 40px;" onclick="cancelEdit()">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; border: none; border-radius: 25px; padding: 12px 40px;">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
                
                <!-- Change Password Section -->
                <hr class="my-4">
                
                <div class="accordion accordion-flush" id="passwordAccordion">
                    <div class="accordion-item" style="border: none;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#passwordCollapse" 
                                    style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; border-radius: 15px; padding: 1rem 1.5rem;">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </button>
                        </h2>
                        <div id="passwordCollapse" class="accordion-collapse collapse" data-bs-parent="#passwordAccordion">
                            <div class="accordion-body pt-4">
                                <form method="POST" action="index.php?page=profile&action=change_password">
                                    <?php echo CSRF::getTokenField(); ?>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                            <i class="fas fa-key me-1"></i>Current Password
                                        </label>
                                        <input type="password" name="current_password" class="form-control" 
                                               style="border: 2px solid var(--purple-medium); border-radius: 10px;"
                                               placeholder="Enter current password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                            <i class="fas fa-key me-1"></i>New Password
                                        </label>
                                        <input type="password" name="new_password" class="form-control" 
                                               style="border: 2px solid var(--purple-medium); border-radius: 10px;"
                                               placeholder="Enter new password (min. 6 characters)" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold" style="color: var(--purple-dark);">
                                            <i class="fas fa-key me-1"></i>Confirm New Password
                                        </label>
                                        <input type="password" name="confirm_password" class="form-control" 
                                               style="border: 2px solid var(--purple-medium); border-radius: 10px;"
                                               placeholder="Confirm new password" required>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; border-radius: 25px; padding: 12px 40px;">
                                            <i class="fas fa-shield-alt me-2"></i>Change Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Profile picture preview
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview-image');
            const placeholder = document.getElementById('preview-placeholder');
            
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            
            if (placeholder) {
                placeholder.classList.add('d-none');
            }
        };
        reader.readAsDataURL(file);
    }
});

// Toggle edit mode for profile form
let isEditMode = false;
const originalValues = {};

function toggleEditMode() {
    isEditMode = !isEditMode;
    const editButton = document.getElementById('editButton');
    const saveContainer = document.getElementById('saveButtonContainer');
    const inputs = document.querySelectorAll('.profile-input');
    const profilePicture = document.getElementById('profile_picture');
    
    if (isEditMode) {
        // Enable editing
        inputs.forEach(input => {
            originalValues[input.name] = input.value;
            input.disabled = false;
            input.style.background = 'white';
        });
        profilePicture.disabled = false;
        
        editButton.innerHTML = '<i class="fas fa-lock-open me-1"></i>Editing...';
        editButton.classList.remove('btn-light');
        editButton.classList.add('btn-warning');
        saveContainer.style.display = 'block';
    } else {
        // Disable editing (cancel)
        inputs.forEach(input => {
            input.disabled = true;
            input.style.background = '#f5f5f5';
        });
        profilePicture.disabled = true;
        
        editButton.innerHTML = '<i class="fas fa-lock me-1"></i>Edit Profile';
        editButton.classList.remove('btn-warning');
        editButton.classList.add('btn-light');
        saveContainer.style.display = 'none';
    }
}

function cancelEdit() {
    // Restore original values
    const inputs = document.querySelectorAll('.profile-input');
    inputs.forEach(input => {
        if (originalValues[input.name] !== undefined) {
            input.value = originalValues[input.name];
        }
    });
    
    // Reset file input
    document.getElementById('profile_picture').value = '';
    
    // Exit edit mode
    isEditMode = true; // Set to true so toggle will switch to false
    toggleEditMode();
}
</script>
