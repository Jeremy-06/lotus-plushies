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
                
                <form action="index.php?page=register&action=process" method="POST" id="registerForm" novalidate>
                    <?php echo CSRF::getTokenField(); ?>
                    
                    <div class="form-outline mb-4">
                        <label class="form-label" for="email">Email address</label>
                        <input type="email" id="email" class="form-control" name="email" 
                               value="<?php echo Session::get('email', ''); Session::remove('email'); ?>" />
                        <small class="text-danger" id="emailError" style="display: none;"></small>
                        <?php if (Session::getFlash('emailError')): ?>
                            <small class="text-danger"><?php echo Session::getFlash('emailError'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-outline mb-4">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" class="form-control" name="password" />
                        <small class="form-text text-muted">Minimum 6 characters</small>
                        <small class="text-danger" id="passwordError" style="display: none;"></small>
                        <?php if (Session::getFlash('passwordError')): ?>
                            <small class="text-danger d-block"><?php echo Session::getFlash('passwordError'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-outline mb-4">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" class="form-control" name="confirm_password" />
                        <small class="text-danger" id="confirmPasswordError" style="display: none;"></small>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');

    // Email validation
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Real-time email validation
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

    // Real-time password validation
    passwordInput.addEventListener('blur', function() {
        const password = this.value.trim();
        if (password === '') {
            passwordError.textContent = 'Password is required';
            passwordError.style.display = 'block';
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else if (password.length < 6) {
            passwordError.textContent = 'Password must be at least 6 characters long';
            passwordError.style.display = 'block';
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else {
            passwordError.style.display = 'none';
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        }
        
        // Revalidate confirm password if it has a value
        if (confirmPasswordInput.value !== '') {
            validateConfirmPassword();
        }
    });

    passwordInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            const password = this.value.trim();
            if (password !== '' && password.length >= 6) {
                passwordError.style.display = 'none';
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        }
        
        // Revalidate confirm password if it has a value
        if (confirmPasswordInput.value !== '' && confirmPasswordInput.classList.contains('is-invalid')) {
            validateConfirmPassword();
        }
    });

    // Confirm password validation
    function validateConfirmPassword() {
        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();
        
        if (confirmPassword === '') {
            confirmPasswordError.textContent = 'Please confirm your password';
            confirmPasswordError.style.display = 'block';
            confirmPasswordInput.classList.add('is-invalid');
            confirmPasswordInput.classList.remove('is-valid');
            return false;
        } else if (password !== confirmPassword) {
            confirmPasswordError.textContent = 'Passwords do not match';
            confirmPasswordError.style.display = 'block';
            confirmPasswordInput.classList.add('is-invalid');
            confirmPasswordInput.classList.remove('is-valid');
            return false;
        } else {
            confirmPasswordError.style.display = 'none';
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.add('is-valid');
            return true;
        }
    }

    confirmPasswordInput.addEventListener('blur', validateConfirmPassword);

    confirmPasswordInput.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            validateConfirmPassword();
        }
    });

    // Form submission validation
    registerForm.addEventListener('submit', function(e) {
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
        } else if (password.length < 6) {
            passwordError.textContent = 'Password must be at least 6 characters long';
            passwordError.style.display = 'block';
            passwordInput.classList.add('is-invalid');
            passwordInput.classList.remove('is-valid');
            isValid = false;
        } else {
            passwordError.style.display = 'none';
            passwordInput.classList.remove('is-invalid');
            passwordInput.classList.add('is-valid');
        }

        // Validate confirm password
        if (!validateConfirmPassword()) {
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = registerForm.querySelector('.is-invalid');
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