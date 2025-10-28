<?php
$pageTitle = 'Manage Customers - Admin';
ob_start();

require_once __DIR__ . '/../../helpers/Session.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Manage Customers</h2>
    </div>
</div>

<?php if (!empty($customers)): ?>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?php echo $customer['id']; ?></td>
                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                <td><span class="badge bg-info"><?php echo ucfirst($customer['role']); ?></span></td>
                <td><?php echo date('F d, Y', strtotime($customer['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info text-center">
    No customers found.
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../admin_layout.php';
?>