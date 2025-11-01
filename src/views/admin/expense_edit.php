<?php
$pageTitle = 'Edit Expense - Admin';
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
        <h2><i class="fas fa-edit me-2"></i>Edit Expense</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="admin.php?page=edit_expense&id=<?php echo $expense['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" 
                                   value="<?php echo htmlspecialchars($expense['expense_date']); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   step="0.01" min="0.01" 
                                   value="<?php echo htmlspecialchars($expense['amount']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <input list="categories" class="form-control" id="category" name="category" 
                               value="<?php echo htmlspecialchars($expense['category']); ?>" required>
                        <datalist id="categories">
                            <?php foreach (array_merge($commonCategories, $categories ?? []) as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3" required><?php echo htmlspecialchars($expense['description']); ?></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="vendor_name" class="form-label">Vendor/Supplier Name</label>
                            <input type="text" class="form-control" id="vendor_name" name="vendor_name" 
                                   value="<?php echo htmlspecialchars($expense['vendor_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="cash" <?php echo ($expense['payment_method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                                <option value="credit_card" <?php echo ($expense['payment_method'] == 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                                <option value="debit_card" <?php echo ($expense['payment_method'] == 'debit_card') ? 'selected' : ''; ?>>Debit Card</option>
                                <option value="bank_transfer" <?php echo ($expense['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                <option value="check" <?php echo ($expense['payment_method'] == 'check') ? 'selected' : ''; ?>>Check</option>
                                <option value="online_payment" <?php echo ($expense['payment_method'] == 'online_payment') ? 'selected' : ''; ?>>Online Payment</option>
                                <option value="other" <?php echo ($expense['payment_method'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="receipt_number" class="form-label">Receipt/Invoice Number</label>
                        <input type="text" class="form-control" id="receipt_number" name="receipt_number" 
                               value="<?php echo htmlspecialchars($expense['receipt_number'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" 
                                  rows="2"><?php echo htmlspecialchars($expense['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="admin.php?page=expenses" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Expense Details</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($expense['created_at'])); ?></li>
                    <?php if (!empty($expense['created_by_email'])): ?>
                    <li class="mb-2"><strong>Created By:</strong> <?php echo htmlspecialchars($expense['created_by_email']); ?></li>
                    <?php endif; ?>
                    <?php if ($expense['updated_at'] != $expense['created_at']): ?>
                    <li class="mb-2"><strong>Last Updated:</strong> <?php echo date('M d, Y H:i', strtotime($expense['updated_at'])); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>
