<?php
/**
 * L9 Fitness Login Page with Google OAuth
 * PHP 8+ Compatible
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/google_config.php';
require_once __DIR__ . '/../app/helpers/validator.php';

$pageTitle = "Login";
$pageCSS = ["assets/css/login.css", "assets/css/chatbot.css"];

$error = '';
$email = '';

$loggedInMessage = '';
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $loggedInMessage = 'Account created successfully! You can now log in.';
}

// Handle admin/trainer redirects
if (isset($_GET['admin']) && $_GET['admin'] === '1') {
    $loggedInMessage = '🔐 Admin access required. Please log in with your admin account.';
} elseif (isset($_GET['trainer']) && $_GET['trainer'] === '1') {
    $loggedInMessage = '👨‍🏫 Trainer access required. Please log in with your trainer account.';
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
                require_once __DIR__ . '/../app/helpers/redirect.php';
                
                login_user($u);
                setWelcomeMessage($u);
                
                // Smart redirect based on user role and status
                $redirectUrl = getPostLoginRedirect($u);
                header('Location: ' . $redirectUrl);
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
      </form>

      <!-- Social Login Options -->
      <?php if (isGoogleOAuthConfigured()): ?>
      <div class="social-login-section mb-3">
        <div class="divider-with-text">
          <span>or continue with</span>
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
      <?php else: ?>
      <!-- Google OAuth disabled in development -->
      <div class="text-center mb-3">
        <small class="text-muted">
          <i class="fas fa-info-circle"></i>
          Google OAuth is disabled in development mode
        </small>
      </div>
      <?php endif; ?>

      <p class="text-center text-secondary mb-0">
        New to L9? <a class="link-light text-decoration-underline" href="register.php" 
                      title="Create a free account to join our fitness community and access all features">
          ✨ Create an account
        </a>
      </p>

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
