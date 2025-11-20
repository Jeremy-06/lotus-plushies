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
                <form action="/lotus-plushies/public/index.php?page=login&action=process" method="POST" id="loginForm" novalidate>
                    <?php echo CSRF::getTokenField(); ?>
                    
                    <div class="form-outline mb-4">
                        <label class="form-label" for="email">Email address</label>
                        <input type="email" id="email" class="form-control" name="email" 
                               value="<?php echo htmlspecialchars(Session::get('email', '')); Session::remove('email'); ?>" />
                        <small class="text-danger" id="emailError" style="display: none;"></small>
                        <?php if (Session::getFlash('emailError')): ?>
                            <small class="text-danger"><?php echo Session::getFlash('emailError'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-outline mb-4">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" class="form-control" name="password" />
                        <small class="text-danger" id="passwordError" style="display: none;"></small>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    // Email validation
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Real-time validation
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email === '') {
            emailError.textContent = 'Email is required';
            emailError.style.display = 'block';
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else if (!validateEmail(email)) {
            emailError.textContent = 'Please enter a valid email address';
            emailError.style.display = 'block';
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else {
            emailError.style.display = 'none';
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });

    emailInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            const email = this.value.trim();
            if (email !== '' && validateEmail(email)) {
                emailError.style.display = 'none';
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        }
    });

    passwordInput.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            passwordError.textContent = 'Password is required';
            passwordError.style.display = 'block';
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else {
            passwordError.style.display = 'none';
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });

    passwordInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
            passwordError.style.display = 'none';
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
    });

    // Form submission validation
    loginForm.addEventListener('submit', function(e) {
        let isValid = true;

        // Validate email
        const email = emailInput.value.trim();
        if (email === '') {
            emailError.textContent = 'Email is required';
            emailError.style.display = 'block';
            emailInput.classList.add('is-invalid');
            emailInput.classList.remove('is-valid');
            isValid = false;
        } else if (!validateEmail(email)) {
            emailError.textContent = 'Please enter a valid email address';
            emailError.style.display = 'block';
            emailInput.classList.add('is-invalid');
            emailInput.classList.remove('is-valid');
            isValid = false;
        } else {
            emailError.style.display = 'none';
            emailInput.classList.remove('is-invalid');
            emailInput.classList.add('is-valid');
        }

        // Validate password
        const password = passwordInput.value.trim();
        if (password === '') {
            passwordError.textContent = 'Password is required';
            passwordError.style.display = 'block';
            passwordInput.classList.add('is-invalid');
            passwordInput.classList.remove('is-valid');
            isValid = false;
        } else {
            passwordError.style.display = 'none';
            passwordInput.classList.remove('is-invalid');
            passwordInput.classList.add('is-valid');
        }

        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = loginForm.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>