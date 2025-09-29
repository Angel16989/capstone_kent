<?php
/**
 * Fake Google Quick Login
 * Shows previously used Google accounts for quick access
 */

require_once __DIR__ . '/../../config/config.php';

// Get users who have logged in via fake Google before
try {
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, email, google_id, updated_at
        FROM users 
        WHERE google_id LIKE 'fake_%' 
        ORDER BY updated_at DESC 
        LIMIT 10
    ");
    $googleUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $googleUsers = [];
    $error = "Database error: " . $e->getMessage();
}

// Handle quick login
if (isset($_GET['quick_login']) && isset($_GET['user_id'])) {
    $userId = (int)$_GET['user_id'];
    
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND google_id LIKE "fake_%" LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Make sure we have the auth helper
            if (!function_exists('login_user')) {
                require_once __DIR__ . '/../../app/helpers/auth.php';
            }
            login_user($user);
            $_SESSION['welcome_message'] = "Quick login successful! Welcome back, " . $user['first_name'] . "!";
            
            // Update last login time
            $stmt = $pdo->prepare('UPDATE users SET updated_at = NOW() WHERE id = ?');
            $stmt->execute([$userId]);
            
            // Redirect based on role
            $redirectUrl = ($user['role_id'] == 1) ? BASE_URL . 'admin.php' : BASE_URL . 'dashboard.php';
            
            // Try header redirect first
            if (!headers_sent()) {
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                // Fallback to JavaScript redirect
                echo "<script>window.location.href = '" . $redirectUrl . "';</script>";
                echo "<p>Redirecting... <a href='" . $redirectUrl . "'>Click here if not redirected</a></p>";
                exit;
            }
        } else {
            $error = 'User not found or not a demo Google account.';
        }
    } catch (Exception $e) {
        $error = 'Quick login failed: ' . $e->getMessage();
    }
}

$pageTitle = "Quick Google Login - Demo";
$pageCSS = "assets/css/login.css";
include __DIR__ . '/../../app/views/layouts/header.php';
?>

<style>
.quick-login-container {
    max-width: 500px;
    margin: 50px auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.header-section {
    background: linear-gradient(135deg, #4285f4, #34a853);
    color: white;
    padding: 30px;
    text-align: center;
}

.google-accounts-section {
    padding: 30px;
}

.account-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid #f1f3f4;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    color: inherit;
}

.account-item:hover {
    border-color: #4285f4;
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(66, 133, 244, 0.15);
    text-decoration: none;
    color: inherit;
}

.account-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4285f4, #ea4335);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 15px;
    font-size: 18px;
}

.account-info {
    flex: 1;
}

.account-name {
    font-weight: 600;
    color: #202124;
    margin-bottom: 2px;
}

.account-email {
    color: #5f6368;
    font-size: 14px;
}

.account-badge {
    background: #e8f5e9;
    color: #137333;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.new-account-btn {
    display: block;
    width: 100%;
    padding: 15px;
    border: 2px dashed #dadce0;
    border-radius: 8px;
    text-align: center;
    color: #5f6368;
    text-decoration: none;
    transition: all 0.2s;
    margin-top: 20px;
}

.new-account-btn:hover {
    border-color: #4285f4;
    color: #4285f4;
    text-decoration: none;
}

.demo-notice {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 15px;
    margin: 20px 30px;
    border-radius: 6px;
    text-align: center;
    font-size: 14px;
}
</style>

<div class="container">
    <div class="quick-login-container">
        <div class="header-section">
            <div style="margin-bottom: 15px;">
                <svg width="60" height="60" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
            </div>
            <h2 style="margin: 0; font-size: 24px; font-weight: 400;">Choose an account</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">to continue to L9 Fitness</p>
        </div>

            <?php if (isset($error)): ?>
                <div class="demo-notice" style="background: #f8d7da; border-color: #f5c6cb; color: #721c24;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Debug Info (remove in production) -->
            <?php if (empty($googleUsers)): ?>
                <div style="background: #fff3cd; padding: 15px; margin: 20px 30px; border-radius: 6px; font-size: 14px;">
                    <strong>Debug Info:</strong><br>
                    - Database connection: <?php echo isset($pdo) ? '✅ Connected' : '❌ Failed'; ?><br>
                    - BASE_URL: <?php echo BASE_URL; ?><br>
                    - Current file: <?php echo __FILE__; ?><br>
                    - Google users count: <?php echo count($googleUsers); ?>
                </div>
            <?php endif; ?>        <div class="google-accounts-section">
            <?php if (empty($googleUsers)): ?>
                <div style="text-align: center; padding: 20px; color: #5f6368;">
                    <i class="fas fa-user-plus" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <h5>No Google accounts found</h5>
                    <p>You haven't logged in with Google before.</p>
                </div>
            <?php else: ?>
                <h6 style="color: #5f6368; margin-bottom: 20px; font-size: 14px;">
                    <i class="fas fa-history"></i> Previously used accounts:
                </h6>
                
                <?php foreach ($googleUsers as $user): ?>
                    <a href="?quick_login=1&user_id=<?php echo $user['id']; ?>" class="account-item">
                        <div class="account-avatar">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                        </div>
                        <div class="account-info">
                            <div class="account-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                            <div class="account-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="account-badge">Demo</div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <a href="<?php echo BASE_URL; ?>auth/fake_google_login.php" class="new-account-btn">
                <i class="fas fa-plus-circle me-2"></i>
                Use another account
            </a>
        </div>

        <div class="demo-notice">
            <i class="fas fa-info-circle"></i>
            <strong>Demo Mode:</strong> This simulates Google's account selection for demonstration.
            Click any account for instant login!
        </div>

        <!-- Real Implementation Info -->
        <div style="margin: 20px 30px; background: #e8f5e9; border-radius: 8px; padding: 20px;">
            <div style="text-align: center; margin-bottom: 15px;">
                <h6 style="color: #137333; margin-bottom: 10px;">
                    <i class="fas fa-lightbulb"></i> Want Real Google OAuth? It's Just 5 Minutes Away!
                </h6>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div style="background: white; padding: 12px; border-radius: 6px; text-align: center;">
                    <div style="color: #1a73e8; font-size: 24px; margin-bottom: 8px;">⚡</div>
                    <div style="font-size: 13px; color: #137333;">
                        <strong>5 Minutes Setup</strong><br>
                        Just copy-paste credentials
                    </div>
                </div>
                <div style="background: white; padding: 12px; border-radius: 6px; text-align: center;">
                    <div style="color: #34a853; font-size: 24px; margin-bottom: 8px;">💰</div>
                    <div style="font-size: 13px; color: #137333;">
                        <strong>$0 Cost</strong><br>
                        Google OAuth is free forever
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; font-size: 12px; color: #137333;">
                <strong>We've built everything!</strong> Only missing: Google credentials (free from Google Cloud Console)
            </div>
        </div>
    </div>
</div>

<script>
// Add click animation
document.querySelectorAll('.account-item').forEach(item => {
    item.addEventListener('click', function(e) {
        this.style.transform = 'scale(0.98)';
        setTimeout(() => {
            this.style.transform = 'translateY(-2px)';
        }, 100);
    });
});
</script>

<?php include __DIR__ . '/../../app/views/layouts/footer.php'; ?>