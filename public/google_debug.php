<?php
/**
 * Google OAuth Debug & Test Page
 * L9 Fitness - Troubleshooting Tool
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/google_config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google OAuth Debug - L9 Fitness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #050505 0%, #0a0a0a 25%, #111111 50%, #000000 100%);
            color: #ffffff;
            min-height: 100vh;
        }
        .container { max-width: 900px; }
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
        .btn-google {
            background: #4285f4;
            color: white;
            border: none;
            border-radius: 25px;
        }
        .btn-google:hover {
            background: #357ae8;
            color: white;
        }
        .status-good {
            color: #22c55e;
            background: rgba(34,197,94,0.1);
            padding: 0.5rem;
            border-radius: 8px;
            border: 1px solid rgba(34,197,94,0.3);
        }
        .status-bad {
            color: #ef4444;
            background: rgba(239,68,68,0.1);
            padding: 0.5rem;
            border-radius: 8px;
            border: 1px solid rgba(239,68,68,0.3);
        }
        .debug-info {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1><i class="fas fa-dumbbell text-danger"></i> L9 FITNESS</h1>
            <h2>Google OAuth Debug</h2>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card p-4 mb-4">
                    <h4><i class="fas fa-bug"></i> Configuration Check</h4>
                    
                    <div class="mb-3">
                        <strong>Google OAuth Status:</strong><br>
                        <div class="<?= isGoogleOAuthConfigured() ? 'status-good' : 'status-bad' ?>">
                            <i class="fas <?= isGoogleOAuthConfigured() ? 'fa-check' : 'fa-times' ?>"></i>
                            <?= isGoogleOAuthConfigured() ? 'Configured' : 'Not Configured' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Files Exist:</strong><br>
                        <?php
                        $files = [
                            'google_config.php' => file_exists(__DIR__ . '/../config/google_config.php'),
                            'google_callback.php' => file_exists(__DIR__ . '/google_callback.php'),
                        ];
                        foreach ($files as $file => $exists) {
                            echo '<div class="' . ($exists ? 'status-good' : 'status-bad') . ' mb-1">';
                            echo '<i class="fas ' . ($exists ? 'fa-check' : 'fa-times') . '"></i> ' . $file;
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card p-4 mb-4">
                    <h4><i class="fas fa-link"></i> URLs & Links</h4>
                    
                    <div class="mb-3">
                        <strong>Google Auth URL:</strong><br>
                        <div class="debug-info">
                            <?php
                            try {
                                $authUrl = getGoogleAuthUrl();
                                echo '<a href="' . $authUrl . '" target="_blank" class="text-info">' . $authUrl . '</a>';
                            } catch (Exception $e) {
                                echo '<span class="text-danger">Error: ' . $e->getMessage() . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Redirect URI:</strong><br>
                        <div class="debug-info">
                            <?= GOOGLE_REDIRECT_URI ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <h4><i class="fas fa-info-circle"></i> System Information</h4>
            
            <div class="debug-info">
                <strong>Constants:</strong><br>
                BASE_URL: <?= defined('BASE_URL') ? BASE_URL : 'NOT DEFINED' ?><br>
                GOOGLE_CLIENT_ID: <?= defined('GOOGLE_CLIENT_ID') ? (GOOGLE_CLIENT_ID === 'your-google-client-id.googleusercontent.com' ? 'NOT CONFIGURED (placeholder)' : 'Configured') : 'NOT DEFINED' ?><br>
                GOOGLE_CLIENT_SECRET: <?= defined('GOOGLE_CLIENT_SECRET') ? (GOOGLE_CLIENT_SECRET === 'your-google-client-secret' ? 'NOT CONFIGURED (placeholder)' : 'Configured') : 'NOT DEFINED' ?><br>
                GOOGLE_REDIRECT_URI: <?= defined('GOOGLE_REDIRECT_URI') ? GOOGLE_REDIRECT_URI : 'NOT DEFINED' ?><br><br>
                
                <strong>Server Info:</strong><br>
                HTTP_HOST: <?= $_SERVER['HTTP_HOST'] ?? 'not set' ?><br>
                REQUEST_URI: <?= $_SERVER['REQUEST_URI'] ?? 'not set' ?><br>
                SCRIPT_NAME: <?= $_SERVER['SCRIPT_NAME'] ?? 'not set' ?><br>
                DOCUMENT_ROOT: <?= $_SERVER['DOCUMENT_ROOT'] ?? 'not set' ?><br>
                HTTPS: <?= isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'not set' ?><br>
                SERVER_PORT: <?= $_SERVER['SERVER_PORT'] ?? 'not set' ?><br><br>
                
                <strong>Session Info:</strong><br>
                Session ID: <?= session_id() ?><br>
                Session Status: <?= session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive' ?><br>
                User Logged In: <?= isset($_SESSION['user']) ? 'Yes (ID: ' . $_SESSION['user']['id'] . ')' : 'No' ?><br>
            </div>
        </div>

        <div class="card p-4">
            <h4><i class="fas fa-play"></i> Test Actions</h4>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <?php if (isGoogleOAuthConfigured()): ?>
                        <a href="<?= getGoogleAuthUrl() ?>" class="btn btn-google w-100">
                            <i class="fab fa-google"></i> Test Google Login
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fas fa-exclamation-triangle"></i> Configure First
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-4 mb-3">
                    <a href="google_oauth_setup.php?password=l9fitness123" class="btn btn-primary w-100">
                        <i class="fas fa-cog"></i> Setup Credentials
                    </a>
                </div>
                
                <div class="col-md-4 mb-3">
                    <a href="login.php" class="btn btn-info w-100">
                        <i class="fas fa-sign-in-alt"></i> Go to Login Page
                    </a>
                </div>
            </div>
            
            <div class="mt-3">
                <h6>🔍 Manual URL Tests:</h6>
                <ul class="list-unstyled">
                    <li><a href="google_callback.php" target="_blank" class="text-info">google_callback.php</a> (should show debug info)</li>
                    <li><a href="<?= BASE_URL ?>google_callback.php" target="_blank" class="text-info">Full callback URL</a> (should show debug info)</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>