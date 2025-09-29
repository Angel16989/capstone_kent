<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>🔍 Checking Mike's Account & Fixing Trainer Access</h2>";

try {
    // Find Mike's account
    $mike_query = "SELECT * FROM users WHERE first_name LIKE '%mike%' OR email LIKE '%mike%' ORDER BY id";
    $mike_stmt = $pdo->prepare($mike_query);
    $mike_stmt->execute();
    $mike_accounts = $mike_stmt->fetchAll();
    
    echo "<h3>👤 Mike's Current Account(s):</h3>";
    if (empty($mike_accounts)) {
        echo "<p style='color: red;'>❌ No Mike accounts found!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th>ID</th><th>Name</th><th>Email</th><th>Current Role</th><th>Status</th><th>Google ID</th>";
        echo "</tr>";
        
        foreach ($mike_accounts as $mike) {
            $role_names = [1 => '🔴 Admin', 3 => '🟢 Trainer', 4 => '🔵 Member'];
            $role = $role_names[$mike['role_id']] ?? '⚪ Unknown';
            
            echo "<tr>";
            echo "<td>{$mike['id']}</td>";
            echo "<td>{$mike['first_name']} {$mike['last_name']}</td>";
            echo "<td>{$mike['email']}</td>";
            echo "<td>{$role}</td>";
            echo "<td>{$mike['status']}</td>";
            echo "<td>" . ($mike['google_id'] ?? 'None') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Solution 1: Update Mike to be a trainer
    echo "<h3>🔧 Solution 1: Make Mike a Trainer</h3>";
    if (!empty($mike_accounts)) {
        $mike = $mike_accounts[0]; // Use first Mike account
        
        if ($mike['role_id'] != 3) {
            $update_stmt = $pdo->prepare("UPDATE users SET role_id = 3 WHERE id = ?");
            $update_stmt->execute([$mike['id']]);
            echo "<p style='color: green;'>✅ Updated Mike (ID: {$mike['id']}) to Trainer role!</p>";
            
            // Also give him a Google ID if he doesn't have one
            if (empty($mike['google_id'])) {
                $update_google = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                $update_google->execute(['fake_mike_trainer_' . $mike['id'], $mike['id']]);
                echo "<p style='color: green;'>✅ Added Google ID for Mike!</p>";
            }
        } else {
            echo "<p style='color: blue;'>✅ Mike is already a trainer!</p>";
        }
    }
    
    // Solution 2: Create a dedicated Mike Trainer account
    echo "<h3>🆕 Solution 2: Create Dedicated Mike Trainer Account</h3>";
    $trainer_mike_email = 'mike.trainer@l9fitness.com';
    
    $check_trainer_mike = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check_trainer_mike->execute([$trainer_mike_email]);
    $existing_trainer_mike = $check_trainer_mike->fetch();
    
    if (!$existing_trainer_mike) {
        $create_trainer_mike = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password_hash, role_id, google_id, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        
        $password_hash = password_hash('password123', PASSWORD_DEFAULT);
        $create_trainer_mike->execute([
            'Mike',
            'Trainer',
            $trainer_mike_email,
            $password_hash,
            3, // Trainer role
            'fake_mike_trainer_dedicated'
        ]);
        
        $new_id = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Created dedicated Mike Trainer account (ID: {$new_id})</p>";
    } else {
        echo "<p style='color: blue;'>✅ Mike Trainer account already exists!</p>";
    }
    
    // Show all trainer accounts now
    echo "<h3>👨‍🏫 All Trainer Accounts:</h3>";
    $trainers_query = "SELECT * FROM users WHERE role_id = 3 ORDER BY first_name";
    $trainers_stmt = $pdo->prepare($trainers_query);
    $trainers_stmt->execute();
    $trainers = $trainers_stmt->fetchAll();
    
    if (empty($trainers)) {
        echo "<p style='color: red;'>❌ No trainers found!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #e8f5e8;'>";
        echo "<th>ID</th><th>Name</th><th>Email</th><th>Password</th><th>Google ID</th>";
        echo "</tr>";
        
        foreach ($trainers as $trainer) {
            echo "<tr>";
            echo "<td>{$trainer['id']}</td>";
            echo "<td>{$trainer['first_name']} {$trainer['last_name']}</td>";
            echo "<td>{$trainer['email']}</td>";
            echo "<td>password123</td>";
            echo "<td>" . ($trainer['google_id'] ?? 'None') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test the trainer dashboard access
    echo "<h3>🧪 Test Trainer Dashboard Access:</h3>";
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px;'>";
    echo "<h4>Try These Trainer Logins:</h4>";
    
    foreach ($trainers as $trainer) {
        echo "<div style='background: white; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
        echo "<strong>{$trainer['first_name']} {$trainer['last_name']}</strong><br>";
        echo "Email: <code>{$trainer['email']}</code><br>";
        echo "Password: <code>password123</code>";
        echo "</div>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🚀 Test Mike's Trainer Access:</h4>";
echo "<p>";
echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Login Page</a>";
echo "<a href='auth/simple_google_demo.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🎭 Google Demo</a>";
echo "<a href='trainer_dashboard.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👨‍🏫 Trainer Dashboard</a>";
echo "</p>";
echo "<p><small>If you get 'Access Denied', login as Mike with trainer credentials first!</small></p>";
echo "</div>";
?>