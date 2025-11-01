<?php
$pageTitle = 'Admin Dashboard - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-3">Admin Dashboard</h2>
        <div class="welcome-message">
            <i class="fas fa-hand-sparkles me-2" style="color: var(--purple-medium);"></i>
            <span class="welcome-text">Welcome back, <strong><?php echo Session::getEmail(); ?></strong></span>
        </div>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card dashboard-card dashboard-card-blue">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Total Products</h6>
                        <h2 class="dashboard-card-number"><?php echo $totalProducts; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-box fa-3x dashboard-card-icon"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="admin.php?page=products" class="btn btn-sm btn-primary">
                        View Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card dashboard-card dashboard-card-green">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Total Orders</h6>
                        <h2 class="dashboard-card-number"><?php echo $totalOrders; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-shopping-cart fa-3x dashboard-card-icon"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="admin.php?page=orders" class="btn btn-sm btn-primary">
                        View Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card dashboard-card dashboard-card-purple">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Total Customers</h6>
                        <h2 class="dashboard-card-number"><?php echo $totalCustomers; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-users fa-3x dashboard-card-icon"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="admin.php?page=customers" class="btn btn-sm btn-primary">
                        View Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card dashboard-card dashboard-card-peach">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Pending Orders</h6>
                        <h2 class="dashboard-card-number"><?php echo $pendingOrders; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-clock fa-3x dashboard-card-icon"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="admin.php?page=orders&status=pending" class="btn btn-sm btn-primary">
                        View Details <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-6">
        <div class="card dashboard-card dashboard-card-green">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Completed Orders (Sales)</h6>
                        <h2 class="dashboard-card-number"><?php echo $completedOrders; ?></h2>
                        <p class="text-muted mb-0 small">Orders marked as received by customers</p>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-3x dashboard-card-icon"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="admin.php?page=orders&status=completed" class="btn btn-sm btn-success">
                        View Completed <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card dashboard-card dashboard-card-purple">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Total Sales Revenue</h6>
                        <h2 class="dashboard-card-number">₱<?php echo number_format($totalSales, 2); ?></h2>
                        <p class="text-muted mb-0 small">From completed orders only</p>
                    </div>
                    <div>
                        <i class="fas fa-dollar-sign fa-3x dashboard-card-icon"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="admin.php?page=sales_report" class="btn btn-sm btn-success">
                        View Details <i class="fas fa-arrow-right ms-1"></i>
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