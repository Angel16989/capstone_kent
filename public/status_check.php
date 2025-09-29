<?php
require_once __DIR__ . '/../config/config.php';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

echo "<!DOCTYPE html>";
echo "<html><head><title>Capstone-Latest Status Check</title>";
echo "<meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate' />";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #0a0a0a; color: white; }";
echo ".status-box { background: rgba(255,68,68,0.1); border: 2px solid #FF4444; padding: 20px; margin: 15px 0; border-radius: 10px; }";
echo ".success { border-color: #28a745; background: rgba(40,167,69,0.1); }";
echo ".error { border-color: #dc3545; background: rgba(220,53,69,0.1); }";
echo "</style>";
echo "</head><body>";

echo "<h1 style='color: #FF4444;'>🔍 CAPSTONE-LATEST STATUS CHECK</h1>";

// Check database connection
echo "<div class='status-box'>";
echo "<h2>📊 Database Connection</h2>";
try {
    $test = $pdo->query("SELECT 1")->fetchColumn();
    echo "<p style='color: #28a745;'>✅ Database connection: SUCCESS</p>";
    
    // Check current database
    $current_db = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "<p><strong>Current Database:</strong> {$current_db}</p>";
    
} catch (Exception $e) {
    echo "<p style='color: #dc3545;'>❌ Database connection: FAILED</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Check membership plans
echo "<div class='status-box'>";
echo "<h2>💪 Membership Plans</h2>";
try {
    $plans = $pdo->query('SELECT id, name, price, duration_days FROM membership_plans WHERE is_active=1 ORDER BY price ASC')->fetchAll();
    
    if (empty($plans)) {
        echo "<p style='color: #dc3545;'>❌ No membership plans found!</p>";
        echo "<p><a href='setup_db.php' style='color: #FFD700;'>Run Database Setup</a></p>";
    } else {
        echo "<p style='color: #28a745;'>✅ Found " . count($plans) . " membership plans:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #333;'><th>ID</th><th>Name</th><th>Price</th><th>Duration</th></tr>";
        foreach ($plans as $plan) {
            echo "<tr>";
            echo "<td>{$plan['id']}</td>";
            echo "<td style='font-weight: bold;'>{$plan['name']}</td>";
            echo "<td style='color: #28a745; font-weight: bold;'>\${$plan['price']}</td>";
            echo "<td>{$plan['duration_days']} days</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: #dc3545;'>❌ Error loading plans: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Check users
echo "<div class='status-box'>";
echo "<h2>👥 Users</h2>";
try {
    $user_count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    echo "<p style='color: #28a745;'>✅ Found {$user_count} users in database</p>";
    
    // Check if sukeem exists
    $sukeem = $pdo->query("SELECT COUNT(*) FROM users WHERE email = 'sukeem@l9.local'")->fetchColumn();
    if ($sukeem > 0) {
        echo "<p style='color: #28a745;'>✅ Test user 'sukeem@l9.local' exists</p>";
    } else {
        echo "<p style='color: #dc3545;'>❌ Test user 'sukeem@l9.local' missing</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: #dc3545;'>❌ Error checking users: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Configuration info
echo "<div class='status-box success'>";
echo "<h2>⚙️ Configuration</h2>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
echo "<p><strong>Project Path:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

// Quick navigation
echo "<div class='status-box success'>";
echo "<h2>🔗 Quick Navigation</h2>";
echo "<ul>";
echo "<li><a href='http://localhost/Capstone-latest/public/' style='color: #FFD700;'>🏠 Home Page</a></li>";
echo "<li><a href='http://localhost/Capstone-latest/public/memberships.php' style='color: #FFD700;'>💪 Memberships</a></li>";
echo "<li><a href='http://localhost/Capstone-latest/public/login.php' style='color: #FFD700;'>🔐 Login</a></li>";
echo "<li><a href='http://localhost/Capstone-latest/public/register.php' style='color: #FFD700;'>📝 Register</a></li>";
echo "<li><a href='http://localhost/Capstone-latest/public/dashboard.php' style='color: #FFD700;'>📊 Dashboard</a></li>";
echo "</ul>";
echo "</div>";

// Fix buttons
echo "<div class='status-box'>";
echo "<h2>🔧 Quick Fixes</h2>";
if (isset($_POST['setup_db'])) {
    try {
        // Setup database
        require_once __DIR__ . '/../setup_db.php';
        echo "<p style='color: #28a745;'>✅ Database setup completed!</p>";
    } catch (Exception $e) {
        echo "<p style='color: #dc3545;'>❌ Database setup failed: " . $e->getMessage() . "</p>";
    }
}

if (isset($_POST['create_sukeem'])) {
    try {
        $password_hash = password_hash('Password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (role_id, first_name, last_name, email, password_hash, created_at) VALUES (4, 'Sukeem', 'User', 'sukeem@l9.local', ?, NOW())");
        $stmt->execute([$password_hash]);
        echo "<p style='color: #28a745;'>✅ Test user 'sukeem' created!</p>";
    } catch (Exception $e) {
        echo "<p style='color: #dc3545;'>❌ Error creating user: " . $e->getMessage() . "</p>";
    }
}

echo "<form method='post' style='display: inline-block; margin: 10px;'>";
echo "<button type='submit' name='setup_db' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>🔧 Setup Database</button>";
echo "</form>";

echo "<form method='post' style='display: inline-block; margin: 10px;'>";
echo "<button type='submit' name='create_sukeem' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>👤 Create Test User</button>";
echo "</form>";

echo "</div>";

echo "</body></html>";
?>