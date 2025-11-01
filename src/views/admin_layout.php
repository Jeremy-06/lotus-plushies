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
    <title><?php echo $pageTitle ?? 'Admin - Lotus Plushies'; ?></title>
</head>
<body class="admin-panel page-wrapper">
<nav class="navbar navbar-expand-lg navbar-dark admin-navbar">
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
                    <a class="nav-link" href="admin.php?page=categories">
                        <i class="fas fa-tags"></i> Categories
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
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 220px;">
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
            echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong>" . htmlspecialchars($flashSuccess) . "</strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
        }
        if ($flashError) {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <strong>" . htmlspecialchars($flashError) . "</strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
        }
        ?>
        
        <?php echo $content ?? ''; ?>
    </div>
</div>

<footer class="site-footer admin-footer py-3 text-white">
    <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> Lotus Plushies Admin Panel. All rights reserved.</p>
    </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>