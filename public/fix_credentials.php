<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>🔧 Creating Demo Accounts & Fixing Credentials</h2>";

try {
    // First, let's create basic accounts with proper credentials
    $demo_accounts = [
        [
            'email' => 'admin@l9fitness.com',
            'password' => 'password123',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role_id' => 1,
            'google_id' => 'fake_admin_google_001'
        ],
        [
            'email' => 'trainer@l9fitness.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Trainer',
            'role_id' => 3,
            'google_id' => 'fake_trainer_google_001'
        ],
        [
            'email' => 'trainer2@l9fitness.com',
            'password' => 'password123',
            'first_name' => 'Sarah',
            'last_name' => 'Coach',
            'role_id' => 3,
            'google_id' => 'fake_trainer_google_002'
        ],
        [
            'email' => 'member@l9fitness.com',
            'password' => 'password123',
            'first_name' => 'Mike',
            'last_name' => 'Member',
            'role_id' => 4,
            'google_id' => 'fake_member_google_001'
        ],
        [
            'email' => 'jane@l9fitness.com',
            'password' => 'password123',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role_id' => 4,
            'google_id' => 'fake_member_google_002'
        ]
    ];
    
    echo "<h3>Creating Demo Accounts:</h3>";
    
    foreach ($demo_accounts as $account) {
        // Check if user already exists
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->execute([$account['email']]);
        $existing = $check_stmt->fetch();
        
        if ($existing) {
            // Update existing user with Google ID and correct password
            $update_stmt = $pdo->prepare("
                UPDATE users 
                SET password_hash = ?, google_id = ?, first_name = ?, last_name = ?, role_id = ?, status = 'active'
                WHERE email = ?
            ");
            $password_hash = password_hash($account['password'], PASSWORD_DEFAULT);
            $update_stmt->execute([
                $password_hash,
                $account['google_id'],
                $account['first_name'],
                $account['last_name'],
                $account['role_id'],
                $account['email']
            ]);
            echo "<p style='color: orange;'>⚠️ Updated existing: {$account['email']} (ID: {$existing['id']})</p>";
        } else {
            // Create new user
            $insert_stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, email, password_hash, role_id, google_id, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            $password_hash = password_hash($account['password'], PASSWORD_DEFAULT);
            $insert_stmt->execute([
                $account['first_name'],
                $account['last_name'],
                $account['email'],
                $password_hash,
                $account['role_id'],
                $account['google_id']
            ]);
            $new_id = $pdo->lastInsertId();
            echo "<p style='color: green;'>✅ Created new: {$account['email']} (ID: {$new_id})</p>";
        }
    }
    
    // Check if google_id column exists, if not add it
    try {
        $pdo->query("SELECT google_id FROM users LIMIT 1");
    } catch (Exception $e) {
        echo "<p style='color: blue;'>📋 Adding google_id column to users table...</p>";
        $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL");
        echo "<p style='color: green;'>✅ Added google_id column</p>";
        
        // Now update the accounts again
        foreach ($demo_accounts as $account) {
            $update_stmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE email = ?");
            $update_stmt->execute([$account['google_id'], $account['email']]);
        }
    }
    
    echo "<h3>📋 Test Credentials:</h3>";
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px;'>";
    echo "<h4>🔐 Regular Login (login.php):</h4>";
    echo "<ul>";
    foreach ($demo_accounts as $account) {
        $role_names = [1 => 'Admin', 3 => 'Trainer', 4 => 'Member'];
        $role = $role_names[$account['role_id']] ?? 'User';
        echo "<li><strong>{$role}:</strong> {$account['email']} / {$account['password']}</li>";
    }
    echo "</ul>";
    
    echo "<h4>🎭 Google Demo Login:</h4>";
    echo "<p>Use the 'Continue with Google (Demo)' button on the login page to see all demo accounts for instant login!</p>";
    echo "</div>";
    
    echo "<h3>🔍 Current Users in Database:</h3>";
    $all_users = $pdo->query("
        SELECT id, first_name, last_name, email, role_id, google_id, status 
        FROM users 
        ORDER BY role_id, first_name
    ")->fetchAll();
    
    echo "<table border='1' style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Google ID</th><th>Status</th>";
    echo "</tr>";
    
    foreach ($all_users as $user) {
        $role_names = [1 => '🔴 Admin', 3 => '🟢 Trainer', 4 => '🔵 Member'];
        $role = $role_names[$user['role_id']] ?? '⚪ User';
        
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['first_name']} {$user['last_name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$role}</td>";
        echo "<td>" . ($user['google_id'] ? substr($user['google_id'], 0, 20) . '...' : 'None') . "</td>";
        echo "<td>{$user['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🚀 Test Login Now:</h4>";
echo "<p>";
echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Regular Login</a>";
echo "<a href='auth/simple_google_demo.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🎭 Google Demo</a>";
echo "<a href='trainer_dashboard.php' style='background: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👨‍🏫 Trainer Dashboard</a>";
echo "</p>";
echo "</div>";
?>