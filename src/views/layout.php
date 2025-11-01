<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../models/Cart.php';

$cartCount = 0;
if (Session::isLoggedIn()) {
    $cartModel = new Cart();
    $cartCount = $cartModel->getItemCount(Session::getUserId());
}
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
    <title><?php echo $pageTitle ?? 'Lotus Plushies'; ?></title>
</head>
<body class="page-wrapper">
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <div class="brand-logo me-2" style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(139, 95, 191, 0.3);">
                <i class="fas fa-heart" style="color: white; font-size: 1.1rem;"></i>
            </div>
            <span style="font-size: 1.5rem;">Lotus Plushies</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=products">Products</a>
                </li>
                <?php if (Session::isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=cart">
                        <i class="fas fa-shopping-cart"></i> Cart 
                        <?php if ($cartCount > 0): ?>
                            <span class="badge" style="background: rgba(255, 255, 255, 0.3); font-size: 0.7rem; padding: 0.25rem 0.5rem; margin-left: 0.25rem;"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (Session::isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #8b5fbf 0%, #b19cd9 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(139, 95, 191, 0.3);">
                                <i class="fas fa-user" style="font-size: 0.9rem; color: white;"></i>
                            </div>
                            <span>
                                <?php 
                                    $firstName = Session::getFirstName();
                                    echo (!empty($firstName)) ? htmlspecialchars($firstName) : htmlspecialchars(Session::getEmail()); 
                                ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 250px;">
                            <li class="px-3 py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5fbf 0%, #b19cd9 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user" style="font-size: 1.2rem; color: white;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">
                                            <?php 
                                                $firstName = Session::getFirstName();
                                                echo (!empty($firstName)) ? htmlspecialchars($firstName) : 'User'; 
                                            ?>
                                        </div>
                                        <small class="text-muted"><?php echo htmlspecialchars(Session::getEmail()); ?></small>
                                    </div>
                                </div>
                            </li>
                            <?php if (Session::isAdmin()): ?>
                                <li class="mt-1"><a class="dropdown-item py-2" href="admin.php"><i class="fas fa-user-shield me-2" style="width: 20px;"></i> Admin Dashboard</a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                            <?php else: ?>
                                <li class="mt-1"><a class="dropdown-item py-2" href="index.php?page=profile"><i class="fas fa-user-circle me-2" style="width: 20px;"></i> Profile</a></li>
                                <li><a class="dropdown-item py-2" href="index.php?page=order_history"><i class="fas fa-box me-2" style="width: 20px;"></i> Orders</a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item py-2 text-danger" href="index.php?page=logout"><i class="fas fa-sign-out-alt me-2" style="width: 20px;"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=register">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="main-content">
    <div class="container mt-4 mb-5 px-5">
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

<footer class="site-footer py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                    <div class="brand-logo me-2" style="width: 35px; height: 35px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                        <i class="fas fa-heart" style="color: var(--purple-dark); font-size: 1rem;"></i>
                    </div>
                    <span class="fw-bold" style="font-size: 1.2rem;">Lotus Plushies</span>
                </div>
            </div>
            <div class="col-md-4 text-center mb-3 mb-md-0">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Lotus Plushies. All rights reserved.</p>
            </div>
            <div class="col-md-4 text-center text-md-end">
                <div class="social-links">
                    <a href="#" class="text-white me-3" style="font-size: 1.2rem; transition: transform 0.3s ease;"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white me-3" style="font-size: 1.2rem; transition: transform 0.3s ease;"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white" style="font-size: 1.2rem; transition: transform 0.3s ease;"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>