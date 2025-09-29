<?php
/**
 * Fake Google OAuth Demo Page
 * Simulates Google login for demonstration purposes
 */

require_once __DIR__ . '/../../config/config.php';

$pageTitle = "Sign in with Google - Demo";
$pageCSS = "assets/css/login.css";

// Check if we should show quick accounts first
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE google_id LIKE 'fake_%'");
    $hasGoogleUsers = $stmt->fetchColumn() > 0;
    
    // If there are existing Google users and no explicit bypass, show quick accounts
    if ($hasGoogleUsers && !isset($_GET['new_account'])) {
        header('Location: ' . BASE_URL . 'auth/google_accounts.php');
        exit;
    }
} catch (Exception $e) {
    // Continue to regular login form
}

// Handle the fake Google login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Demo credentials - you can add more here
    $demoAccounts = [
        'demo@gmail.com' => [
            'password' => 'demo123',
            'name' => 'Demo User',
            'first_name' => 'Demo',
            'last_name' => 'User',
            'avatar' => 'https://via.placeholder.com/150'
        ],
        'john.doe@gmail.com' => [
            'password' => 'google123',
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'avatar' => 'https://via.placeholder.com/150'
        ],
        'sarah.wilson@gmail.com' => [
            'password' => 'password',
            'name' => 'Sarah Wilson',
            'first_name' => 'Sarah',
            'last_name' => 'Wilson',
            'avatar' => 'https://via.placeholder.com/150'
        ]
    ];
    
    if (isset($demoAccounts[$email]) && $demoAccounts[$email]['password'] === $password) {
        // Store the fake Google user data in session
        $_SESSION['fake_google_user'] = [
            'email' => $email,
            'name' => $demoAccounts[$email]['name'],
            'first_name' => $demoAccounts[$email]['first_name'],
            'last_name' => $demoAccounts[$email]['last_name'],
            'picture' => $demoAccounts[$email]['avatar'],
            'verified_email' => true
        ];
        
        // Redirect to our callback handler
        header('Location: ' . BASE_URL . 'auth/fake_google_callback.php');
        exit;
    } else {
        $error = 'Invalid email or password. Try the demo accounts shown below.';
    }
}

include __DIR__ . '/../../app/views/layouts/header.php';
?>

<style>
.fake-google-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    max-width: 450px;
    margin: 50px auto;
    padding: 40px;
}

.google-logo {
    text-align: center;
    margin-bottom: 30px;
}

.google-logo img {
    width: 75px;
    height: 75px;
}

.demo-badge {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 14px;
}

.demo-accounts {
    background: #e8f5e9;
    border: 1px solid #c8e6c9;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 20px;
}

.demo-accounts h6 {
    color: #2e7d32;
    margin-bottom: 10px;
    font-weight: 600;
}

.demo-account {
    background: white;
    padding: 8px 12px;
    border-radius: 4px;
    margin-bottom: 8px;
    font-size: 13px;
    border-left: 3px solid #4caf50;
}

.form-control {
    border: 1px solid #dadce0;
    border-radius: 4px;
    padding: 12px 16px;
    font-size: 16px;
    margin-bottom: 16px;
}

.form-control:focus {
    border-color: #4285f4;
    box-shadow: 0 0 0 2px rgba(66, 133, 244, 0.2);
}

