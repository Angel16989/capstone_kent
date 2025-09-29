<?php
// test_db.php - Database connection test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    require_once __DIR__ . '/config/config.php';
    echo "✅ Config loaded successfully<br>";
    
    // Test database connection
    $stmt = $pdo->query('SELECT 1 as test');
    $result = $stmt->fetch();
    echo "✅ Database connection successful<br>";
    
    // Check if users table exists
    $stmt = $pdo->query('SHOW TABLES LIKE "users"');
    if ($stmt->fetch()) {
        echo "✅ Users table exists<br>";
    } else {
        echo "❌ Users table does not exist<br>";
    }
    
    // Check if user_roles table exists and has data
    $stmt = $pdo->query('SHOW TABLES LIKE "user_roles"');
    if ($stmt->fetch()) {
        echo "✅ User_roles table exists<br>";
        
        // Check roles data
        $stmt = $pdo->query('SELECT * FROM user_roles');
        $roles = $stmt->fetchAll();
        echo "✅ Found " . count($roles) . " roles:<br>";
        foreach ($roles as $role) {
            echo "&nbsp;&nbsp;- ID: {$role['id']}, Name: {$role['name']}<br>";
        }
    } else {
        echo "❌ User_roles table does not exist<br>";
    }
    
    // Test a sample user creation (dry run)
    echo "<br><h3>Testing User Creation (Dry Run)</h3>";
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM users WHERE email = ?');
    $stmt->execute(['test@test.com']);
    $result = $stmt->fetch();
    echo "✅ User email check query works - found {$result['count']} users with test email<br>";
    
    // Test password hashing
    $test_hash = password_hash('testpassword', PASSWORD_DEFAULT);
    echo "✅ Password hashing works: " . substr($test_hash, 0, 20) . "...<br>";
    
    echo "<br><h3>Session Test</h3>";
    echo "✅ Session ID: " . session_id() . "<br>";
    echo "✅ CSRF Token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "❌ File: " . $e->getFile() . "<br>";
    echo "❌ Line: " . $e->getLine() . "<br>";
    echo "❌ Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
