<?php
$pageTitle = 'Login - Lotus Plushies';
ob_start();

require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';

// Debug: Show current URL
// echo "<!-- Current URL: " . $_SERVER['REQUEST_URI'] . " -->";
// echo "<!-- PHP_SELF: " . $_SERVER['PHP_SELF'] . " -->";
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Login</h3>
                
                <!-- Updated form action to use full path -->
                <form action="/IM-final-project/public/index.php?page=login&action=process" method="POST">
                    <?php echo CSRF::getTokenField(); ?>
                    
                    <div class="form-outline mb-4">
                        <label class="form-label" for="email">Email address</label>
                        <input type="email" id="email" class="form-control" name="email" 
                               value="<?php echo htmlspecialchars(Session::get('email', '')); Session::remove('email'); ?>" required />
                        <?php if (Session::getFlash('emailError')): ?>
                            <small class="text-danger"><?php echo Session::getFlash('emailError'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-outline mb-4">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" class="form-control" name="password" required />
                        <?php if (Session::getFlash('passwordError')): ?>
                            <small class="text-danger"><?php echo Session::getFlash('passwordError'); ?></small>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block mb-4 w-100" name="submit" value="submit">Sign in</button>

                    <div class="text-center">
                        <p>Not a member? <a href="index.php?page=register">Register</a></p>
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