.btn-google-signin {
    background: #4285f4;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 500;
    width: 100%;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-google-signin:hover {
    background: #3367d6;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(66, 133, 244, 0.3);
}

.back-link {
    text-align: center;
    margin-top: 20px;
}

.back-link a {
    color: #5f6368;
    text-decoration: none;
    font-size: 14px;
}

.back-link a:hover {
    text-decoration: underline;
}

.error-message {
    background: #fce8e6;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 20px;
    text-align: center;
}
</style>

<div class="container">
    <div class="fake-google-container">
        <div class="google-logo">
            <svg width="75" height="75" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
        </div>
        
        <h2 class="text-center mb-4" style="color: #202124; font-size: 24px; font-weight: 400;">
            Sign in with Google
        </h2>
        
        <div class="demo-badge">
            <i class="fas fa-info-circle"></i>
            <strong>Demo Mode:</strong> This is a simulation of Google OAuth for demonstration purposes.
        </div>

        <div style="background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
            <div style="font-weight: 600; margin-bottom: 8px;">
                <i class="fas fa-rocket"></i> Ready for Production?
            </div>
            <div style="font-size: 14px; margin-bottom: 10px;">
                Real Google OAuth is just 5 minutes away and completely FREE!
            </div>
            <a href="<?php echo BASE_URL; ?>google_oauth_setup_guide.php" 
               style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 500;">
                <i class="fas fa-external-link-alt"></i> See Setup Guide
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="demo-accounts">
            <h6><i class="fas fa-user-friends"></i> Demo Accounts Available:</h6>
            <div class="demo-account">
                <strong>Email:</strong> demo@gmail.com<br>
                <strong>Password:</strong> demo123
            </div>
            <div class="demo-account">
                <strong>Email:</strong> john.doe@gmail.com<br>
                <strong>Password:</strong> google123
            </div>
            <div class="demo-account">
                <strong>Email:</strong> sarah.wilson@gmail.com<br>
                <strong>Password:</strong> password
            </div>
        </div>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label" style="color: #5f6368; font-size: 14px;">
                    Email address
                </label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="Enter your Gmail address" required
                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label" style="color: #5f6368; font-size: 14px;">
                    Password
                </label>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn-google-signin">
                <i class="fab fa-google me-2"></i>
                Continue to L9 Fitness
            </button>
        </form>

        <div class="back-link">
            <a href="<?php echo BASE_URL; ?>auth/google_accounts.php" style="margin-right: 15px;">
                <i class="fas fa-users"></i> Choose existing account
            </a>
            <a href="<?php echo BASE_URL; ?>login.php">
                <i class="fas fa-arrow-left"></i> Back to regular login
            </a>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dadce0;">
            <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 15px;">
                <h6 style="color: #1a73e8; margin-bottom: 15px; font-weight: 600;">
                    <i class="fas fa-rocket"></i> Ready to Go Live? It's Super Easy!
                </h6>
                
                <div style="background: white; border-radius: 6px; padding: 15px; margin-bottom: 15px; border-left: 4px solid #34a853;">
                    <h6 style="color: #137333; margin-bottom: 10px; font-size: 14px;">
                        <i class="fas fa-clock"></i> Real Google OAuth Setup - Only Takes Minutes!
                    </h6>
                    <div style="font-size: 13px; color: #5f6368; line-height: 1.5;">
                        <strong>Step 1:</strong> Go to <a href="https://console.developers.google.com/" target="_blank" style="color: #1a73e8;">Google Cloud Console</a> (FREE)<br>
                        <strong>Step 2:</strong> Create a project & enable Google+ API<br>
                        <strong>Step 3:</strong> Create OAuth 2.0 credentials<br>
                        <strong>Step 4:</strong> Copy Client ID & Secret to config file<br>
                        <strong>Step 5:</strong> Set redirect URL - DONE! ✅
                    </div>
                </div>
                
                <div style="background: white; border-radius: 6px; padding: 15px; margin-bottom: 15px; border-left: 4px solid #ea4335;">
                    <h6 style="color: #d93025; margin-bottom: 10px; font-size: 14px;">
                        <i class="fas fa-code"></i> We've Already Done The Hard Work!
                    </h6>
                    <div style="font-size: 13px; color: #5f6368; line-height: 1.5;">
                        ✅ <strong>OAuth Flow:</strong> Complete authentication system built<br>
                        ✅ <strong>User Management:</strong> Auto-registration & login ready<br>
                        ✅ <strong>Database Integration:</strong> Google accounts seamlessly stored<br>
                        ✅ <strong>Error Handling:</strong> Professional error management<br>
                        ✅ <strong>Security:</strong> State verification & CSRF protection
                    </div>
                </div>

                <div style="background: #fff3cd; border-radius: 6px; padding: 15px; border-left: 4px solid #ffc107;">
                    <h6 style="color: #856404; margin-bottom: 10px; font-size: 14px;">
                        <i class="fas fa-dollar-sign"></i> Cost: $0 (Google OAuth is FREE!)
                    </h6>
                    <div style="font-size: 13px; color: #856404; line-height: 1.5;">
                        💰 <strong>No monthly fees</strong> - Google OAuth is completely free<br>
                        ⚡ <strong>Instant setup</strong> - Just add your credentials<br>
                        🔒 <strong>Enterprise security</strong> - Google handles all the complex stuff<br>
                        📈 <strong>Unlimited users</strong> - No usage limits or restrictions
                    </div>
                </div>

                <div style="text-align: center; margin-top: 15px;">
                    <a href="https://console.developers.google.com/" target="_blank" 
                       style="display: inline-block; background: #1a73e8; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        <i class="fas fa-external-link-alt"></i> Get FREE Google Credentials
                    </a>
                </div>
            </div>
            
            <div style="text-align: center;">
                <small style="color: #5f6368;">
                    <i class="fas fa-shield-alt"></i>
                    This demo simulates Google OAuth without actually connecting to Google services.
                    <br>
                    Switch to real Google OAuth by updating credentials in <code>config/google_config.php</code>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill demo credentials on click
document.addEventListener('DOMContentLoaded', function() {
    const demoAccounts = document.querySelectorAll('.demo-account');
    demoAccounts.forEach(account => {
        account.style.cursor = 'pointer';
        account.addEventListener('click', function() {
            const emailMatch = this.innerHTML.match(/Email:<\/strong>\s*([^\<]+)/);
            const passwordMatch = this.innerHTML.match(/Password:<\/strong>\s*([^\<]+)/);
            
            if (emailMatch && passwordMatch) {
                document.getElementById('email').value = emailMatch[1].trim();
                document.getElementById('password').value = passwordMatch[1].trim();
                
                // Add visual feedback
                this.style.background = '#e8f5e9';
                setTimeout(() => {
                    this.style.background = 'white';
                }, 500);
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../app/views/layouts/footer.php'; ?>