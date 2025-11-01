<?php
$pageTitle = 'Sales Report - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-chart-line me-2"></i><?php echo $reportTitle; ?></h2>
        <div>
            <?php if ($reportType === 'daily' && isset($date)): ?>
                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 12px; font-size: 0.85rem;">
                    <i class="fas fa-calendar-day me-1"></i> <?php echo date('l, F d, Y', strtotime($date)); ?>
                </span>
            <?php elseif ($reportType === 'weekly'): ?>
                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 12px; font-size: 0.85rem;">
                    <i class="fas fa-calendar-week me-1"></i> Week of <?php echo date('F d, Y', strtotime('monday this week')); ?>
                </span>
            <?php elseif ($reportType === 'monthly'): ?>
                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 12px; font-size: 0.85rem;">
                    <i class="fas fa-calendar-alt me-1"></i> <?php echo date('F Y'); ?>
                </span>
            <?php elseif ($reportType === 'yearly'): ?>
                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 12px; font-size: 0.85rem;">
                    <i class="fas fa-calendar me-1"></i> Year <?php echo date('Y'); ?>
                </span>
            <?php elseif ($reportType === 'custom' && $customStart && $customEnd): ?>
                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 12px; font-size: 0.85rem;">
                    <i class="fas fa-calendar-check me-1"></i> <?php echo date('M d, Y', strtotime($customStart)) . ' - ' . date('M d, Y', strtotime($customEnd)); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Report Type Selection -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-filter me-2"></i>Report Filters</h5>
                
                <!-- Quick Report Buttons -->
                <div class="btn-group mb-3 d-flex flex-wrap" role="group" style="gap: 10px;">
                    <a href="admin.php?page=sales_report&type=daily" 
                       class="btn btn-outline-primary <?php echo ($reportType == 'daily') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-day me-1"></i> Today
                    </a>
                    <a href="admin.php?page=sales_report&type=weekly" 
                       class="btn btn-outline-primary <?php echo ($reportType == 'weekly') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-week me-1"></i> This Week
                    </a>
                    <a href="admin.php?page=sales_report&type=monthly" 
                       class="btn btn-outline-primary <?php echo ($reportType == 'monthly') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt me-1"></i> This Month
                    </a>
                    <a href="admin.php?page=sales_report&type=yearly" 
                       class="btn btn-outline-primary <?php echo ($reportType == 'yearly') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar me-1"></i> This Year
                    </a>
                </div>
                
                <!-- Custom Date Range Form -->
                <form method="GET" action="admin.php" class="row g-3">
                    <input type="hidden" name="page" value="sales_report">
                    <input type="hidden" name="type" value="custom">
                    
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo $customStart; ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo $customEnd; ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sales Summary Cards -->
<?php if ($salesData): ?>
<div class="row mb-4 g-3">
    <div class="col-md-2">
        <div class="card dashboard-card dashboard-card-green">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Total Revenue</h6>
                        <h2 class="dashboard-card-number" style="font-size: 1.5rem;">₱<?php echo number_format($salesData['total_sales'] ?? 0, 2); ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-money-bill-wave fa-2x dashboard-card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card dashboard-card dashboard-card-peach">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Total Expenses</h6>
                        <h2 class="dashboard-card-number" style="font-size: 1.5rem; color: #dc3545;">₱<?php echo number_format($totalExpenses ?? 0, 2); ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-receipt fa-2x dashboard-card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card dashboard-card" style="background: linear-gradient(135deg, <?php echo ($netProfit >= 0) ? '#b6ffe5 0%, #8cffcc 100%' : '#ffb6b6 0%, #ff8c8c 100%'; ?>);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Net Profit</h6>
                        <h2 class="dashboard-card-number" style="font-size: 1.5rem; color: <?php echo ($netProfit >= 0) ? '#28a745' : '#dc3545'; ?>;">₱<?php echo number_format($netProfit ?? 0, 2); ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-chart-line fa-2x dashboard-card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card dashboard-card dashboard-card-blue">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Orders</h6>
                        <h2 class="dashboard-card-number"><?php echo number_format($salesData['order_count'] ?? 0); ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-shopping-cart fa-2x dashboard-card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card dashboard-card dashboard-card-purple">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Avg Order</h6>
                        <h2 class="dashboard-card-number" style="font-size: 1.3rem;">₱<?php echo number_format($salesData['avg_order_value'] ?? 0, 2); ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-chart-bar fa-2x dashboard-card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="card dashboard-card" style="background: linear-gradient(135deg, #e6e6ff 0%, #d6b6ff 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="dashboard-card-subtitle">Profit Margin</h6>
                        <h2 class="dashboard-card-number" style="font-size: 1.5rem;">
                            <?php 
                            $profitMargin = ($totalRevenue > 0) ? (($netProfit / $totalRevenue) * 100) : 0;
                            echo number_format($profitMargin, 1); 
                            ?>%
                        </h2>
                    </div>
                    <div>
                        <i class="fas fa-percentage fa-2x dashboard-card-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expenses Breakdown -->
