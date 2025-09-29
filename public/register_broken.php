<?php
// register.php - Working Registration System
declare(strict_types=1);

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';

$debug = [];

$pageTitle = "Register";
$pageCSS = "assets/css/register.css";

$errors = [];
$success = false;
$old = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $debug[] = 'Form data: ' . json_encode(['full_name' => $old['full_name'], 'email' => $old['email'], 'password_len' => strlen($password), 'accept_terms' => $accept]);
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
        

        $debug[] = 'Validation errors: ' . json_encode($errors);
        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        
        if (!$accept) {
            $errors['accept_terms'] = 'You must accept the terms.';
        }
                $debug[] = 'Email already exists.';

        // Check if email exists
        if (!$errors) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$old['email']]);
            if ($stmt->fetch()) {
                $errors['email'] = 'An account with this email already exists.';
            }
        }

            $debug[] = 'Attempting DB insert: ' . json_encode([$first_name, $last_name, $old['email'], '[hash]']);

        // Create account
        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $name_parts = explode(' ', $old['full_name'], 2);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1] ?? '';
                $debug[] = 'Account created successfully.';

            $stmt = $pdo->prepare('INSERT INTO users (role_id, first_name, last_name, email, password_hash, created_at) VALUES (4, ?, ?, ?, ?, NOW())');
                $debug[] = 'DB insert failed: ' . json_encode($stmt->errorInfo());
            $result = $stmt->execute([$first_name, $last_name, $old['email'], $hash]);
            
            if ($result) {
                $success = true;
                $old = ['full_name' => '', 'email' => ''];
            } else {
        $debug[] = 'Exception: ' . $e->getMessage();
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

      <?php if (!empty($debug)): ?>
        <div class="alert alert-warning small" style="white-space: pre-wrap;">
          <strong>Debug Info:</strong><br>
          <?= htmlspecialchars(implode("\n", $debug)) ?>
        </div>
      <?php endif; ?>
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
          <div class="form-text">Must be at least 8 characters long</div>
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

      <?php endif; ?>

      <p class="text-center text-secondary mb-0">
        Already have an account? 
        <a href="login.php" class="link-light text-decoration-underline">Login Here</a>
      </p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
