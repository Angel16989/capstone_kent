<?php
// create_test_users.php - Create test admin and trainer credentials
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔥 CREATE TEST USERS - L9 FITNESS</h1>";

try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/app/helpers/auth.php';
    
    echo "<h2>✅ Files loaded successfully</h2>";
    
    // Test credentials
    $test_users = [
        [
            'role_id' => 1,
            'first_name' => 'Beast',
            'last_name' => 'Admin',
            'email' => 'beastadmin@l9.local',
            'password' => 'BeastMode123!',
            'role_name' => 'Admin'
        ],
        [
            'role_id' => 3,
            'first_name' => 'Iron',
            'last_name' => 'Trainer',
            'email' => 'trainer@l9.local',
            'password' => 'TrainHard123!',
            'role_name' => 'Trainer'
        ],
        [
            'role_id' => 4,
            'first_name' => 'Flex',
            'last_name' => 'Member',
            'email' => 'member@l9.local',
            'password' => 'FlexTime123!',
            'role_name' => 'Member'
        ]
    ];
    
    echo "<div style='background: rgba(255,68,68,0.1); padding: 20px; margin: 20px 0; border-radius: 10px; border: 2px solid #FF4444;'>";
    echo "<h2>🎯 TEST CREDENTIALS</h2>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    foreach ($test_users as $user) {
        echo "<div style='background: rgba(0,0,0,0.3); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);'>";
        echo "<h4 style='color: #FFD700; margin-bottom: 10px;'>{$user['role_name']} Account</h4>";
        echo "<p><strong>Email:</strong> <span style='color: #00CCFF;'>{$user['email']}</span></p>";
        echo "<p><strong>Password:</strong> <span style='color: #00CCFF;'>{$user['password']}</span></p>";
        echo "<p><strong>Name:</strong> {$user['first_name']} {$user['last_name']}</p>";
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    
    // Check if users already exist and create if needed
    foreach ($test_users as $user) {
        // Check if user exists
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ?');
        $stmt->execute([$user['email']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            echo "<p style='color: #FFD700;'>⚠️ User {$user['email']} already exists (ID: {$existing['id']})</p>";
        } else {
            // Create new user
            $password_hash = password_hash($user['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare('
                INSERT INTO users (role_id, first_name, last_name, email, password_hash, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, "active", NOW(), NOW())
            ');
            
            $stmt->execute([
                $user['role_id'], 
                $user['first_name'], 
                $user['last_name'], 
                $user['email'], 
                $password_hash
            ]);
            
            $new_id = $pdo->lastInsertId();
            echo "<p style='color: #28a745;'>✅ Created {$user['role_name']}: {$user['email']} (ID: {$new_id})</p>";
        }
    }
    
    echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #c3e6cb; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🎉 TEST USERS READY!</h3>";
    echo "<p>You can now use these credentials to test different roles:</p>";
    echo "<ul>";
    echo "<li><strong>Admin Panel:</strong> Login with beastadmin@l9.local</li>";
    echo "<li><strong>Trainer Dashboard:</strong> Login with trainer@l9.local</li>";
    echo "<li><strong>Member Features:</strong> Login with member@l9.local</li>";
    echo "</ul>";
    echo "<p><strong>🔗 Quick Links:</strong></p>";
    echo "<p><a href='login.php' style='color: #007bff;'>Login Page</a> | ";
    echo "<a href='login_enhanced.php' style='color: #007bff;'>Enhanced Login</a> | ";
    echo "<a href='admin_test.php' style='color: #007bff;'>Admin Test</a></p>";
    echo "</div>";
    
    // Verify roles exist
    echo "<h3>🔍 Role Verification:</h3>";
    $stmt = $pdo->query('SELECT * FROM user_roles ORDER BY id');
    $roles = $stmt->fetchAll();
    
    if (empty($roles)) {
        echo "<p style='color: #dc3545;'>❌ No roles found! Running setup...</p>";
        
        // Insert default roles
        $default_roles = [
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'staff'],
            ['id' => 3, 'name' => 'trainer'],
            ['id' => 4, 'name' => 'member']
        ];
        
        foreach ($default_roles as $role) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO user_roles (id, name) VALUES (?, ?)');
            $stmt->execute([$role['id'], $role['name']]);
        }
        
        echo "<p style='color: #28a745;'>✅ Default roles created</p>";
    } else {
        echo "<p style='color: #28a745;'>✅ Roles exist:</p>";
        echo "<ul>";
        foreach ($roles as $role) {
            echo "<li>ID: {$role['id']} - {$role['name']}</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>

<style>
body {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
    color: white;
    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
    padding: 20px;
    margin: 0;
}

h1, h2, h3 {
    color: #FF4444;
}

p {
    margin: 8px 0;
}

a {
    color: #00CCFF;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

code {
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
    color: #FFD700;
}
</style>