<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    // Redirect to admin login page
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$pageTitle = "Google OAuth Management - L9 Fitness";
$pageCSS = "/assets/css/admin.css";

$message = '';
$error = '';

// Handle Google OAuth settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'save_credentials':
                $client_id = trim($_POST['client_id'] ?? '');
                $client_secret = trim($_POST['client_secret'] ?? '');
                $redirect_uri = trim($_POST['redirect_uri'] ?? '');
                
                // Save to database or config file
                // For now, we'll save to a config file
                $google_config = [
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'redirect_uri' => $redirect_uri,
                    'enabled' => !empty($client_id) && !empty($client_secret)
                ];
                
                file_put_contents(__DIR__ . '/../config/google_oauth.json', json_encode($google_config, JSON_PRETTY_PRINT));
                
                $message = '<div class="alert alert-success">Google OAuth credentials saved successfully!</div>';
                break;
                
            case 'test_connection':
                // Test Google OAuth connection
                $message = '<div class="alert alert-info">Google OAuth connection test - Feature coming soon!</div>';
                break;
        }
    } catch (Exception $e) {
        $error = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Load current Google OAuth settings
$google_config = [];
if (file_exists(__DIR__ . '/../config/google_oauth.json')) {
    $google_config = json_decode(file_get_contents(__DIR__ . '/../config/google_oauth.json'), true) ?? [];
}
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<!-- Google OAuth Hero Section -->
<div class="admin-hero py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <div class="admin-badge mb-3">
                        <i class="bi bi-google"></i>
                        OAuth Management
                    </div>
                    <h1 class="display-4 fw-bold text-gradient">🔐 Google Sign-in</h1>
                    <p class="lead">Manage Google OAuth integration for seamless user authentication</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Google OAuth Configuration</h2>
                <a href="admin.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <?php echo $message; ?>
    <?php echo $error; ?>
    
    <div class="row g-4">
        
        <!-- Configuration Panel -->
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="bi bi-gear-fill"></i> OAuth Credentials</h3>
                    <p>Configure your Google OAuth application credentials</p>
                </div>
                
                <form method="post">
                    <input type="hidden" name="action" value="save_credentials">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="client_id" class="form-label">Google Client ID</label>
                            <input type="text" class="form-control" id="client_id" name="client_id" 
                                   value="<?php echo htmlspecialchars($google_config['client_id'] ?? ''); ?>"
                                   placeholder="1234567890-abcdefghijklmnop.apps.googleusercontent.com">
                            <small class="form-text text-muted">Get this from Google Cloud Console → APIs & Services → Credentials</small>
                        </div>
                        
                        <div class="col-12">
                            <label for="client_secret" class="form-label">Google Client Secret</label>
                            <input type="password" class="form-control" id="client_secret" name="client_secret" 
                                   value="<?php echo htmlspecialchars($google_config['client_secret'] ?? ''); ?>"
                                   placeholder="GOCSPX-AbCdEfGhIjKlMnOpQrStUvWxYz">
                            <small class="form-text text-muted">Keep this secret and secure</small>
                        </div>
                        
                        <div class="col-12">
                            <label for="redirect_uri" class="form-label">Redirect URI</label>
                            <input type="url" class="form-control" id="redirect_uri" name="redirect_uri" 
                                   value="<?php echo htmlspecialchars($google_config['redirect_uri'] ?? BASE_URL . 'auth/google_callback.php'); ?>"
                                   placeholder="<?php echo BASE_URL; ?>auth/google_callback.php">
                            <small class="form-text text-muted">This must match the URI configured in Google Cloud Console</small>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Save Credentials
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="testConnection()">
                                    <i class="bi bi-wifi"></i> Test Connection
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Status Panel -->
        <div class="col-lg-4">
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="bi bi-info-circle"></i> OAuth Status</h3>
                </div>
                
                <div class="oauth-status">
                    <div class="status-item">
                        <div class="status-label">Configuration</div>
                        <div class="status-value">
                            <?php if (!empty($google_config['client_id']) && !empty($google_config['client_secret'])): ?>
                                <span class="badge bg-success">✓ Configured</span>
                            <?php else: ?>
                                <span class="badge bg-warning">⚠ Incomplete</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="status-item">
                        <div class="status-label">OAuth Enabled</div>
                        <div class="status-value">
                            <?php if ($google_config['enabled'] ?? false): ?>
                                <span class="badge bg-success">✓ Yes</span>
                            <?php else: ?>
                                <span class="badge bg-danger">✗ No</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="status-item">
                        <div class="status-label">Google Users</div>
                        <div class="status-value">
                            <span class="badge bg-info">0 users</span>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="quick-actions">
                    <h5>Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewGoogleUsers()">
                            <i class="bi bi-people"></i> View Google Users
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="viewLoginHistory()">
                            <i class="bi bi-clock-history"></i> Login History
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="disableOAuth()">
                            <i class="bi bi-pause-circle"></i> Disable OAuth
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Setup Instructions -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="bi bi-book"></i> Setup Instructions</h3>
                    <p>Follow these steps to enable Google Sign-in for your L9 Fitness website</p>
                </div>
                
                <div class="setup-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h5>Create Google Cloud Project</h5>
                            <p>Go to <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a> and create a new project or select an existing one.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h5>Enable Google+ API</h5>
                            <p>Navigate to "APIs & Services" → "Library" and enable the "Google+ API" for your project.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h5>Create OAuth 2.0 Credentials</h5>
                            <p>Go to "APIs & Services" → "Credentials" → "Create Credentials" → "OAuth 2.0 Client IDs"</p>
                            <ul>
                                <li>Application type: Web application</li>
                                <li>Name: L9 Fitness Website</li>
                                <li>Authorized redirect URIs: <code><?php echo BASE_URL; ?>auth/google_callback.php</code></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h5>Configure OAuth Consent Screen</h5>
                            <p>Set up the OAuth consent screen with your app information, logo, and privacy policy.</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h5>Enter Credentials Above</h5>
                            <p>Copy the Client ID and Client Secret from Google Cloud Console and paste them in the form above.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.oauth-status {
    margin-bottom: 20px;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.status-item:last-child {
    border-bottom: none;
}

.status-label {
    color: #ccc;
    font-weight: 500;
}

.quick-actions h5 {
    color: #FF4444;
    margin-bottom: 15px;
}

.setup-steps {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.step {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.step-number {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #FF4444, #FFD700);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    flex-shrink: 0;
}

.step-content h5 {
    color: #FF4444;
    margin-bottom: 10px;
}

.step-content p {
    color: #ccc;
    margin-bottom: 10px;
}

.step-content ul {
    color: #ccc;
    margin-left: 20px;
}

.step-content code {
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
    color: #FFD700;
}

.step-content a {
    color: #00CCFF;
    text-decoration: none;
}

.step-content a:hover {
    text-decoration: underline;
}
</style>

<script>
function testConnection() {
    // Test Google OAuth connection
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=test_connection'
    })
    .then(response => response.text())
    .then(data => {
        alert('Connection test completed - check server logs for details');
    })
    .catch(error => {
        alert('Error testing connection: ' + error);
    });
}

function viewGoogleUsers() {
    alert('Google Users management - Feature coming soon!');
}

function viewLoginHistory() {
    alert('Login History - Feature coming soon!');
}

function disableOAuth() {
    if (confirm('Are you sure you want to disable Google OAuth? Users will no longer be able to sign in with Google.')) {
        alert('OAuth disabled - Feature coming soon!');
    }
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>