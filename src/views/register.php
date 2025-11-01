<?php
$pageTitle = 'Register - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Register</h3>
                
                <form action="index.php?page=register&action=process" method="POST">
                    <?php echo CSRF::getTokenField(); ?>
                    
                    <div class="form-outline mb-4">
                        <label class="form-label" for="email">Email address</label>
                        <input type="email" id="email" class="form-control" name="email" 
                               value="<?php echo Session::get('email', ''); Session::remove('email'); ?>" required />
                        <?php if (Session::getFlash('emailError')): ?>
                            <small class="text-danger"><?php echo Session::getFlash('emailError'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-outline mb-4">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" class="form-control" name="password" required />
                        <small class="form-text text-muted">Minimum 6 characters</small>
                        <?php if (Session::getFlash('passwordError')): ?>
                            <small class="text-danger d-block"><?php echo Session::getFlash('passwordError'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-outline mb-4">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" class="form-control" name="confirm_password" required />
                        <?php if (Session::getFlash('confirm_passwordError')): ?>
                            <small class="text-danger"><?php echo Session::getFlash('confirm_passwordError'); ?></small>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block mb-4 w-100" name="submit">Register</button>

                    <div class="text-center">
                        <p>Already a member? <a href="index.php?page=login">Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>