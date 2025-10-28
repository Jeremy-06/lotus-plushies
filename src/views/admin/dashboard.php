<?php
$pageTitle = 'Admin Dashboard - Online Shop';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Admin Dashboard</h2>
        <p class="text-muted">Welcome back, <?php echo Session::getEmail(); ?></p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Products</h6>
                        <h2 class="card-title mb-0"><?php echo $totalProducts; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-box fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-primary bg-opacity-75">
                <a href="admin.php?page=products" class="text-white text-decoration-none">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Orders</h6>
                        <h2 class="card-title mb-0"><?php echo $totalOrders; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-success bg-opacity-75">
                <a href="admin.php?page=orders" class="text-white text-decoration-none">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Customers</h6>
                        <h2 class="card-title mb-0"><?php echo $totalCustomers; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-info bg-opacity-75">
                <a href="admin.php?page=customers" class="text-white text-decoration-none">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Pending Orders</h6>
                        <h2 class="card-title mb-0"><?php echo $pendingOrders; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-warning bg-opacity-75">
                <a href="admin.php?page=orders&status=pending" class="text-white text-decoration-none">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="d-flex gap-2">
                    <a href="admin.php?page=create_product" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                    <a href="admin.php?page=orders" class="btn btn-success">
                        <i class="fas fa-list"></i> View Orders
                    </a>
                    <a href="admin.php?page=customers" class="btn btn-info">
                        <i class="fas fa-users"></i> View Customers
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Back to Store
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>