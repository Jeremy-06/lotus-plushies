<?php
$pageTitle = 'Add Expense - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';

// Common expense categories
$commonCategories = [
    'Inventory',
    'Shipping',
    'Marketing',
    'Utilities',
    'Packaging',
    'Maintenance',
    'Rent',
    'Salaries',
    'Equipment',
    'Software',
    'Other'
];
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-plus-circle me-2"></i>Add New Expense</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="admin.php?page=create_expense">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <input list="categories" class="form-control" id="category" name="category" 
                               placeholder="Select or type category" required>
                        <datalist id="categories">
                            <?php foreach (array_merge($commonCategories, $categories ?? []) as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3" placeholder="Describe the expense..." required></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="vendor_name" class="form-label">Vendor/Supplier Name</label>
                            <input type="text" class="form-control" id="vendor_name" name="vendor_name" 
                                   placeholder="Optional">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="online_payment">Online Payment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="receipt_number" class="form-label">Receipt/Invoice Number</label>
                        <input type="text" class="form-control" id="receipt_number" name="receipt_number" 
                               placeholder="Optional">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" 
                                  rows="2" placeholder="Any additional information..."></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="admin.php?page=expenses" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Quick Tips</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Record expenses as soon as possible</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Keep receipts and invoices</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Use consistent categories</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Include vendor details</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Add notes for clarity</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>
