<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>🔍 L9 Fitness User Accounts & Credentials</h2>";

try {
    // Check all users in the database
    echo "<h3>👥 Current Users:</h3>";
    $users_query = "SELECT id, first_name, last_name, email, role_id, status, created_at FROM users ORDER BY role_id, id";
    $users_stmt = $pdo->prepare($users_query);
    $users_stmt->execute();
    $users = $users_stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p style='color: red;'>❌ No users found in database!</p>";
    } else {
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th>ID</th><th>Name</th><th>Email</th><th>Role ID</th><th>Status</th><th>Created</th>";
        echo "</tr>";
        
        foreach ($users as $user) {
            $role_name = '';
            switch ($user['role_id']) {
                case 1: $role_name = '🔴 Admin'; break;
                case 2: $role_name = '🟡 Staff'; break;
                case 3: $role_name = '🟢 Trainer'; break;
                case 4: $role_name = '🔵 Member'; break;
                default: $role_name = '⚪ Unknown'; break;
            }
            
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['first_name']} {$user['last_name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$role_name}</td>";
            echo "<td>{$user['status']}</td>";
            echo "<td>" . date('M j, Y', strtotime($user['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
    // Check for test/default accounts
    echo "<h3>🔑 Test Credentials:</h3>";
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
    echo "<h4>Try these default accounts:</h4>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@l9fitness.com / password123</li>";
    echo "<li><strong>Trainer:</strong> trainer@l9fitness.com / password123</li>";
    echo "<li><strong>Member:</strong> member@l9fitness.com / password123</li>";
    echo "</ul>";
    echo "</div>";
    
    // Check if default accounts exist
    $check_accounts = [
        'admin@l9fitness.com' => 1,
        'trainer@l9fitness.com' => 3,
        'member@l9fitness.com' => 4
    ];
    
    foreach ($check_accounts as $email => $role_id) {
        $check_stmt = $pdo->prepare("SELECT id, email, role_id FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        $account = $check_stmt->fetch();
        
        if ($account) {
            echo "<p style='color: green;'>✅ {$email} exists with role {$role_id}</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ {$email} not found - creating now...</p>";
            
            // Create the account
            $password_hash = password_hash('password123', PASSWORD_DEFAULT);
            $names = explode('@', $email);
            $role_name = $names[0];
            
            $create_stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, email, password_hash, role_id, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'active', NOW())
            ");
            
            try {
                $create_stmt->execute([
                    ucfirst($role_name),
                    'User',
                    $email,
                    $password_hash,
                    $role_id
                ]);
                echo "<p style='color: green;'>✅ Created {$email} successfully!</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Failed to create {$email}: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Check Google OAuth settings
    echo "<h3>🔍 Google OAuth Configuration:</h3>";
    $config_files = [
        __DIR__ . '/../config/config.php',
        __DIR__ . '/../config/google_config.php'
    ];
    
    foreach ($config_files as $config_file) {
        if (file_exists($config_file)) {
            echo "<p style='color: green;'>✅ Found: " . basename($config_file) . "</p>";
            
            $content = file_get_contents($config_file);
            if (strpos($content, 'GOOGLE_CLIENT_ID') !== false || strpos($content, 'google') !== false) {
                echo "<p style='color: blue;'>📋 Contains Google configuration</p>";
                
                // Extract Google client ID if visible
                if (preg_match('/GOOGLE_CLIENT_ID.*[\'"]([^\'\"]+)[\'"]/', $content, $matches)) {
                    $client_id = $matches[1];
                    if (strlen($client_id) > 20) {
                        echo "<p style='color: green;'>✅ Google Client ID configured: " . substr($client_id, 0, 20) . "...</p>";
                    } else {
                        echo "<p style='color: red;'>❌ Google Client ID seems invalid or empty</p>";
                    }
                }
            }
        } else {
            echo "<p style='color: red;'>❌ Missing: " . basename($config_file) . "</p>";
        }
    }
    
    // Check Google callback
    $google_callback = __DIR__ . '/auth/google_callback.php';
    if (file_exists($google_callback)) {
        echo "<p style='color: green;'>✅ Google callback exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Google callback missing</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🚀 Quick Actions:</h4>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Try Login</a>";
echo "<a href='register.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📝 Register New</a>";
echo "<a href='trainer_dashboard.php' style='background: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👨‍🏫 Trainer Dashboard</a></p>";
echo "</div>";
?>