<?php
/**
 * Debug Google Demo Accounts
 * Simple test to verify demo accounts exist and work
 */

require_once __DIR__ . '/config/config.php';

echo "<!DOCTYPE html><html><head><title>Debug Google Demo</title></head><body>";
echo "<h2>Google Demo Accounts Debug</h2>";

try {
    echo "<h3>Database Connection</h3>";
    echo "Status: ✅ Connected<br>";
    echo "BASE_URL: " . BASE_URL . "<br><br>";
    
    echo "<h3>Demo Google Accounts</h3>";
    $stmt = $pdo->query("
        SELECT id, first_name, last_name, email, google_id, role_id, created_at, updated_at
        FROM users 
        WHERE google_id LIKE 'fake_%' 
        ORDER BY role_id, first_name
    ");
    
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($accounts)) {
        echo "<p style='color: red;'>❌ No demo Google accounts found!</p>";
        echo "<p><a href='setup_demo_google_accounts.php'>Click here to create demo accounts</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($accounts) . " demo accounts:</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Quick Login</th></tr>";
        
        foreach ($accounts as $account) {
            $roleNames = [1 => 'Admin', 2 => 'Trainer', 4 => 'Member'];
            $roleName = $roleNames[$account['role_id']] ?? 'Unknown';
            
            echo "<tr>";
            echo "<td>" . $account['id'] . "</td>";
            echo "<td>" . htmlspecialchars($account['first_name'] . ' ' . $account['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($account['email']) . "</td>";
            echo "<td>" . $roleName . "</td>";
            echo "<td><a href='auth/google_accounts.php?quick_login=1&user_id=" . $account['id'] . "' target='_blank'>Login as this user</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Test Links</h3>";
    echo "<p><a href='auth/google_accounts.php' target='_blank'>Test Google Accounts Page</a></p>";
    echo "<p><a href='auth/fake_google_login.php' target='_blank'>Test Fake Google Login</a></p>";
    echo "<p><a href='login.php' target='_blank'>Regular Login Page</a></p>";
    
    echo "<h3>Session Info</h3>";
    echo "Session started: " . (session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "<br>";
    echo "Current user: " . (isset($_SESSION['user']) ? $_SESSION['user']['name'] : 'Not logged in') . "<br>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>