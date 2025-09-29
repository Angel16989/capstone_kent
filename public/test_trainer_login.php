<?php
/**
 * Trainer Login Test Page
 * Quick test to verify trainer dashboard redirects
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$pageTitle = "Trainer Login Test";

// Show current user status if logged in
$currentUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-secondary">
                    <div class="card-header">
                        <h3 class="mb-0">🏋️ Trainer Dashboard Access Test</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($currentUser): ?>
                            <div class="alert alert-info">
                                <h5>Current Session:</h5>
                                <p><strong>Name:</strong> <?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($currentUser['email']) ?></p>
                                <p><strong>Role ID:</strong> <?= htmlspecialchars($currentUser['role_id']) ?></p>
                                <p><strong>Expected Dashboard:</strong> 
                                    <?php if ($currentUser['role_id'] == 1): ?>
                                        <span class="badge bg-danger">Admin Dashboard</span>
                                    <?php elseif ($currentUser['role_id'] == 3): ?>
                                        <span class="badge bg-success">Trainer Dashboard</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Member Dashboard</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <?php if ($currentUser['role_id'] == 3): ?>
                                <div class="alert alert-success">
                                    ✅ You should be able to access the trainer dashboard!
                                </div>
                                <a href="trainer_dashboard.php" class="btn btn-success btn-lg">
                                    🚀 Go to Trainer Dashboard
                                </a>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    ⚠️ Your account is not set up as a trainer (Role ID should be 3)
                                </div>
                            <?php endif; ?>
                            
                            <hr>
                            <a href="logout.php" class="btn btn-outline-light">Logout</a>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                You're not logged in. Please log in with a trainer account.
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <h5>🎯 Quick Trainer Login Options:</h5>
                        
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-dark">
                                    <div class="card-body">
                                        <h6 class="text-warning">Method 1: Regular Login</h6>
                                        <p class="small">Use existing trainer credentials</p>
                                        <a href="login.php" class="btn btn-outline-warning btn-sm">
                                            Login Page
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card bg-dark">
                                    <div class="card-body">
                                        <h6 class="text-info">Method 2: Google Demo</h6>
                                        <p class="small">Instant trainer access</p>
                                        <a href="google_admin.php" class="btn btn-outline-info btn-sm">
                                            Google Demo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6>🔧 Trainer Credentials:</h6>
                            <div class="bg-dark p-3 rounded">
                                <p class="mb-1"><strong>Email:</strong> <code>mike.trainer@l9fitness.com</code></p>
                                <p class="mb-1"><strong>Password:</strong> <code>password123</code></p>
                                <p class="mb-0 text-muted small">Or use member@l9fitness.com if it has trainer role</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6>📱 Expected Trainer Dashboard Features:</h6>
                            <ul class="list-group list-group-flush bg-dark">
                                <li class="list-group-item bg-dark text-light">📊 Statistics Dashboard</li>
                                <li class="list-group-item bg-dark text-light">⚡ Quick Actions (Call in Sick, Upload Suggestions)</li>
                                <li class="list-group-item bg-dark text-light">📅 Class Management</li>
                                <li class="list-group-item bg-dark text-light">💬 Message Center</li>
                                <li class="list-group-item bg-dark text-light">📁 Customer File Viewer</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>