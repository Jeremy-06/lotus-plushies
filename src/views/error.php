<?php
$pageTitle = 'Error - Online Shop';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6 text-center">
        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 80px;"></i>
        <h2 class="mt-4">Oops! Something went wrong</h2>
        <p class="text-muted"><?php echo $errorMessage ?? 'An unexpected error occurred.'; ?></p>
        <a href="index.php" class="btn btn-primary">Go to Homepage</a>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>