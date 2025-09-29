<?php
// Enhanced login page with Google Sign-in
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/validator.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

$pageTitle = "Login - L9 Fitness";
$pageCSS = "assets/css/login.css";

$error = '';
$email = '';
$success_message = '';

// Handle success messages
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $success_message = 'Account created successfully! You can now log in.';
}
if (isset($_GET['welcome']) && $_GET['welcome'] === '1') {
    $success_message = 'Welcome to L9 Fitness! Your profile has been created.';
}

// Handle error messages
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'oauth_failed':
            $error = 'Google sign-in failed. Please try again or use email/password.';
            break;
        case 'oauth_cancelled':
            $error = 'Google sign-in was cancelled.';
            break;
    }
}

// Load Google OAuth configuration
$google_config = [];
$google_enabled = false;
if (file_exists(__DIR__ . '/../config/google_oauth.json')) {
    $google_config = json_decode(file_get_contents(__DIR__ . '/../config/google_oauth.json'), true) ?? [];
    $google_enabled = $google_config['enabled'] ?? false;
}

// Generate Google OAuth URL
$google_auth_url = '';
if ($google_enabled) {
    $params = [
        'client_id' => $google_config['client_id'],
        'redirect_uri' => $google_config['redirect_uri'],
        'scope' => 'email profile',
        'response_type' => 'code',
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
    $google_auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }

        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';

        if (!email_valid($email) || !not_empty($pass)) {
            $error = 'Enter a valid email and password.';
        } else {
            $stmt = $pdo->prepare('SELECT id, email, password_hash, first_name, last_name, role_id, phone, address, created_at FROM users WHERE email = ? AND status = "active" LIMIT 1');
            $stmt->execute([$email]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($u && password_verify($pass, $u['password_hash'])) {
                require_once __DIR__ . '/../app/helpers/auth.php';
                
                // Log the login
                try {
                    $stmt = $pdo->prepare('INSERT INTO login_history (user_id, login_method, ip_address, user_agent, success) VALUES (?, "password", ?, ?, TRUE)');
                    $stmt->execute([$u['id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                } catch (Exception $e) {
                    // Login logging failed, but continue with login
                }
                
                login_user($u);
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Invalid email or password.';
        }
    } catch (Throwable $e) {
        $error = 'Something went wrong. Please try again.';
    }
}
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="l9-auth-wrap">
    <div class="l9-card row g-0">
        
        <!-- Left Side - Branding -->
        <div class="col-lg-6 l9-auth-brand">
            <div class="brand-content">
                <div class="brand-logo">
                    <div class="logo-icon">💪</div>
                    <h2>L9 FITNESS</h2>
                </div>
                <h3>Welcome Back, Warrior!</h3>
                <p>Sign in to continue your fitness journey and unlock your full potential.</p>
                
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Track your progress</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Book classes instantly</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Connect with trainers</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Access exclusive content</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="col-lg-6 l9-auth-form">
            <div class="form-container">
                <div class="form-header">
                    <h1>Sign In</h1>
                    <p>Enter your credentials to access your account</p>
                </div>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Google Sign-in Button -->
                <?php if ($google_enabled): ?>
                    <div class="google-signin-section">
                        <a href="<?php echo $google_auth_url; ?>" class="btn btn-google">
                            <svg width="20" height="20" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Continue with Google
                        </a>
                        
                        <div class="divider">
                            <span>or</span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form method="post" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($email); ?>" 
                                   placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Sign In
                    </button>
                </form>
                
                <!-- Sign Up Link -->
                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Sign up now</a></p>
                </div>
                
                <!-- Test Credentials -->
                <div class="test-credentials">
                    <h6>Test Credentials:</h6>
                    <div class="credential-item">
                        <strong>Member:</strong> sukeem@l9.local / Password123
                    </div>
                    <div class="credential-item">
                        <strong>Admin:</strong> admin@l9.local / Password123
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.l9-auth-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(0,0,0,0.9), rgba(255,68,68,0.1));
    padding: 20px;
}

.l9-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    max-width: 1000px;
    width: 100%;
}

.l9-auth-brand {
    background: linear-gradient(135deg, rgba(255,68,68,0.1), rgba(255,215,0,0.05));
    padding: 60px 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-content {
    text-align: center;
    color: white;
}

.brand-logo {
    margin-bottom: 30px;
}

.logo-icon {
    font-size: 4rem;
    margin-bottom: 10px;
}

.brand-logo h2 {
    font-size: 2.5rem;
    font-weight: bold;
    color: #FF4444;
    margin: 0;
}

.brand-content h3 {
    font-size: 1.8rem;
    margin-bottom: 15px;
    color: #FFD700;
}

.brand-content p {
    font-size: 1.1rem;
    margin-bottom: 30px;
    color: #ccc;
}

.feature-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    text-align: left;
    max-width: 300px;
    margin: 0 auto;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
}

.feature-item i {
    color: #28a745;
    font-size: 1.2rem;
}

.l9-auth-form {
    padding: 60px 40px;
}

.form-container {
    max-width: 400px;
    margin: 0 auto;
}

.form-header {
    text-align: center;
    margin-bottom: 30px;
}

.form-header h1 {
    color: #FF4444;
    font-size: 2.2rem;
    font-weight: bold;
    margin-bottom: 10px;
}

.form-header p {
    color: #ccc;
    font-size: 1rem;
}

.google-signin-section {
    margin-bottom: 30px;
}

.btn-google {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    padding: 12px;
    background: white;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-google:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.divider {
    text-align: center;
    margin: 20px 0;
    position: relative;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.2);
}

.divider span {
    background: rgba(0, 0, 0, 0.9);
    padding: 0 15px;
    color: #ccc;
    font-size: 0.9rem;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #FFD700;
    font-weight: 600;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 15px;
    z-index: 2;
    color: #999;
}

.form-control {
    width: 100%;
    padding: 12px 15px 12px 45px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    color: white;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #FF4444;
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 0 3px rgba(255, 68, 68, 0.1);
}

.password-toggle {
    position: absolute;
    right: 15px;
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    z-index: 2;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
}

.remember-me input {
    width: auto;
}

.remember-me label {
    color: #ccc;
    font-weight: normal;
    margin: 0;
}

.forgot-link {
    color: #00CCFF;
    text-decoration: none;
    font-size: 0.9rem;
}

.forgot-link:hover {
    text-decoration: underline;
}

.btn-primary {
    background: linear-gradient(135deg, #FF4444, #FFD700);
    border: none;
    padding: 15px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 68, 68, 0.3);
}

.auth-footer {
    text-align: center;
    margin-top: 30px;
    color: #ccc;
}

.auth-footer a {
    color: #FF4444;
    text-decoration: none;
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.test-credentials {
    margin-top: 30px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.test-credentials h6 {
    color: #FFD700;
    margin-bottom: 10px;
    font-weight: 600;
}

.credential-item {
    margin-bottom: 8px;
    font-size: 0.9rem;
    color: #ccc;
}

.credential-item strong {
    color: #FF4444;
}

@media (max-width: 992px) {
    .l9-auth-brand {
        display: none;
    }
    
    .l9-auth-form {
        padding: 40px 30px;
    }
}

@media (max-width: 576px) {
    .l9-auth-wrap {
        padding: 10px;
    }
    
    .l9-auth-form {
        padding: 30px 20px;
    }
    
    .form-header h1 {
        font-size: 1.8rem;
    }
}
</style>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('password-toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
    }
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>