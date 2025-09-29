<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

echo "<h1>🏋️ Mike's Trainer Dashboard Access Test</h1>";

// Check if someone is logged in
if (is_logged_in()) {
    $current_user = current_user();
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>✅ Currently Logged In:</h3>";
    echo "<p><strong>Name:</strong> {$current_user['first_name']} {$current_user['last_name']}</p>";
    echo "<p><strong>Email:</strong> {$current_user['email']}</p>";
    echo "<p><strong>Role ID:</strong> {$current_user['role_id']} ";
    
    $role_names = [1 => '(Admin)', 3 => '(Trainer)', 4 => '(Member)'];
    echo $role_names[$current_user['role_id']] ?? '(Unknown)';
    echo "</p>";
    
    // Check trainer dashboard access
    if ($current_user['role_id'] === 3 || $current_user['role_id'] === 1) {
        echo "<p style='color: green;'>🎉 <strong>You have trainer dashboard access!</strong></p>";
        echo "<a href='trainer_dashboard.php' class='btn' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👨‍🏫 Go to Trainer Dashboard</a>";
    } else {
        echo "<p style='color: red;'>❌ <strong>No trainer access - Role {$current_user['role_id']} is not trainer or admin</strong></p>";
    }
    
    echo "<p><a href='logout.php' style='color: #dc3545;'>Logout</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>🔐 Not Logged In</h3>";
    echo "<p>Please login first to test trainer dashboard access.</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mike Trainer Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        
        <!-- Mike's Login Options -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3>👨‍🏫 Mike's Trainer Login Options</h3>
            </div>
            <div class="card-body">
                
                <!-- Regular Login -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>🔐 Regular Login</h5>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace;">
                            Email: member@l9fitness.com<br>
                            Password: password123<br>
                            <small>(Mike's original account - now upgraded to Trainer)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>🔐 Dedicated Trainer Account</h5>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace;">
                            Email: mike.trainer@l9fitness.com<br>
                            Password: password123<br>
                            <small>(New dedicated trainer account)</small>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="login.php" class="btn btn-primary">
                        🔐 Go to Login Page
                    </a>
                    <a href="auth/simple_google_demo.php" class="btn btn-danger">
                        🎭 Google Demo Login
                    </a>
                    <a href="trainer_dashboard.php" class="btn btn-success">
                        👨‍🏫 Try Trainer Dashboard
                    </a>
                </div>
                
                <!-- Instructions -->
                <div class="alert alert-info">
                    <h5>📋 Instructions:</h5>
                    <ol>
                        <li><strong>Login Method 1:</strong> Click "Go to Login Page" → Use Mike's credentials above</li>
                        <li><strong>Login Method 2:</strong> Click "Google Demo Login" → Click on Mike Trainer account</li>
                        <li><strong>Test Access:</strong> After login, click "Try Trainer Dashboard"</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="card">
            <div class="card-header bg-warning">
                <h3>🔧 Troubleshooting</h3>
            </div>
            <div class="card-body">
                
                <div class="alert alert-warning">
                    <h5>If you get "Access Denied" on trainer dashboard:</h5>
                    <ol>
                        <li>Make sure you're logged in as a user with trainer role (role_id = 3)</li>
                        <li>Check that Mike's account was updated to trainer role</li>
                        <li>Try logging out and logging back in</li>
                        <li>Clear browser cache/cookies if needed</li>
                    </ol>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="fix_mike_trainer.php" class="btn btn-outline-success">
                        🔧 Re-run Mike Fix
                    </a>
                    <a href="check_credentials.php" class="btn btn-outline-info">
                        🔍 Check All Credentials
                    </a>
                    <a href="login_test_center.php" class="btn btn-outline-primary">
                        🎯 Full Login Test Center
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>