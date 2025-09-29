<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

echo "<h1>🔥 ADMIN ACCESS TEST</h1>";

echo "<h2>Session Debug:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Authentication Status:</h2>";
echo "<p><strong>is_logged_in():</strong> " . (is_logged_in() ? "✅ TRUE" : "❌ FALSE") . "</p>";

if (is_logged_in()) {
    echo "<p><strong>User Role ID:</strong> " . ($_SESSION['user']['role_id'] ?? 'NOT SET') . "</p>";
    echo "<p><strong>is_admin():</strong> " . (is_admin() ? "✅ TRUE" : "❌ FALSE") . "</p>";
    
    $current_user = current_user();
    echo "<h3>Current User Data:</h3>";
    echo "<pre>";
    print_r($current_user);
    echo "</pre>";
} else {
    echo "<p>❌ No user logged in</p>";
}

echo "<h2>Database Check:</h2>";
try {
    // Check user roles in database
    $stmt = $pdo->query('SELECT * FROM user_roles ORDER BY id');
    $roles = $stmt->fetchAll();
    
    echo "<p>✅ Database connected</p>";
    echo "<h3>Available Roles:</h3>";
    echo "<ul>";
    foreach ($roles as $role) {
        echo "<li>ID: {$role['id']}, Name: {$role['name']}</li>";
    }
    echo "</ul>";
    
    // Check admin users
    $stmt = $pdo->query('SELECT id, first_name, last_name, email, role_id FROM users WHERE role_id = 1');
    $admins = $stmt->fetchAll();
    
    echo "<h3>Admin Users:</h3>";
    if (empty($admins)) {
        echo "<p>❌ No admin users found!</p>";
        echo "<p><a href='create_admin.php'>Create Admin User</a></p>";
    } else {
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>{$admin['first_name']} {$admin['last_name']} ({$admin['email']}) - Role ID: {$admin['role_id']}</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Quick Actions:</h2>";
echo "<p><a href='login.php'>Login Page</a></p>";
echo "<p><a href='create_admin.php'>Create Admin</a></p>";
echo "<p><a href='admin.php'>Try Admin Page</a></p>";
echo "<p><a href='logout.php'>Logout</a></p>";
?>