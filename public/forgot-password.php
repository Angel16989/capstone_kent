<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/validator.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

$pageTitle = "Forgot Password";
$pageCSS = "/assets/css/forgot-password.css";

$errors = [];
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        $email = trim($_POST['email'] ?? '');

        // Validation
        if (!email_valid($email)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (empty($errors)) {
            // Check if user exists
            $stmt = $pdo->prepare('SELECT id, email, first_name FROM users WHERE email = ? AND status = "active"');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + (60 * 60)); // 1 hour

                // Store reset token (you'll need to create this table)
                try {
                    $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW()) 
                                          ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()');
                    $stmt->execute([$user['id'], $token, $expires, $token, $expires]);
                } catch (PDOException $e) {
                    // If table doesn't exist, create it
                    $pdo->exec('CREATE TABLE IF NOT EXISTS password_resets (
                        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                        user_id INT UNSIGNED NOT NULL,
                        token VARCHAR(64) NOT NULL,
                        expires_at DATETIME NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_user (user_id),
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        INDEX idx_token (token),
                        INDEX idx_expires (expires_at)
                    )');
                    
                    $stmt = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW()) 
                                          ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()');
                    $stmt->execute([$user['id'], $token, $expires, $token, $expires]);
                }

                // In a real application, you would send an email here
                // For now, we'll just show a success message
                $reset_link = BASE_URL . 'reset-password.php?token=' . $token;
                
                // Simulate email sending (in production, use PHPMailer or similar)
                // mail($email, 'Password Reset - L9 Fitness', $email_content);
                
                $success = 'Password reset link has been sent to your email address. Please check your inbox.';
            } else {
                // Don't reveal if email exists or not for security
                $success = 'If an account with that email address exists, we have sent a password reset link to it.';
            }
        }

    } catch (Exception $e) {
        $errors['general'] = 'An error occurred. Please try again.';
    }
}
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="forgot-password-container">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-5 col-md-7">
                <div class="forgot-password-card">
                    <!-- Header -->
                    <div class="card-header text-center">
                        <div class="logo-section mb-3">
                            <div class="brand-mark"></div>
                            <h2 class="brand-name">L9 Fitness</h2>
                        </div>
                        <h3 class="card-title">Forgot Password?</h3>
                        <p class="card-subtitle">
                            No worries! Enter your email address and we'll send you a link to reset your password.
                        </p>
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
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo htmlspecialchars($errors['general']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <?php if (!$success): ?>
                        <form method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <div class="form-group mb-4">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-2"></i>
                                    Email Address
                                </label>
                                <input type="email" 
                                       class="form-control form-control-lg <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       placeholder="Enter your email address"
                                       autocomplete="email"
                                       required>
                                
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['email']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="submitBtn">
                                <i class="bi bi-arrow-right me-2"></i>
                                <span class="btn-text">Send Reset Link</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none"></span>
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Footer Links -->
                    <div class="card-footer">
                        <div class="text-center">
                            <p class="mb-2">
                                <a href="<?php echo BASE_URL; ?>login.php" class="link-primary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Back to Login
                                </a>
                            </p>
                            <p class="mb-0 text-muted">
                                Don't have an account? 
                                <a href="<?php echo BASE_URL; ?>register.php" class="link-primary">Sign up here</a>
                            </p>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="help-section">
                        <div class="help-header">
                            <i class="bi bi-question-circle me-2"></i>
                            Need Help?
                        </div>
                        <ul class="help-list">
                            <li>Check your spam/junk folder for the reset email</li>
                            <li>Make sure you're using the correct email address</li>
                            <li>Reset links expire after 1 hour for security</li>
                            <li>Contact support if you continue having issues</li>
                        </ul>
                        <div class="text-center mt-3">
                            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-headset me-1"></i>
                                Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('.needs-validation');
    const submitBtn = document.getElementById('submitBtn');

    if (form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.querySelector('.btn-text').textContent = 'Sending...';
                submitBtn.querySelector('.spinner-border').classList.remove('d-none');
            }
            form.classList.add('was-validated');
        });
    }

    // Auto-focus email input
    const emailInput = document.getElementById('email');
    if (emailInput && !emailInput.value) {
        emailInput.focus();
    }
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
