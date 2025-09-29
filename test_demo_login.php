<?php
/**
 * Test Demo Login Flow
 */

require_once __DIR__ . '/config/config.php';

// Test if demo accounts exist
echo "<h2>🧪 Demo Login System Test</h2>";
echo "<hr>";

echo "<h3>📊 Demo Accounts Status</h3>";
$stmt = $pdo->query("SELECT id, first_name, last_name, email, role_id FROM users WHERE google_id LIKE 'fake_%' ORDER BY role_id, first_name");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($accounts)) {
    echo "<p>❌ No demo accounts found!</p>";
    echo "<p><a href='setup_demo_google_accounts.php'>Create Demo Accounts</a></p>";
} else {
    echo "<p>✅ Found " . count($accounts) . " demo accounts:</p>";
    echo "<ul>";
    foreach($accounts as $account) {
        $roleNames = [1 => 'Admin', 2 => 'Trainer', 4 => 'Member'];
        $roleName = $roleNames[$account['role_id']] ?? 'User';
        echo "<li><strong>{$account['first_name']} {$account['last_name']}</strong> ({$account['email']}) - {$roleName}</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3>🔗 Test Links</h3>";
echo "<ul>";
echo "<li><a href='public/auth/simple_google_demo.php' target='_blank'>📱 Demo Google Login Page</a></li>";
echo "<li><a href='public/login.php' target='_blank'>🔐 Regular Login Page</a></li>";
echo "<li><a href='public/admin.php' target='_blank'>👑 Admin Dashboard</a></li>";
echo "<li><a href='public/dashboard.php' target='_blank'>👤 User Dashboard</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h3>ℹ️ How to Test</h3>";
echo "<ol>";
echo "<li>Go to <strong>Regular Login Page</strong></li>";
echo "<li>Click <strong>'Continue with Google'</strong></li>";
echo "<li>Select any demo account from the list</li>";
echo "<li>You should be redirected to the appropriate dashboard</li>";
echo "</ol>";

echo "<hr>";
echo "<p><em>Last updated: " . date('Y-m-d H:i:s') . "</em></p>";
?>