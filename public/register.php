<?php
/**
 * Registration Page with Google OAuth Support
 * PHP 8+ Compatible - L9 Fitness Gym
 */

declare(strict_types=1);

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/google_config.php';

$pageTitle = "Register";
$pageCSS = ["assets/css/register.css", "assets/css/chatbot.css"];

$errors = [];
$success = false;
$old = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $old['full_name'] = trim($_POST['full_name'] ?? '');
        $old['email'] = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $accept = isset($_POST['accept_terms']);

        // Simple validation
        if (empty($old['full_name'])) {
            $errors['full_name'] = 'Full name is required.';
        }
        
        if (empty($old['email']) || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required.';
        }
        
        if (empty($password) || strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        
        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        
        if (!$accept) {
            $errors['accept_terms'] = 'You must accept the terms.';
        }

        // Check if email exists
        if (!$errors) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$old['email']]);
            if ($stmt->fetch()) {
                $errors['email'] = 'An account with this email already exists.';
            }
        }

        // Create account
        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $name_parts = explode(' ', $old['full_name'], 2);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1] ?? '';

            $stmt = $pdo->prepare('INSERT INTO users (role_id, first_name, last_name, email, password_hash, created_at) VALUES (4, ?, ?, ?, ?, NOW())');
            $result = $stmt->execute([$first_name, $last_name, $old['email'], $hash]);
            
            if ($result) {
                $success = true;
                $old = ['full_name' => '', 'email' => ''];
            } else {
                $errors['general'] = 'Failed to create account. Please try again.';
            }
        }
        
    } catch (Exception $e) {
        $errors['general'] = 'System error: ' . $e->getMessage();
        error_log("Registration error: " . $e->getMessage());
    }
}

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="l9-auth-wrap">
  <div class="l9-card row g-0">
    <!-- Left column -->
    <div class="col-lg-6 l9-left">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="brand-mark"></div>
        <strong>L9 Fitness Gym</strong>
      </div>

      <span class="l9-badge mb-3">
        <i class="bi bi-bolt-fill"></i>
        No lock-in • 24/7 access
      </span>

      <h1 class="display-6 l9-title mb-2">Join the crew.</h1>
      <p class="l9-sub mb-4">Create your account to book classes, track progress, and unlock member-only perks.</p>

      <ul class="l9-cta-points list-unstyled small text-light">
        <li>• Personalized programs & PB tracking</li>
        <li>• Class reminders & waitlist auto-join</li>
        <li>• Exclusive challenges & rewards</li>
      </ul>
    </div>

    <!-- Right column -->
    <div class="col-lg-6 l9-right">
      <h2 class="h4 fw-bold mb-3">Create Account</h2>

      <?php if ($success): ?>
        <div class="alert alert-success mb-4">
          <h5><i class="bi bi-check-circle"></i> Account Created Successfully!</h5>
          <p class="mb-2">Welcome to L9 Fitness! Your account has been created.</p>
          <a href="login.php" class="btn btn-success">
            <i class="bi bi-box-arrow-in-right"></i> Login Now
          </a>
        </div>
      <?php else: ?>

      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($errors['general']) ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label" for="full_name">Full name</label>
          <input id="full_name" class="form-control form-control-lg" type="text" name="full_name" 
                 value="<?= htmlspecialchars($old['full_name']) ?>" required>
          <?php if (!empty($errors['full_name'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['full_name']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label" for="email">Email address</label>
          <input id="email" class="form-control form-control-lg" type="email" name="email" 
                 value="<?= htmlspecialchars($old['email']) ?>" required>
          <?php if (!empty($errors['email'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['email']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input id="password" class="form-control form-control-lg" type="password" name="password" minlength="8" required>
          <div class="form-text text-muted">Must be at least 8 characters long</div>
          <?php if (!empty($errors['password'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['password']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label" for="confirm_password">Confirm password</label>
          <input id="confirm_password" class="form-control form-control-lg" type="password" name="confirm_password" required>
          <?php if (!empty($errors['confirm_password'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['confirm_password']) ?></div>
          <?php endif; ?>
        </div>

        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="accept_terms" name="accept_terms" required>
          <label class="form-check-label" for="accept_terms">
            I agree to the Terms and Privacy Policy
          </label>
          <?php if (!empty($errors['accept_terms'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['accept_terms']) ?></div>
          <?php endif; ?>
        </div>

        <button class="btn btn-l9 btn-lg w-100 mb-3" type="submit">
          <i class="bi bi-person-plus"></i> Create Account
        </button>
      </form>

      <!-- Social Registration Options -->
      <?php if (isGoogleOAuthConfigured()): ?>
      <div class="social-login-section mb-3">
        <div class="divider-with-text">
          <span>or sign up with</span>
        </div>
        
        <a href="<?php echo getGoogleAuthUrl(); ?>" class="btn btn-google btn-lg w-100 mb-2">
          <svg width="20" height="20" viewBox="0 0 24 24" class="me-2">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Continue with Google (Demo)
        </a>
      </div>

      <?php endif; ?>

      <p class="text-center text-secondary mb-0">
        Already have an account? 
        <a href="login.php" class="link-light text-decoration-underline">Login Here</a>
      </p>
    </div>
  </div>
</div>

<?php endif; ?>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
