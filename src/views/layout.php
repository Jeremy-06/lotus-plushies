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
    <link href="assets/css/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <title><?php echo $pageTitle ?? 'Online Shop'; ?></title>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Online Shop</a>
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
                            <span class="badge bg-danger"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (Session::isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo Session::getEmail(); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (Session::isAdmin()): ?>
                                <li><a class="dropdown-item" href="admin.php">Admin Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="index.php?page=profile">Profile</a></li>
                                <li><a class="dropdown-item" href="index.php?page=order_history">Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="index.php?page=logout">Logout</a></li>
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

<div class="container mt-4">
    <?php
    // Display flash messages
    if (Session::getFlash('success')) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <strong>" . Session::getFlash('success') . "</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
    
    if (Session::getFlash('message')) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <strong>" . Session::getFlash('message') . "</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
    ?>
    
    <?php echo $content ?? ''; ?>
</div>

<footer class="mt-5 py-3 bg-light">
    <div class="container text-center">
        <p>&copy; <?php echo date('Y'); ?> Online Shop. All rights reserved.</p>
    </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>