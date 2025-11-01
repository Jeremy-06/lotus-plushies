<?php
$pageTitle = 'Order Details - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/UIHelper.php';
?>

<style>
@media print {
    /* Hide navigation, buttons, and non-receipt elements */
    .no-print, nav, footer, .btn, .card-header {
        display: none !important;
    }
    
    /* Reset margins and padding for compact layout */
    body {
        margin: 0;
        padding: 10px;
        background: white !important;
        color: #000 !important;
        font-size: 11px !important;
    }
    
    /* Reset card styles for printing */
    .card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .card-body {
        padding: 5px !important;
    }
    
    /* Show receipt header */
    .receipt-header {
        display: block !important;
        text-align: center;
        margin-bottom: 15px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }
    
    .receipt-title {
        font-size: 22px;
        font-weight: bold;
        color: #000;
        margin: 0;
        padding: 0;
    }
    
    .receipt-subtitle {
        font-size: 11px;
        color: #333;
        margin: 2px 0;
    }
    
    /* Compact order info */
    .order-info-print {
        display: grid !important;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
        margin-bottom: 15px;
        padding: 10px;
        border: 1px solid #ddd;
        background: #f9f9f9;
    }
    
    .order-info-print > div {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .order-info-print h6,
    .order-info-print p {
        margin: 0 !important;
        padding: 0 !important;
        line-height: 1.3 !important;
    }
    
    /* Compact item cards */
    .order-item-card {
        border: 1px solid #ddd !important;
        margin-bottom: 5px !important;
        padding: 8px !important;
        page-break-inside: avoid;
    }
    
    .order-item-card img {
        max-height: 40px !important;
        width: auto !important;
    }
    
    .order-item-card .row {
        margin: 0 !important;
    }
    
    .order-item-card .col-md-2,
    .order-item-card .col-md-4 {
        padding: 2px !important;
    }
    
    /* Compact summary */
    .order-summary-print {
        margin-top: 15px;
        padding: 10px;
        border: 2px solid #000;
        background: #f9f9f9;
    }
    
    .order-summary-print .d-flex {
        margin-bottom: 5px !important;
        padding: 3px 0 !important;
        line-height: 1.2 !important;
    }
    
    /* Make text print-friendly */
    h2, h5, h6, p, span {
        color: #000 !important;
        margin: 0 !important;
    }
    
    h6 {
        font-size: 11px !important;
    }
    
    h5 {
        font-size: 12px !important;
    }
    
    .small, small {
        font-size: 9px !important;
    }
    
    /* Remove excessive spacing */
    .mb-1, .mb-2, .mb-3, .mb-4,
    .mt-1, .mt-2, .mt-3, .mt-4,
    .pb-3, .pt-3 {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Compact borders */
    .border-bottom {
        border-bottom: 1px dashed #999 !important;
        padding-bottom: 3px !important;
        margin-bottom: 3px !important;
    }
    
    .border-top {
        border-top: 2px solid #000 !important;
        padding-top: 5px !important;
        margin-top: 5px !important;
    }
    
    /* Hide row gaps */
    .g-3 {
        gap: 0 !important;
    }
    
    /* Fit everything on one page */
    @page {
        margin: 0.5cm;
        size: auto;
    }
}

/* Hide receipt header on screen */
.receipt-header {
    display: none;
}
</style>

<!-- Receipt Header (only visible when printing) -->
<div class="receipt-header">
    <div class="receipt-title">ORDER RECEIPT</div>
    <div class="receipt-subtitle">Lotus Plushies</div>
    <div class="receipt-subtitle">Order #<?php echo htmlspecialchars($order['order_number']); ?> | <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></div>
</div>

<div class="row mb-4 no-print">
    <div class="col-md-8">
        <h2 class="mb-0" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
            <i class="fas fa-receipt me-2"></i>Order Details
        </h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php?page=order_history" class="btn" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; border: none; border-radius: 20px;">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<!-- Order Info Card -->
<div class="card shadow-sm mb-4" style="border: none; border-radius: 15px; overflow: hidden;">
    <div class="card-header no-print" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; padding: 1.5rem;">
        <h5 class="mb-0" style="color: white;"><i class="fas fa-info-circle me-2"></i>Order Information</h5>
    </div>
    <div class="card-body p-4">
        <div class="row order-info-print">
            <div class="col-md-4 mb-3">
                <p class="text-muted small mb-1">Order Number</p>
                <h6 class="fw-bold" style="color: var(--purple-dark);"><?php echo htmlspecialchars($order['order_number']); ?></h6>
            </div>
            <div class="col-md-4 mb-3">
                <p class="text-muted small mb-1">Order Date</p>
                <h6 class="fw-bold" style="color: var(--purple-dark);">
                    <i class="fas fa-calendar-alt me-1"></i><?php echo UIHelper::formatDate($order['created_at']); ?>
                </h6>
                <p class="text-muted small mb-0"><?php echo UIHelper::formatDate($order['created_at'], 'h:i A'); ?></p>
            </div>
            <div class="col-md-4 mb-3">
                <p class="text-muted small mb-1">Status</p>
                <?php echo UIHelper::renderOrderStatusBadge($order['order_status']); ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Order Items Card -->
        <div class="card shadow-sm mb-4" style="border: none; border-radius: 15px; overflow: hidden;">
            <div class="card-header no-print" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; padding: 1.5rem;">
                <h5 class="mb-0" style="color: white;"><i class="fas fa-shopping-bag me-2"></i>Order Items</h5>
            </div>
            <div class="card-body p-3">
                <?php foreach ($orderItems as $index => $item): ?>
                <div class="card mb-3 shadow-sm order-item-card" style="border: 2px solid var(--purple-medium); border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <?php if ($item['img_path']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($item['img_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         class="img-fluid rounded" 
                                         style="max-height: 80px; object-fit: contain;">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center rounded position-relative" style="height: 80px; width: 80px; background: linear-gradient(135deg, rgba(139, 95, 191, 0.15) 0%, rgba(255, 159, 191, 0.2) 100%); overflow: hidden;">
                                        <div class="position-absolute" style="top: -30%; right: -20%; width: 50px; height: 50px; background: rgba(139, 95, 191, 0.2); border-radius: 50%; filter: blur(15px);"></div>
                                        <i class="fas fa-box-open" style="font-size: 2rem; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--pink-medium) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: float 3s ease-in-out infinite;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1 fw-bold" style="color: var(--purple-dark);">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </h6>
                                <p class="text-muted small mb-0">Product ID: <?php echo $item['product_id']; ?></p>
                            </div>
                            <div class="col-md-2 text-center">
                                <p class="text-muted small mb-1">Quantity</p>
                                <h6 class="mb-0 fw-bold" style="color: var(--purple-dark);">×<?php echo $item['quantity']; ?></h6>
                            </div>
                            <div class="col-md-2 text-center">
                                <p class="text-muted small mb-1">Unit Price</p>
                                <h6 class="mb-0" style="color: var(--purple-dark);"><?php echo UIHelper::formatCurrency($item['unit_price']); ?></h6>
                            </div>
                            <div class="col-md-2 text-end">
                                <p class="text-muted small mb-1">Subtotal</p>
                                <h5 class="mb-0 fw-bold" style="color: var(--purple-dark);"><?php echo UIHelper::formatCurrency($item['item_total']); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Order Summary Card -->
        <div class="card shadow-sm sticky-top" style="border: none; border-radius: 15px; overflow: hidden; top: 20px;">
            <div class="card-header no-print" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; padding: 1.5rem;">
                <h5 class="mb-0" style="color: white;"><i class="fas fa-calculator me-2"></i>Order Summary</h5>
            </div>
            <div class="card-body p-4 order-summary-print">
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-bold" style="color: var(--purple-dark);"><?php echo UIHelper::formatCurrency($order['subtotal']); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted">
                        <i class="fas fa-shipping-fast me-1"></i>Shipping
                    </span>
                    <span class="fw-bold" style="color: var(--purple-dark);"><?php echo UIHelper::formatCurrency($order['shipping_cost']); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-muted">
                        <i class="fas fa-receipt me-1"></i>Tax (12%)
                    </span>
                    <span class="fw-bold" style="color: var(--purple-dark);"><?php echo UIHelper::formatCurrency($order['tax_amount']); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="mb-0 fw-bold" style="color: var(--purple-dark);">Total</h5>
                    <h4 class="mb-0 fw-bold" style="color: var(--purple-dark);"><?php echo UIHelper::formatCurrency($order['total_amount']); ?></h4>
                </div>
                
                <?php if ($order['order_status'] === 'delivered'): ?>
                <div class="mt-4 pt-3 border-top">
                    <a href="index.php?page=order&action=confirm_receipt&id=<?php echo $order['id']; ?>" 
                       class="btn btn-success w-100"
                       style="border-radius: 20px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; padding: 12px;"
                       onclick="return confirm('Confirm that you have received this order?');">
                        <i class="fas fa-check-circle me-2"></i>Mark as Received
                    </a>
                    <p class="text-muted small text-center mt-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>Click to confirm order delivery
                    </p>
                </div>
                <?php elseif ($order['order_status'] === 'completed'): ?>
                <div class="mt-4 pt-3 border-top">
                    <button class="btn btn-primary w-100"
                       style="border-radius: 20px; background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); border: none; padding: 12px;"
                       onclick="window.print();">
                        <i class="fas fa-file-invoice me-2"></i>View Receipt
                    </button>
                    <p class="text-muted small text-center mt-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>Print or save your receipt
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>


