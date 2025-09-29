<?php
/**
 * Session Debug Page
 * Shows current session status and allows testing
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

$pageTitle = "Session Debug";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - L9 Fitness</title>
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
        .debug-info {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
        .status-good { color: #22c55e; }
        .status-bad { color: #ef4444; }
        .status-warning { color: #ffc107; }
        .auto-refresh {
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1><i class="fas fa-dumbbell text-danger"></i> L9 FITNESS</h1>
            <h2>Session Debug & Status</h2>
        </div>

        <div class="card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="fas fa-clock"></i> Session Status</h4>
                <button class="btn btn-sm btn-outline-light" onclick="location.reload()">
                    <i class="fas fa-refresh"></i> Refresh
                </button>
            </div>
            
            <div class="debug-info">
                <strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                <strong>Session Status:</strong> <?php echo session_status() === PHP_SESSION_ACTIVE ? '<span class="status-good">Active</span>' : '<span class="status-bad">Inactive</span>'; ?><br>
                <strong>Session ID:</strong> <?php echo session_id(); ?><br>
                <strong>Is Logged In:</strong> <?php echo is_logged_in() ? '<span class="status-good">Yes</span>' : '<span class="status-bad">No</span>'; ?><br>
                
                <?php if (isset($_SESSION['user'])): ?>
                    <strong>User:</strong> <?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Unknown'); ?> (ID: <?php echo $_SESSION['user']['id']; ?>)<br>
                    <strong>Login Method:</strong> <?php echo $_SESSION['login_method'] ?? 'standard'; ?><br>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['last_activity'])): ?>
                    <strong>Last Activity:</strong> <?php echo date('Y-m-d H:i:s', $_SESSION['last_activity']); ?><br>
                    <strong>Session Age:</strong> <?php echo time() - $_SESSION['last_activity']; ?> seconds<br>
                    <strong>Remaining Time:</strong> 
                    <?php 
                    $remaining = SessionManager::getRemainingTime();
                    $minutes = floor($remaining / 60);
                    $seconds = $remaining % 60;
                    $color = $remaining <= 300 ? 'status-warning' : ($remaining <= 60 ? 'status-bad' : 'status-good');
                    echo '<span class="' . $color . '">' . sprintf('%02d:%02d', $minutes, $seconds) . '</span>';
                    ?>
                    <br>
                    <strong>Session Timeout:</strong> <?php echo SessionManager::getSessionTimeout(); ?> seconds (<?php echo SessionManager::getSessionTimeout() / 60; ?> minutes)<br>
                    <strong>Is Expired:</strong> <?php echo SessionManager::isSessionExpired() ? '<span class="status-bad">Yes</span>' : '<span class="status-good">No</span>'; ?><br>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['created'])): ?>
                    <strong>Session Created:</strong> <?php echo date('Y-m-d H:i:s', $_SESSION['created']); ?><br>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['fingerprint'])): ?>
                    <strong>Session Fingerprint:</strong> <?php echo substr($_SESSION['fingerprint'], 0, 16) . '...'; ?><br>
                <?php endif; ?>
                
                <br><strong>Session Variables:</strong><br>
                <?php
                foreach ($_SESSION as $key => $value) {
                    if (in_array($key, ['user', 'fingerprint'])) continue;
                    echo $key . ': ' . (is_array($value) ? 'Array' : $value) . '<br>';
                }
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card p-4 mb-4">
                    <h5><i class="fas fa-cog"></i> Session Actions</h5>
                    
                    <?php if (is_logged_in()): ?>
                        <button class="btn btn-primary w-100 mb-2" onclick="extendSession()">
                            <i class="fas fa-clock"></i> Extend Session
                        </button>
                        
                        <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-danger w-100 mb-2">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    <?php endif; ?>
                    
                    <button class="btn btn-warning w-100" onclick="clearSession()">
                        <i class="fas fa-trash"></i> Clear Session (Force Logout)
                    </button>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card p-4 mb-4">
                    <h5><i class="fas fa-info-circle"></i> Session Info</h5>
                    
                    <div class="debug-info">
                        <strong>PHP Settings:</strong><br>
                        session.cookie_lifetime: <?php echo ini_get('session.cookie_lifetime'); ?><br>
                        session.gc_maxlifetime: <?php echo ini_get('session.gc_maxlifetime'); ?><br>
                        session.cookie_httponly: <?php echo ini_get('session.cookie_httponly') ? 'On' : 'Off'; ?><br>
                        session.use_only_cookies: <?php echo ini_get('session.use_only_cookies') ? 'On' : 'Off'; ?><br>
                        
                        <br><strong>Browser Info:</strong><br>
                        User Agent: <?php echo substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50) . '...'; ?><br>
                        IP Address: <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?><br>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <label>
                <input type="checkbox" id="autoRefresh"> Auto-refresh every 5 seconds
            </label>
        </div>
    </div>

    <script>
        let autoRefreshInterval;
        
        function extendSession() {
            fetch('session_extend.php', {method: 'POST'})
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Session extended successfully!');
                        location.reload();
                    } else {
                        alert('Failed to extend session: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Error extending session: ' + error);
                });
        }
        
        function clearSession() {
            if (confirm('This will force logout. Are you sure?')) {
                fetch('session_destroy.php', {method: 'POST'})
                    .then(() => {
                        alert('Session cleared. Redirecting to login.');
                        window.location.href = 'login.php';
                    })
                    .catch(() => {
                        alert('Error clearing session.');
                    });
            }
        }
        
        document.getElementById('autoRefresh').addEventListener('change', function() {
            if (this.checked) {
                autoRefreshInterval = setInterval(() => {
                    location.reload();
                }, 5000);
            } else {
                clearInterval(autoRefreshInterval);
            }
        });
    </script>
</body>
</html>