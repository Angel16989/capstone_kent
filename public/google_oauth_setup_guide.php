<?php
/**
 * Real Google OAuth Setup Guide
 * Step-by-step instructions for implementing real Google OAuth
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_login();

if (!is_admin()) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$pageTitle = "Real Google OAuth Setup Guide";
$pageCSS = "assets/css/admin.css";
$currentRedirectUri = BASE_URL . 'auth/google_callback.php';

include __DIR__ . '/../app/views/layouts/header.php';
?>

<style>
.setup-guide {
    max-width: 900px;
    margin: 0 auto;
}

.step-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    overflow: hidden;
    border-left: 5px solid #4285f4;
}

.step-header {
    background: linear-gradient(135deg, #4285f4, #34a853);
    color: white;
    padding: 20px 25px;
    display: flex;
    align-items: center;
}

.step-number {
    background: rgba(255,255,255,0.2);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
    font-size: 18px;
}

.step-content {
    padding: 25px;
}

.code-block {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    font-family: 'Courier New', monospace;
    margin: 15px 0;
    overflow-x: auto;
}

.highlight-box {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 15px;
    margin: 15px 0;
}

.success-box {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    padding: 15px;
    margin: 15px 0;
}

.copy-button {
    background: #6c757d;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    margin-left: 10px;
}

.copy-button:hover {
    background: #5a6268;
}

.screenshot-placeholder {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    padding: 40px;
    text-align: center;
    border-radius: 8px;
    margin: 15px 0;
    color: #6c757d;
}
</style>

<div class="container-fluid py-4">
    <div class="setup-guide">
        <div class="text-center mb-5">
            <h1 class="display-4 text-primary mb-3">
                <i class="fab fa-google"></i>
                Real Google OAuth Setup
            </h1>
            <p class="lead text-muted">
                Transform your demo into production-ready Google authentication in just 5 minutes!
            </p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Great News:</strong> All the complex OAuth code is already built! 
                You just need to get FREE credentials from Google.
            </div>
        </div>

        <!-- Step 1: Google Cloud Console -->
        <div class="step-card">
            <div class="step-header">
                <div class="step-number">1</div>
                <div>
                    <h4 class="mb-1">Access Google Cloud Console</h4>
                    <small>Get your free Google developer account</small>
                </div>
            </div>
            <div class="step-content">
                <p><strong>What to do:</strong> Go to Google Cloud Console and sign in with any Gmail account.</p>
                
                <div class="d-flex align-items-center mb-3">
                    <a href="https://console.developers.google.com/" target="_blank" class="btn btn-primary me-3">
                        <i class="fas fa-external-link-alt"></i> Open Google Cloud Console
                    </a>
                    <small class="text-muted">Opens in new tab - completely free!</small>
                </div>

                <div class="highlight-box">
                    <i class="fas fa-lightbulb text-warning"></i>
                    <strong>Pro Tip:</strong> Use the same Gmail account you want to associate with your app. 
                    You can always add more admins later.
                </div>
            </div>
        </div>

        <!-- Step 2: Create Project -->
        <div class="step-card">
            <div class="step-header">
                <div class="step-number">2</div>
                <div>
                    <h4 class="mb-1">Create New Project</h4>
                    <small>Set up your app's Google project</small>
                </div>
            </div>
            <div class="step-content">
                <p><strong>What to do:</strong> Create a new project for your L9 Fitness app.</p>
                
                <ol>
                    <li>Click <strong>"Select a project"</strong> at the top</li>
                    <li>Click <strong>"New Project"</strong></li>
                    <li>Enter project name: <code>L9 Fitness Gym</code></li>
                    <li>Click <strong>"Create"</strong></li>
                </ol>

                <div class="screenshot-placeholder">
                    <i class="fas fa-image fa-2x mb-2"></i>
                    <p>Screenshot: Google Cloud Console project creation</p>
                </div>

                <div class="success-box">
                    <i class="fas fa-check-circle text-success"></i>
                    <strong>Expected result:</strong> You should see "L9 Fitness Gym" as your selected project.
                </div>
            </div>
        </div>

        <!-- Step 3: Enable APIs -->
        <div class="step-card">
            <div class="step-header">
                <div class="step-number">3</div>
                <div>
                    <h4 class="mb-1">Enable Google+ API</h4>
                    <small>Activate the authentication service</small>
                </div>
            </div>
            <div class="step-content">
                <p><strong>What to do:</strong> Enable the Google+ API for user authentication.</p>
                
                <ol>
                    <li>Go to <strong>"APIs & Services" → "Library"</strong></li>
                    <li>Search for <strong>"Google+ API"</strong></li>
                    <li>Click on it and press <strong>"Enable"</strong></li>
                    <li>Wait for it to activate (usually instant)</li>
                </ol>

                <div class="highlight-box">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    <strong>Alternative:</strong> If Google+ API is deprecated, enable <strong>"Google Identity"</strong> or 
                    <strong>"People API"</strong> instead. Both work with our code!
                </div>
            </div>
        </div>

        <!-- Step 4: Create Credentials -->
        <div class="step-card">
            <div class="step-header">
                <div class="step-number">4</div>
                <div>
                    <h4 class="mb-1">Create OAuth 2.0 Credentials</h4>
                    <small>Get your Client ID and Secret</small>
                </div>
            </div>
            <div class="step-content">
                <p><strong>What to do:</strong> Create the credentials your app needs to authenticate users.</p>
                
                <ol>
                    <li>Go to <strong>"APIs & Services" → "Credentials"</strong></li>
                    <li>Click <strong>"+ Create Credentials" → "OAuth client ID"</strong></li>
                    <li>Choose <strong>"Web application"</strong></li>
                    <li>Name it: <code>L9 Fitness OAuth</code></li>
                    <li>Add <strong>Authorized redirect URI:</strong></li>
                </ol>

                <div class="code-block">
                    <strong>Redirect URI to add:</strong>
                    <br>
                    <code id="redirect-uri"><?php echo $currentRedirectUri; ?></code>
                    <button class="copy-button" onclick="copyToClipboard('redirect-uri')">Copy</button>
                </div>

                <div class="highlight-box">
                    <i class="fas fa-copy text-info"></i>
                    <strong>Important:</strong> Copy the redirect URI exactly as shown above. 
                    This tells Google where to send users after they log in.
                </div>
            </div>
        </div>

        <!-- Step 5: Get Credentials -->
        <div class="step-card">
            <div class="step-header">
                <div class="step-number">5</div>
                <div>
                    <h4 class="mb-1">Copy Your Credentials</h4>
                    <small>Get the Client ID and Client Secret</small>
                </div>
            </div>
            <div class="step-content">
                <p><strong>What to do:</strong> Copy the credentials Google generates for you.</p>
                
                <div class="success-box">
                    <i class="fas fa-key text-success"></i>
                    <strong>You'll get two important values:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Client ID:</strong> Long string ending in <code>.googleusercontent.com</code></li>
                        <li><strong>Client Secret:</strong> Shorter random string</li>
                    </ul>
                </div>

                <div class="highlight-box">
                    <i class="fas fa-shield-alt text-warning"></i>
                    <strong>Security Note:</strong> Keep your Client Secret private! Never share it publicly or commit it to version control.
                </div>
            </div>
        </div>

        <!-- Step 6: Update Config -->
        <div class="step-card">
            <div class="step-header">
                <div class="step-number">6</div>
                <div>
                    <h4 class="mb-1">Update Configuration File</h4>
                    <small>Add credentials to your app</small>
                </div>
            </div>
            <div class="step-content">
                <p><strong>What to do:</strong> Replace the demo credentials with your real Google credentials.</p>
                
                <p><strong>Edit this file:</strong> <code>config/google_config.php</code></p>

                <div class="code-block">
<strong>Change these lines:</strong><br>
<span style="color: #dc3545;">// OLD (Demo)</span><br>
define('GOOGLE_CLIENT_ID', '');<br>
define('GOOGLE_CLIENT_SECRET', '');<br><br>

<span style="color: #28a745;">// NEW (Your credentials)</span><br>
define('GOOGLE_CLIENT_ID', 'YOUR_CLIENT_ID_HERE.googleusercontent.com');<br>
define('GOOGLE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE');
                </div>

                <div class="highlight-box">
                    <i class="fas fa-code text-info"></i>
                    <strong>Pro Tip:</strong> Make sure to keep the quotes around your credentials. 
                    Replace only the text inside the quotes.
                </div>
            </div>
        </div>

        <!-- Step 7: Test & Deploy -->
        <div class="step-card">
            <div class="step-header">
                <div class="step-number">7</div>
                <div>
                    <h4 class="mb-1">Test & Go Live!</h4>
                    <small>Verify everything works perfectly</small>
                </div>
            </div>
            <div class="step-content">
                <p><strong>What to do:</strong> Test your real Google OAuth integration.</p>
                
                <ol>
                    <li><strong>Test login:</strong> Go to your login page and click "Continue with Google"</li>
                    <li><strong>Verify redirect:</strong> You should be taken to real Google login</li>
                    <li><strong>Complete flow:</strong> After logging in, you should return to your app</li>
                    <li><strong>Check user data:</strong> Verify user information is saved correctly</li>
                </ol>

                <div class="success-box">
                    <i class="fas fa-rocket text-success"></i>
                    <strong>Congratulations!</strong> You now have professional Google OAuth authentication! 
                    Your users can log in with their real Google accounts.
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-success btn-lg me-3">
                        <i class="fas fa-test"></i> Test Google Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-arrow-left"></i> Back to Admin
                    </a>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="card mt-5">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-tools"></i> Troubleshooting Common Issues
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-exclamation-triangle text-danger"></i> "redirect_uri_mismatch" Error</h6>
                        <p><small>
                            <strong>Solution:</strong> Check that your redirect URI in Google Console exactly matches: 
                            <code><?php echo $currentRedirectUri; ?></code>
                        </small></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-ban text-danger"></i> "access_denied" Error</h6>
                        <p><small>
                            <strong>Solution:</strong> User clicked "Cancel" on Google login. This is normal user behavior.
                        </small></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-key text-warning"></i> "invalid_client" Error</h6>
                        <p><small>
                            <strong>Solution:</strong> Double-check your Client ID and Client Secret in the config file.
                        </small></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-globe text-info"></i> Still Using Demo?</h6>
                        <p><small>
                            <strong>Check:</strong> Make sure you updated the config file and cleared any browser cache.
                        </small></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost Breakdown -->
        <div class="card mt-4 mb-5">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-dollar-sign"></i> Cost Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success">$0</h3>
                            <small>Google OAuth API</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success">$0</h3>
                            <small>User Accounts</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success">$0</h3>
                            <small>Monthly Fees</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success">∞</h3>
                            <small>User Limit</small>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <strong class="text-success">Total Cost: FREE FOREVER!</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.innerText;
    
    navigator.clipboard.writeText(text).then(function() {
        // Show success feedback
        const button = element.nextElementSibling;
        const originalText = button.innerText;
        button.innerText = 'Copied!';
        button.style.background = '#28a745';
        
        setTimeout(function() {
            button.innerText = originalText;
            button.style.background = '#6c757d';
        }, 2000);
    });
}

// Add smooth scrolling to step cards
document.querySelectorAll('.step-card').forEach((card, index) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'all 0.5s ease';
    
    setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, index * 100);
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>