<?php if (!empty($expensesByCategory)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-wallet me-2"></i>Expenses by Category</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Category</th>
                                <th>Number of Expenses</th>
                                <th>Total Amount</th>
                                <th>% of Total Expenses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expensesByCategory as $expense): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($expense['category']); ?></strong></td>
                                <td><?php echo number_format($expense['count']); ?></td>
                                <td style="color: #dc3545; font-weight: bold;">₱<?php echo number_format($expense['total'], 2); ?></td>
                                <td>
                                    <?php 
                                    $percentage = ($totalExpenses > 0) ? (($expense['total'] / $totalExpenses) * 100) : 0;
                                    ?>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $percentage; ?>%; background: var(--gradient-primary);"
                                             aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo number_format($percentage, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-secondary">
                                <td colspan="2"><strong>TOTAL EXPENSES</strong></td>
                                <td colspan="2"><strong style="color: #dc3545; font-size: 1.1rem;">₱<?php echo number_format($totalExpenses, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="admin.php?page=expenses" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i> View All Expenses
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- Top Selling Products -->
<?php if (!empty($topProducts)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-trophy me-2"></i>Top Selling Products</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Total Revenue</th>
                                <th>Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $index => $product): ?>
                            <tr>
                                <td><?php echo ($index + 1); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($product['product_image'])): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($product['product_image']); ?>" 
                                                 alt="Product" 
                                                 style="width: 50px; height: 50px; object-fit: contain; margin-right: 10px; border-radius: 8px;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center rounded position-relative" style="width: 50px; height: 50px; margin-right: 10px; background: linear-gradient(135deg, rgba(139, 95, 191, 0.15) 0%, rgba(255, 159, 191, 0.2) 100%); overflow: hidden;">
                                                <div class="position-absolute" style="top: -30%; right: -20%; width: 30px; height: 30px; background: rgba(139, 95, 191, 0.2); border-radius: 50%; filter: blur(10px);"></div>
                                                <i class="fas fa-box-open" style="font-size: 1.5rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($product['product_name']); ?></span>
                                    </div>
                                </td>
                                <td><strong><?php echo number_format($product['total_quantity']); ?></strong></td>
                                <td><strong style="color: var(--purple-dark);">₱<?php echo number_format($product['total_revenue'], 2); ?></strong></td>
                                <td><?php echo number_format($product['order_count']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Orders List -->
<?php if (!empty($orders)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-list me-2"></i>Completed Orders</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                <td><?php echo $order['item_count']; ?></td>
                                <td><strong style="color: var(--purple-dark);">₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td>
                                    <a href="admin.php?page=order_detail&id=<?php echo $order['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>No completed orders found for this period.
        </div>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle me-2"></i>Please select a report type or date range to view sales data.
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>
