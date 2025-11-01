<?php
$pageTitle = 'Manage Expenses - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/CSRF.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-wallet me-2"></i>Manage Expenses</h2>
            <a href="admin.php?page=create_expense" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Expense
            </a>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" action="admin.php" class="d-flex align-items-center gap-3 flex-wrap">
            <input type="hidden" name="page" value="expenses">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? 'expense_date'); ?>">
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($_GET['order'] ?? 'DESC'); ?>">
            
            <!-- Search Input -->
            <div class="input-group" style="max-width: 350px;">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search expenses..." 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                       style="border: 2px solid #8b5fbf; border-radius: 25px 0 0 25px; padding: 10px 20px;">
                <button type="submit" class="btn" style="background: #8b5fbf; color: white; border: 2px solid #8b5fbf; border-radius: 0 25px 25px 0; padding: 10px 20px;">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            
            <!-- Category Filter -->
            <select class="form-select" name="category" onchange="this.form.submit()" style="max-width: 200px; border: 2px solid #8b5fbf; border-radius: 25px; padding: 10px 20px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                            <?php echo (isset($_GET['category']) && $_GET['category'] == $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!-- Refresh Button -->
            <a href="admin.php?page=expenses" 
               class="btn" style="background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple-medium) 100%); color: white; border-radius: 25px; padding: 10px 20px; text-decoration: none; border: none;">
                <i class="fas fa-sync-alt"></i> Refresh
            </a>
        </form>
    </div>
</div>

<?php if (!empty($expenses)): ?>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>
                    <a href="admin.php?page=expenses&sort=expense_date&order=<?php echo ($_GET['sort'] ?? '') === 'expense_date' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?>" class="text-white text-decoration-none">
                        Date <?php if (($_GET['sort'] ?? 'expense_date') === 'expense_date') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=expenses&sort=category&order=<?php echo ($_GET['sort'] ?? '') === 'category' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?>" class="text-white text-decoration-none">
                        Category <?php if (($_GET['sort'] ?? '') === 'category') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>Description</th>
                <th>
                    <a href="admin.php?page=expenses&sort=vendor_name&order=<?php echo ($_GET['sort'] ?? '') === 'vendor_name' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?>" class="text-white text-decoration-none">
                        Vendor <?php if (($_GET['sort'] ?? '') === 'vendor_name') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=expenses&sort=amount&order=<?php echo ($_GET['sort'] ?? '') === 'amount' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?>" class="text-white text-decoration-none">
                        Amount <?php if (($_GET['sort'] ?? '') === 'amount') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>
                    <a href="admin.php?page=expenses&sort=payment_method&order=<?php echo ($_GET['sort'] ?? '') === 'payment_method' && ($_GET['order'] ?? 'DESC') === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?>" class="text-white text-decoration-none">
                        Payment Method <?php if (($_GET['sort'] ?? '') === 'payment_method') echo ($_GET['order'] ?? 'DESC') === 'ASC' ? '▲' : '▼'; ?>
                    </a>
                </th>
                <th>Receipt #</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalAmount = 0;
            foreach ($expenses as $expense): 
                $totalAmount += $expense['amount'];
            ?>
            <tr>
                <td><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                <td><span class="badge bg-primary"><?php echo htmlspecialchars($expense['category']); ?></span></td>
                <td><?php echo htmlspecialchars($expense['description']); ?></td>
                <td><?php echo htmlspecialchars($expense['vendor_name'] ?? '-'); ?></td>
                <td><strong style="color: #dc3545;">₱<?php echo number_format($expense['amount'], 2); ?></strong></td>
                <td><?php echo ucwords(str_replace('_', ' ', $expense['payment_method'])); ?></td>
                <td><?php echo htmlspecialchars($expense['receipt_number'] ?? '-'); ?></td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="admin.php?page=edit_expense&id=<?php echo $expense['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: linear-gradient(135deg, #b19cd9 0%, #d6b6ff 100%); color: white; border: none; border-radius: 20px 0 0 20px; padding: 8px 16px;"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button"
                                class="btn btn-sm" 
                                style="background: #dc3545; color: white; border: none; border-radius: 0 20px 20px 0; padding: 8px 16px;"
                                title="Delete"
                                onclick="showDeleteModal(<?php echo $expense['id']; ?>, '<?php echo htmlspecialchars(addslashes($expense['description'])); ?>', <?php echo $expense['amount']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <tr class="table-secondary">
                <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                <td colspan="4"><strong style="color: #dc3545; font-size: 1.1rem;">₱<?php echo number_format($totalAmount, 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info text-center">
    <i class="fas fa-info-circle me-2"></i>No expenses found. <a href="admin.php?page=create_expense">Add your first expense</a>
</div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteExpenseModal" tabindex="-1" aria-labelledby="deleteExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white !important; border-radius: 20px 20px 0 0; border: none;">
                <h5 class="modal-title" id="deleteExpenseModalLabel" style="color: white !important;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div class="text-center mb-3">
                    <i class="fas fa-trash-alt" style="font-size: 3rem; color: #dc3545; opacity: 0.8;"></i>
                </div>
                <h6 class="text-center mb-3">Are you sure you want to delete this expense?</h6>
                <div class="alert alert-warning" style="border-radius: 15px; background: #fff3cd; border: 2px solid #ffc107;">
                    <div class="mb-2" style="word-wrap: break-word; white-space: normal; overflow: visible;">
                        <strong>Description:</strong> <span id="deleteExpenseDescription" style="display: inline; white-space: normal;"></span>
                    </div>
                    <div><strong>Amount:</strong> <span id="deleteExpenseAmount" style="color: #dc3545; font-weight: bold;"></span></div>
                </div>
                <p class="text-muted text-center mb-0" style="font-size: 0.9rem;">
                    <i class="fas fa-info-circle me-1"></i>This action cannot be undone and will affect your profit calculations.
                </p>
            </div>
            <div class="modal-footer" style="border: none; padding: 20px 30px; display: flex; justify-content: center; gap: 15px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 25px; padding: 10px 25px; flex: 1; max-width: 200px;">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger" style="border-radius: 25px; padding: 10px 25px; flex: 1; max-width: 200px; text-decoration: none;">
                    <i class="fas fa-trash me-1"></i>Delete Expense
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="successToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.2);">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i><span id="successMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    
    <div id="errorToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.2);">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle me-2"></i><span id="errorMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
function showDeleteModal(expenseId, description, amount) {
    // Set the content first
    document.getElementById('deleteExpenseDescription').textContent = description;
    document.getElementById('deleteExpenseAmount').textContent = '₱' + parseFloat(amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('confirmDeleteBtn').href = 'admin.php?page=delete_expense&id=' + expenseId;
    
    // Small delay to ensure content is rendered before showing modal
    setTimeout(function() {
        var modal = new bootstrap.Modal(document.getElementById('deleteExpenseModal'), {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();
    }, 50);
}

// Show toast notifications for success/error messages
document.addEventListener('DOMContentLoaded', function() {
    <?php if (Session::getFlash('success')): ?>
        document.getElementById('successMessage').textContent = '<?php echo addslashes(Session::getFlash('success')); ?>';
        var successToast = new bootstrap.Toast(document.getElementById('successToast'));
        successToast.show();
    <?php endif; ?>
    
    <?php if (Session::getFlash('message')): ?>
        document.getElementById('errorMessage').textContent = '<?php echo addslashes(Session::getFlash('message')); ?>';
        var errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
        errorToast.show();
    <?php endif; ?>
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>
