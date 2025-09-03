<?php
// register.php - FIXED VERSION
declare(strict_types=1);

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../app/helpers/validator.php';
    require_once __DIR__ . '/../app/helpers/auth.php';
} catch (Exception $e) {
    die("Configuration error: " . $e->getMessage());
}

// Regenerate CSRF token for each page load to prevent issues
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$pageTitle = "Register";
$pageCSS   = "assets/css/register.css";

$errors = [];
$success = false;
$old = [
    'full_name' => '',
    'email'     => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $old['full_name'] = trim($_POST['full_name'] ?? '');
        $old['email']     = strtolower(trim($_POST['email'] ?? ''));
        $password         = $_POST['password'] ?? '';
        $confirm          = $_POST['confirm_password'] ?? '';
        $accept           = isset($_POST['accept_terms']) && $_POST['accept_terms'];
        $csrf_token       = $_POST['csrf_token'] ?? '';

        // Basic validation
        if (empty($old['full_name'])) {
            $errors['full_name'] = 'Full name is required.';
        }
        
        if (empty($old['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        } else {
            // Enhanced password validation
            $password_errors = password_validate($password);
            if (!empty($password_errors)) {
                $errors['password'] = implode('<br>', $password_errors);
            }
        }
        
        if (empty($confirm)) {
            $errors['confirm_password'] = 'Please confirm your password.';
        } elseif ($password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        
        if (!$accept) {
            $errors['accept_terms'] = 'You must accept the Terms and Privacy Policy.';
        }

        // Check if user already exists
        if (empty($errors['email'])) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$old['email']]);
            if ($stmt->fetch()) {
                $errors['email'] = 'An account with this email already exists.';
            }
        }

        // If no errors, create the account
        if (empty($errors)) {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Split full name
            $name_parts = explode(' ', $old['full_name'], 2);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1] ?? '';

            // Insert user
            $stmt = $pdo->prepare('
                INSERT INTO users (role_id, first_name, last_name, email, password_hash, status, created_at) 
                VALUES (4, ?, ?, ?, ?, "active", NOW())
            ');
            
            if ($stmt->execute([$first_name, $last_name, $old['email'], $password_hash])) {
                $user_id = (int)$pdo->lastInsertId();
                
                // Get the created user
                $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, password_hash, role_id FROM users WHERE id = ?');
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Log the user in
                    login_user($user);
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php?welcome=1');
                    exit;
                } else {
                    throw new Exception('Account created but login failed. Please try logging in manually.');
                }
            } else {
                throw new Exception('Failed to create account. Please try again.');
            }
        }
        
    } catch (PDOException $e) {
        error_log("Registration DB Error: " . $e->getMessage());
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $errors['general'] = 'An account with this email already exists.';
        } else {
            $errors['general'] = 'Database error. Please try again later.';
        }
    } catch (Exception $e) {
        error_log("Registration Error: " . $e->getMessage());
        $errors['general'] = $e->getMessage();
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
        <!-- Simplified form without complex CSRF handling -->

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
            <span class="input-group-text" id="togglePass1" 
                  title="Click to show or hide your password for easier typing">
              <i class="bi bi-eye"></i>
            </span>
          </div>
          
          <!-- Password Strength Indicator -->
          <div id="passwordStrength" class="mt-2" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <small class="text-muted">Password Strength:</small>
              <small id="strengthLabel" class="fw-bold">WEAK</small>
            </div>
            <div class="progress" style="height: 6px;">
              <div id="strengthBar" class="progress-bar bg-danger" style="width: 0%"></div>
            </div>
            <div id="passwordTips" class="mt-2">
              <small class="text-muted d-block">Password must contain:</small>
              <ul class="list-unstyled small mt-1" id="passwordCriteria">
                <li id="minLength">✗ At least 8 characters</li>
                <li id="hasLower">✗ One lowercase letter</li>
                <li id="hasUpper">✗ One uppercase letter</li>
                <li id="hasNumber">✗ One number</li>
                <li id="hasSpecial">✗ One special character (!@#$%^&*)</li>
              </ul>
            </div>
          </div>
          
          <div class="invalid-feedback">Password must meet the requirements above.</div>
          <?php if (!empty($errors['password'])): ?>
            <div class="text-danger small mt-1"><?= $errors['password'] ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label" for="confirm_password">Confirm password</label>
          <div class="input-group input-group-lg">
            <input id="confirm_password" class="form-control" type="password" name="confirm_password" minlength="8" required>
            <span class="input-group-text" id="togglePass2" 
                  title="Click to show or hide your password confirmation">
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
          <label class="form-check-label" for="accept_terms" 
                 title="Please review and accept our terms and privacy policy to create your account">
            📋 I agree to the <a href="/terms.php" class="link-muted">Terms</a> and
            <a href="/privacy.php" class="link-muted">Privacy Policy</a>.
          </label>
          <div class="invalid-feedback">You must accept to continue.</div>
          <?php if (!empty($errors['accept_terms'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['accept_terms']) ?></div>
          <?php endif; ?>
        </div>

        <button class="btn btn-l9 btn-lg w-100 mb-3" id="registerBtn" 
                title="Create your free L9 Fitness account and join our community of fitness enthusiasts">
          <span class="btn-text">🎉 Create My Account</span>
          <span class="spinner-border spinner-border-sm ms-2 d-none"></span>
        </button>

        <p class="text-center text-secondary mb-0">
          Already have an account? <a href="login.php" class="link-light text-decoration-underline" 
                                     title="Sign in to your existing L9 Fitness account">
            🔑 Login Here
          </a>
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

  // Real-time password strength checker
  pwd1?.addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLabel = document.getElementById('strengthLabel');
    
    if (password.length === 0) {
      strengthDiv.style.display = 'none';
      return;
    }
    
    strengthDiv.style.display = 'block';
    
    // Check criteria
    const criteria = {
      minLength: password.length >= 8,
      hasLower: /[a-z]/.test(password),
      hasUpper: /[A-Z]/.test(password),
      hasNumber: /[0-9]/.test(password),
      hasSpecial: /[^a-zA-Z0-9]/.test(password)
    };
    
    // Update visual indicators
    Object.keys(criteria).forEach(key => {
      const element = document.getElementById(key);
      if (element) {
        element.innerHTML = criteria[key] ? '✓ ' + element.textContent.substring(2) : '✗ ' + element.textContent.substring(2);
        element.style.color = criteria[key] ? '#28a745' : '#dc3545';
      }
    });
    
    // Calculate strength
    let score = 0;
    score += Math.min(25, password.length * 2);
    if (criteria.hasLower) score += 15;
    if (criteria.hasUpper) score += 15;  
    if (criteria.hasNumber) score += 15;
    if (criteria.hasSpecial) score += 20;
    if (password.length >= 12) score += 10;
    
    score = Math.min(100, score);
    
    // Update progress bar and label
    strengthBar.style.width = score + '%';
    
    if (score < 30) {
      strengthBar.className = 'progress-bar bg-danger';
      strengthLabel.textContent = 'WEAK';
      strengthLabel.style.color = '#dc3545';
    } else if (score < 60) {
      strengthBar.className = 'progress-bar bg-warning';
      strengthLabel.textContent = 'FAIR';
      strengthLabel.style.color = '#ffc107';
    } else if (score < 80) {
      strengthBar.className = 'progress-bar bg-info';
      strengthLabel.textContent = 'GOOD';
      strengthLabel.style.color = '#17a2b8';
    } else {
      strengthBar.className = 'progress-bar bg-success';
      strengthLabel.textContent = 'STRONG';
      strengthLabel.style.color = '#28a745';
    }
  });
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
