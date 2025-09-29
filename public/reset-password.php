<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/validator.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

$pageTitle = "Reset Password";
$pageCSS = "/assets/css/reset-password.css";

$errors = [];
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_id = null;

// Verify token
if ($token) {
    $stmt = $pdo->prepare('SELECT pr.user_id, u.email, u.first_name 
                          FROM password_resets pr 
                          JOIN users u ON pr.user_id = u.id 
                          WHERE pr.token = ? AND pr.expires_at > NOW()');
    $stmt->execute([$token]);
    $reset_data = $stmt->fetch();
    
    if ($reset_data) {
        $valid_token = true;
        $user_id = $reset_data['user_id'];
    }
}

if (!$valid_token) {
    $errors['general'] = 'Invalid or expired reset token. Please request a new password reset link.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }
        
        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        // Check password strength
        if (strlen($password) >= 8) {
            $has_upper = preg_match('/[A-Z]/', $password);
            $has_lower = preg_match('/[a-z]/', $password);
            $has_number = preg_match('/\d/', $password);
            
            if (!$has_upper || !$has_lower || !$has_number) {
                $errors['password'] = 'Password must contain at least one uppercase letter, lowercase letter, and number.';
            }
        }

        if (empty($errors)) {
            // Update password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$password_hash, $user_id]);
            
            // Delete used reset token
            $stmt = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
            $stmt->execute([$user_id]);
            
            $success = 'Your password has been successfully reset! You can now log in with your new password.';
        }

    } catch (Exception $e) {
        $errors['general'] = 'An error occurred while resetting your password. Please try again.';
    }
}
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="reset-password-container">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-5 col-md-7">
                <div class="reset-password-card">
                    <!-- Header -->
                    <div class="card-header text-center">
                        <div class="logo-section mb-3">
                            <div class="brand-mark"></div>
                            <h2 class="brand-name">L9 Fitness</h2>
                        </div>
                        <h3 class="card-title">
                            <i class="bi bi-key me-2"></i>
                            Reset Password
                        </h3>
                        <?php if ($valid_token && !$success): ?>
                            <p class="card-subtitle">
                                Enter your new password below. Make it strong and memorable!
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Success Message -->
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div>
                                    <?php echo htmlspecialchars($success); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Login Now
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo htmlspecialchars($errors['general']); ?>
                        </div>
                        
                        <?php if (!$valid_token): ?>
                            <div class="text-center">
                                <a href="<?php echo BASE_URL; ?>forgot-password.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-arrow-clockwise me-2"></i>
                                    Request New Reset Link
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Reset Form -->
                    <?php if ($valid_token && !$success): ?>
                        <form method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <!-- New Password -->
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-2"></i>
                                    New Password
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control form-control-lg <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter new password"
                                           minlength="8"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                
                                <!-- Password Requirements -->
                                <div class="password-requirements mt-2">
                                    <small class="text-muted">
                                        Password must contain:
                                        <ul class="requirement-list">
                                            <li class="requirement" data-requirement="length">At least 8 characters</li>
                                            <li class="requirement" data-requirement="uppercase">One uppercase letter</li>
                                            <li class="requirement" data-requirement="lowercase">One lowercase letter</li>
                                            <li class="requirement" data-requirement="number">One number</li>
                                        </ul>
                                    </small>
                                </div>
                                
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['password']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-group mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill me-2"></i>
                                    Confirm Password
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control form-control-lg <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                           id="confirm_password" 
                                           name="confirm_password" 
                                           placeholder="Confirm new password"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['confirm_password']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Password Strength Indicator -->
                            <div class="password-strength mb-4">
                                <div class="strength-label">Password Strength:</div>
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="strength-text" id="strengthText">Enter password</div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>
                                <span class="btn-text">Reset Password</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none"></span>
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Footer Links -->
                    <div class="card-footer">
                        <div class="text-center">
                            <p class="mb-0">
                                <a href="<?php echo BASE_URL; ?>login.php" class="link-primary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Back to Login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    const submitBtn = document.getElementById('submitBtn');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');

    // Password toggle functionality
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // Password strength checker
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            checkPasswordStrength(password);
            updatePasswordRequirements(password);
        });
    }

    // Confirm password matching
    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    }

    // Form validation
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.querySelector('.btn-text').textContent = 'Resetting...';
                submitBtn.querySelector('.spinner-border').classList.remove('d-none');
            }
            form.classList.add('was-validated');
        });
    }

    function checkPasswordStrength(password) {
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        if (!strengthFill || !strengthText) return;

        let strength = 0;
        let strengthLabel = 'Very Weak';
        let strengthClass = 'very-weak';

        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        switch (strength) {
            case 0:
            case 1:
                strengthLabel = 'Very Weak';
                strengthClass = 'very-weak';
                break;
            case 2:
                strengthLabel = 'Weak';
                strengthClass = 'weak';
                break;
            case 3:
                strengthLabel = 'Fair';
                strengthClass = 'fair';
                break;
            case 4:
                strengthLabel = 'Good';
                strengthClass = 'good';
                break;
            case 5:
                strengthLabel = 'Strong';
                strengthClass = 'strong';
                break;
        }

        strengthFill.style.width = (strength * 20) + '%';
        strengthFill.className = 'strength-fill ' + strengthClass;
        strengthText.textContent = strengthLabel;
    }

    function updatePasswordRequirements(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password)
        };

        Object.keys(requirements).forEach(req => {
            const element = document.querySelector(`[data-requirement="${req}"]`);
            if (element) {
                if (requirements[req]) {
                    element.classList.add('met');
                } else {
                    element.classList.remove('met');
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
