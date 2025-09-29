<?php
/**
 * Google OAuth Setup Helper
 * L9 Fitness - Admin Tool
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/google_config.php';

// Simple authentication (remove in production)
$admin_password = 'l9fitness123';
$provided_password = $_POST['admin_password'] ?? $_GET['password'] ?? '';

if ($provided_password !== $admin_password) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Google OAuth Setup - L9 Fitness</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #111; color: #fff; }
            .container { max-width: 600px; margin-top: 100px; }
            .card { background: #222; border: 1px solid #333; }
            .btn-primary { background: #FF4444; border-color: #FF4444; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card p-4">
                <h2 class="text-center mb-4">🔐 Admin Access Required</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label>Admin Password:</label>
                        <input type="password" name="admin_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Access Setup</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle configuration update
if ($_POST['action'] ?? '' === 'update_config') {
    $client_id = trim($_POST['client_id'] ?? '');
    $client_secret = trim($_POST['client_secret'] ?? '');
    
    if ($client_id && $client_secret) {
        $config_content = file_get_contents(__DIR__ . '/../config/google_config.php');
        
        $config_content = str_replace(
            "define('GOOGLE_CLIENT_ID', 'your-google-client-id.googleusercontent.com');",
            "define('GOOGLE_CLIENT_ID', '" . addslashes($client_id) . "');",
            $config_content
        );
        
        $config_content = str_replace(
            "define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');",
            "define('GOOGLE_CLIENT_SECRET', '" . addslashes($client_secret) . "');",
            $config_content
        );
        
        file_put_contents(__DIR__ . '/../config/google_config.php', $config_content);
        $success_message = "✅ Google OAuth credentials updated successfully!";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google OAuth Setup - L9 Fitness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #050505 0%, #0a0a0a 25%, #111111 50%, #000000 100%);
            color: #ffffff;
            min-height: 100vh;
        }
        .container { max-width: 800px; }
        .card {
            background: rgba(255,68,68,.06);
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255,68,68,.12);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
        }
        .btn-primary {
            background: linear-gradient(135deg, #FF4444, #FFD700);
            border: none;
            border-radius: 25px;
            font-weight: 600;
        }
        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border: none;
            border-radius: 25px;
        }
        .alert-success {
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            color: #22c55e;
        }
        .alert-info {
            background: rgba(59,130,246,0.1);
            border: 1px solid rgba(59,130,246,0.3);
            color: #3b82f6;
        }
        .form-control {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #ffffff;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.08);
            border-color: #FF4444;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(255,68,68,0.25);
        }
        .code-box {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        .status-configured {
            background: rgba(34,197,94,0.2);
            color: #22c55e;
            border: 1px solid rgba(34,197,94,0.3);
        }
        .status-not-configured {
            background: rgba(239,68,68,0.2);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.3);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1><i class="fas fa-dumbbell text-danger"></i> L9 FITNESS</h1>
            <h2>Google OAuth Setup</h2>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success_message ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card p-4 mb-4">
                    <h4><i class="fab fa-google"></i> Current Status</h4>
                    
                    <div class="mb-3">
                        <strong>Configuration Status:</strong><br>
                        <span class="status-indicator <?= isGoogleOAuthConfigured() ? 'status-configured' : 'status-not-configured' ?>">
                            <i class="fas <?= isGoogleOAuthConfigured() ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                            <?= isGoogleOAuthConfigured() ? 'Configured' : 'Not Configured' ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Current Redirect URI:</strong><br>
                        <div class="code-box">
                            <?= GOOGLE_REDIRECT_URI ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Base URL:</strong><br>
                        <div class="code-box">
                            <?= BASE_URL ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card p-4 mb-4">
                    <h4><i class="fas fa-cog"></i> Update Credentials</h4>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_config">
                        <input type="hidden" name="admin_password" value="<?= htmlspecialchars($provided_password) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Google Client ID:</label>
                            <input type="text" name="client_id" class="form-control" 
                                   placeholder="your-app.googleusercontent.com" required>
                            <small class="text-muted">From Google Cloud Console</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Google Client Secret:</label>
                            <input type="text" name="client_secret" class="form-control" 
                                   placeholder="Your client secret" required>
                            <small class="text-muted">Keep this secret and secure</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Update Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <h4><i class="fas fa-question-circle"></i> Setup Instructions</h4>
            
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> How to get Google OAuth credentials:</h6>
                <ol class="mb-0">
                    <li>Go to <a href="https://console.cloud.google.com/" target="_blank" class="text-info">Google Cloud Console</a></li>
                    <li>Create a new project or select existing one</li>
                    <li>Enable the "Google+ API" or "Google Identity API"</li>
                    <li>Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client IDs"</li>
                    <li>Choose "Web application"</li>
                    <li>Add this redirect URI: <code><?= GOOGLE_REDIRECT_URI ?></code></li>
                    <li>Copy the Client ID and Client Secret</li>
                    <li>Paste them in the form above</li>
                </ol>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h6>✅ Test Google Login:</h6>
                    <?php if (isGoogleOAuthConfigured()): ?>
                        <a href="<?= BASE_URL ?>login.php" class="btn btn-success">
                            <i class="fas fa-sign-in-alt"></i> Go to Login Page
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Configure credentials first</p>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-6">
                    <h6>🔍 Debug Info:</h6>
                    <div class="code-box">
                        HTTP_HOST: <?= $_SERVER['HTTP_HOST'] ?? 'not set' ?><br>
                        SCRIPT_NAME: <?= $_SERVER['SCRIPT_NAME'] ?? 'not set' ?><br>
                        HTTPS: <?= $_SERVER['HTTPS'] ?? 'not set' ?><br>
                        PHP_SELF: <?= $_SERVER['PHP_SELF'] ?? 'not set' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>