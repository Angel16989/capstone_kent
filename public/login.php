<?php
// --- L9 Fitness Login Page ---
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/validator.php';

$pageTitle = "Login";
$pageCSS = "assets/css/login.css";

$error = '';
$email = '';

$loggedInMessage = '';
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $loggedInMessage = 'Account created successfully! You can now log in.';
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';

        if (!email_valid($email) || !not_empty($pass)) {
            $error = 'Enter a valid email and password.';
        } else {
            $stmt = $pdo->prepare('SELECT id, email, password_hash, first_name, last_name, role_id, phone, address, created_at FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($u && password_verify($pass, $u['password_hash'])) {
                require_once __DIR__ . '/../app/helpers/auth.php';
                login_user($u);
                header('Location: index.php');
                exit;
            }
            $error = 'Invalid credentials.';
        }
    } catch (Throwable $e) {
        $error = 'Something went wrong. Please try again.';
    }
}
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="l9-auth-wrap">
  <div class="l9-card row g-0">
    <!-- Left / Promo -->
    <div class="col-lg-6 l9-left">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="brand-mark"></div>
        <strong>L9 Fitness Gym</strong>
      </div>

      <span class="l9-badge mb-3">
        <svg width="16" height="16" fill="currentColor" class="bi bi-bolt-fill"><path d="M5.5 0h6L7.5 6h4L3.5 16l2-7H3L5.5 0z"/></svg>
        Beast Mode • No Excuses
      </span>

      <h1 class="display-6 l9-title mb-2">Unleash the Beast.</h1>
      <p class="l9-sub mb-4">Access your warrior dashboard. Track your dominance. Push your limits.</p>

      <ul class="l9-cta-points list-unstyled small text-light">
        <li>• Elite training protocols</li>
        <li>• Beast mode progress tracking</li>
        <li>• Hardcore member challenges</li>
      </ul>
      <div class="mt-4 small text-secondary">
        Tip: Press <span class="l9-kbd">/</span> to focus email, <span class="l9-kbd">Enter</span> to submit.
      </div>
    </div>

    <!-- Right / Form -->
    <div class="col-lg-6 l9-right">
      <h2 class="h4 fw-bold mb-3">Beast Login</h2>

      <?php if (!empty($loggedInMessage)): ?>
        <div class="alert alert-success py-2"><?= htmlspecialchars($loggedInMessage) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
          <label class="form-label" for="email">Email address</label>
          <input id="email" class="form-control form-control-lg" type="email" name="email"
                 value="<?= htmlspecialchars($email) ?>" autocomplete="username" required>
          <div class="invalid-feedback">Please enter a valid email.</div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <div class="input-group input-group-lg">
            <input id="password" class="form-control" type="password" name="password"
                   autocomplete="current-password" required>
            <span class="input-group-text" id="togglePass" 
                  title="Click to show or hide your password for easier typing">
              <i class="bi bi-eye" aria-hidden="true"></i>
            </span>
            <div class="invalid-feedback">Password is required.</div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" 
                   title="Keep me signed in for faster access next time">
            <label class="form-check-label" for="remember">🔒 Remember me</label>
          </div>
          <a href="forgot-password.php" class="link-muted" 
             title="Reset your password via email if you've forgotten it">
            🔑 Forgot password?
          </a>
        </div>

        <button class="btn btn-l9 btn-lg w-100 mb-3" id="loginBtn" 
                title="Sign in to access your dashboard, book classes, and track your fitness journey">
          <span class="btn-text">🚀 Login to Dashboard</span>
          <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
        </button>

        <p class="text-center text-secondary mb-0">
          New to L9? <a class="link-light text-decoration-underline" href="register.php" 
                        title="Create a free account to join our fitness community and access all features">
            ✨ Create an account
          </a>
        </p>
      </form>

      <hr class="border-secondary my-4 opacity-25">
      <div class="small text-secondary">
        By continuing, you agree to our <a href="terms.php" class="link-muted">Terms</a> and
        <a href="privacy.php" class="link-muted">Privacy Policy</a>.
      </div>
    </div>
  </div>
</div>

<script>
  // Bootstrap validation + loading state ..
  (function () {
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      } else {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.querySelector('.btn-text').textContent = 'Signing in...';
        btn.querySelector('.spinner-border').classList.remove('d-none');
      }
      form.classList.add('was-validated');
    }, false);
  })();

  // Password toggle
  const toggle = document.getElementById('togglePass');
  const pwd = document.getElementById('password');
  toggle?.addEventListener('click', () => {
    const isPwd = pwd.type === 'password';
    pwd.type = isPwd ? 'text' : 'password';
    toggle.innerHTML = isPwd ? '<i class="bi bi-eye-slash" aria-hidden="true"></i>' :
                               '<i class="bi bi-eye" aria-hidden="true"></i>';
  });

  // Keyboard shortcut: "/" focuses email
  window.addEventListener('keydown', (e) => {
    if (e.key === '/' && !e.target.closest('input, textarea')) {
      e.preventDefault();
      document.getElementById('email').focus();
    }
  });
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
