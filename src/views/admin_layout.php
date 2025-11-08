<?php
require_once __DIR__ . '/../helpers/Session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <style>
    /* Custom Confirmation Modal Styles */
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

    /* CSS Variables for consistency */
    :root {
        --purple-dark: #8b5fbf;
        --purple-medium: #a78bfa;
        --purple-light: #c4b5fd;
        --pink-medium: #f472b6;
        --pink-light: #fbb6ce;
        --text-primary: #374151;
        --text-secondary: #6b7280;
    }
    </style>

    <title><?php echo $pageTitle ?? 'Admin - Lotus Plushies'; ?></title>
</head>
<body class="admin-panel page-wrapper">
<nav class="navbar navbar-expand-lg navbar-dark admin-navbar" style="position: relative; z-index: 1050;">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin.php">
            <i class="fas fa-user-shield"></i> Admin Panel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="admin.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin.php?page=products">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin.php?page=orders">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin.php?page=expenses">
                        <i class="fas fa-wallet"></i> Expenses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin.php?page=sales_report">
                        <i class="fas fa-chart-line"></i> Sales Report
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin.php?page=users">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-store"></i> View Store
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #8b5fbf 0%, #b19cd9 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255, 255, 255, 0.3);">
                            <i class="fas fa-user-shield" style="font-size: 0.9rem;"></i>
                        </div>
                        <span>
                            <?php 
                            $firstName = Session::getFirstName();
                            echo !empty($firstName) ? htmlspecialchars($firstName) : htmlspecialchars(Session::getEmail()); 
                            ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 220px; z-index: 99999 !important; position: absolute !important;">
                        <li class="px-3 py-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-2" style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5fbf 0%, #b19cd9 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-shield" style="font-size: 1.2rem; color: white;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">
                                        <?php 
                                        $firstName = Session::getFirstName();
                                        echo !empty($firstName) ? htmlspecialchars($firstName) : 'Admin'; 
                                        ?>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars(Session::getEmail()); ?></small>
                                </div>
                            </div>
                        </li>
                        <li class="mt-1"><a class="dropdown-item py-2" href="index.php?page=profile"><i class="fas fa-user me-2"></i> My Profile</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="index.php?page=logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="main-content">
    <div class="container-fluid mt-4 mb-5 px-5">
        <?php
        // Display flash messages (read once to avoid clearing before echo)
        $flashSuccess = Session::getFlash('success');
        $flashError = Session::getFlash('message');
        if ($flashSuccess) {
            echo "<div class='alert alert-dismissible fade show d-flex align-items-center mb-4' role='alert' style='background: #d4edda; border: none; border-left: 4px solid #28a745; border-radius: 10px; padding: 1rem 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 100%;'>
                    <i class='fas fa-check-circle me-3' style='color: #28a745; font-size: 1.2rem;'></i>
                    <span style='color: #155724; font-weight: 500; flex: 1;'>" . htmlspecialchars($flashSuccess) . "</span>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
        }
        if ($flashError) {
            echo "<div class='alert alert-dismissible fade show d-flex align-items-center mb-4' role='alert' style='background: #f8d7da; border: none; border-left: 4px solid #dc3545; border-radius: 10px; padding: 1rem 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 100%;'>
                    <i class='fas fa-exclamation-circle me-3' style='color: #dc3545; font-size: 1.2rem;'></i>
                    <span style='color: #721c24; font-weight: 500; flex: 1;'>" . htmlspecialchars($flashError) . "</span>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
        }
        ?>
        
        <?php echo $content ?? ''; ?>
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

<footer class="site-footer admin-footer py-3 text-white">
    <div class="container text-center">
        <div class="mb-2">
            <div class="d-inline-flex align-items-center justify-content-center mb-2">
                <div class="brand-logo me-2 admin-footer-logo" style="width: 32px; height: 32px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);">
                    <i class="fas fa-heart" style="color: var(--purple-dark); font-size: 0.95rem;"></i>
                </div>
                <span class="fw-bold" style="font-size: 1rem;">Lotus Plushies Admin</span>
            </div>
        </div>
        <p class="mb-0" style="opacity: 0.9; font-size: 0.9rem;">&copy; <?php echo date('Y'); ?> Lotus Plushies Admin Panel. All rights reserved.</p>
    </div>
</footer>

<script src="assets/js/main.js"></script>

<!-- Custom Confirmation Modal JavaScript -->
<script>
// Global confirmation modal variables
let currentActionUrl = null;

// Show confirmation modal
function showConfirmation(title, message, actionUrl) {
    const modal = document.getElementById('confirmationModal');
    const titleEl = document.getElementById('confirmationTitle');
    const messageEl = document.getElementById('confirmationMessage');

    titleEl.innerHTML = title;
    messageEl.textContent = message;
    currentActionUrl = actionUrl;

    modal.style.display = 'flex';

    // Focus on cancel button for accessibility
    setTimeout(() => {
        document.getElementById('cancelBtn').focus();
    }, 100);
}

// Modal event handlers
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('confirmationModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const confirmBtn = document.getElementById('confirmBtn');

    // Cancel button
    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        currentActionUrl = null;
    });

    // Confirm button
    confirmBtn.addEventListener('click', function() {
        if (currentActionUrl) {
            window.location.href = currentActionUrl;
        }
    });

    // Close modal on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            currentActionUrl = null;
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            modal.style.display = 'none';
            currentActionUrl = null;
        }
    });
});
</script>

</body>
</html>