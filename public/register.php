<?php
// register.php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/validator.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

$pageTitle = "Register";
$pageCSS   = "/assets/css/register.css";


$errors = [];
$old = [
    'full_name' => '',
    'email'     => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();

        $old['full_name'] = trim($_POST['full_name'] ?? '');
        $old['email']     = trim($_POST['email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm          = $_POST['confirm_password'] ?? '';
        $accept           = isset($_POST['accept_terms']);

        // Validation
        if (!not_empty($old['full_name'])) {
            $errors['full_name'] = 'Full name is required.';
        }
        if (!email_valid($old['email'])) {
            $errors['email'] = 'Enter a valid email.';
        }
        if (!not_empty($password) || strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        if (!$accept) {
            $errors['accept_terms'] = 'You must accept the Terms and Privacy Policy.';
        }

        // Check existing user
        if (!$errors) {
            $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$old['email']]);
            if ($stmt->fetch()) {
                $errors['email'] = 'An account with this email already exists.';
            }
        }

        // Create account
        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare('
              INSERT INTO users (full_name, email, password_hash, created_at)
              VALUES (?, ?, ?, NOW())
            ');
            $stmt->execute([$old['full_name'], $old['email'], $hash]);

            $id = (int)$pdo->lastInsertId();
            $stmt = $pdo->prepare('SELECT id, email, full_name, password_hash FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);

            require_once __DIR__ . '/../app/helpers/auth.php';
            login_user($u);
            header('Location: /dashboard.php');
            exit;
        }
    } catch (Throwable $e) {
        $errors['general'] = 'Something went wrong. Please try again.';
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

      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($errors['general']) ?></div>
      <?php endif; ?>

      <form method="post" class="needs-validation" novalidate>
        <?= csrf_field(); ?>

        <div class="mb-3">
          <label class="form-label" for="full_name">Full name</label>
          <input id="full_name" class="form-control form-control-lg" type="text" name="full_name"
                 value="<?= htmlspecialchars($old['full_name']) ?>" required>
          <div class="invalid-feedback">Full name is required.</div>
          <?php if (!empty($errors['full_name'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['full_name']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label" for="email">Email address</label>
          <input id="email" class="form-control form-control-lg" type="email" name="email"
                 value="<?= htmlspecialchars($old['email']) ?>" autocomplete="username" required>
          <div class="invalid-feedback">Enter a valid email.</div>
          <?php if (!empty($errors['email'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['email']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <div class="input-group input-group-lg">
            <input id="password" class="form-control" type="password" name="password" minlength="8" required>
            <span class="input-group-text" id="togglePass1">
              <i class="bi bi-eye"></i>
            </span>
          </div>
          <div class="invalid-feedback">Password must be at least 8 characters.</div>
          <?php if (!empty($errors['password'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['password']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label" for="confirm_password">Confirm password</label>
          <div class="input-group input-group-lg">
            <input id="confirm_password" class="form-control" type="password" name="confirm_password" minlength="8" required>
            <span class="input-group-text" id="togglePass2">
              <i class="bi bi-eye"></i>
            </span>
          </div>
          <div class="invalid-feedback">Please confirm your password.</div>
          <?php if (!empty($errors['confirm_password'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['confirm_password']) ?></div>
          <?php endif; ?>
        </div>

        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="accept_terms" name="accept_terms" required>
          <label class="form-check-label" for="accept_terms">
            I agree to the <a href="/terms.php" class="link-muted">Terms</a> and
            <a href="/privacy.php" class="link-muted">Privacy Policy</a>.
          </label>
          <div class="invalid-feedback">You must accept to continue.</div>
          <?php if (!empty($errors['accept_terms'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['accept_terms']) ?></div>
          <?php endif; ?>
        </div>

        <button class="btn btn-l9 btn-lg w-100 mb-3" id="registerBtn">
          <span class="btn-text">Create account</span>
          <span class="spinner-border spinner-border-sm ms-2 d-none"></span>
        </button>

        <p class="text-center text-secondary mb-0">
          Already have an account? <a href="login.php" class="link-light text-decoration-underline">Login</a>
        </p>
      </form>
    </div>
  </div>
</div>

<script>
  // Validation & loading state
  (function () {
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      } else {
        const btn = document.getElementById('registerBtn');
        btn.disabled = true;
        btn.querySelector('.btn-text').textContent = 'Creating...';
        btn.querySelector('.spinner-border').classList.remove('d-none');
      }
      form.classList.add('was-validated');
    }, false);
  })();

  // Toggle password
  const toggle1 = document.getElementById('togglePass1');
  const toggle2 = document.getElementById('togglePass2');
  const pwd1 = document.getElementById('password');
  const pwd2 = document.getElementById('confirm_password');

  toggle1?.addEventListener('click', () => {
    const isPwd = pwd1.type === 'password';
    pwd1.type = isPwd ? 'text' : 'password';
    toggle1.innerHTML = isPwd ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
  });

  toggle2?.addEventListener('click', () => {
    const isPwd = pwd2.type === 'password';
    pwd2.type = isPwd ? 'text' : 'password';
    toggle2.innerHTML = isPwd ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
  });
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
