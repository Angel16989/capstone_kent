<?php
/**
 * Create Admin Account Script
 * Run this once to create/verify admin accounts
 */

require_once __DIR__ . '/config/config.php';

echo "L9 Fitness Admin Account Manager\n";
echo "================================\n\n";

try {
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name FROM users WHERE email = ?");
    $stmt->execute(['admin@l9.local']);
    $admin = $stmt->fetch();

    if ($admin) {
        echo "✅ Admin account already exists:\n";
        echo "   Email: admin@l9.local\n";
        echo "   Name: {$admin['first_name']} {$admin['last_name']}\n";
        echo "   ID: {$admin['id']}\n\n";
    } else {
        // Create admin account
        echo "⚠️  Creating admin account...\n";
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (role_id, first_name, last_name, email, password_hash, phone, gender, dob, address, status, created_at, updated_at)
            VALUES (1, 'Admin', 'Master', 'admin@l9.local', ?, '555-ADMIN', 'male', '1985-01-01', '123 Admin Street, Muscle City', 'active', NOW(), NOW())
        ");
        
        if ($stmt->execute([$passwordHash])) {
            echo "✅ Admin account created successfully!\n";
            echo "   Email: admin@l9.local\n";
            echo "   Password: password123\n\n";
        } else {
            echo "❌ Failed to create admin account\n\n";
        }
    }

    // Check trainer account
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name FROM users WHERE email = ?");
    $stmt->execute(['trainer@l9.local']);
    $trainer = $stmt->fetch();

    if (!$trainer) {
        echo "⚠️  Creating trainer account...\n";
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (role_id, first_name, last_name, email, password_hash, phone, gender, dob, address, status, created_at, updated_at)
            VALUES (2, 'Trainer', 'Beast', 'trainer@l9.local', ?, '555-TRAIN', 'female', '1990-05-15', '456 Trainer Ave, Fitness City', 'active', NOW(), NOW())
        ");
        
        if ($stmt->execute([$passwordHash])) {
            echo "✅ Trainer account created successfully!\n";
            echo "   Email: trainer@l9.local\n";
            echo "   Password: password123\n\n";
        } else {
            echo "❌ Failed to create trainer account\n\n";
        }
    } else {
        echo "✅ Trainer account already exists:\n";
        echo "   Email: trainer@l9.local\n";
        echo "   Name: {$trainer['first_name']} {$trainer['last_name']}\n";
        echo "   ID: {$trainer['id']}\n\n";
    }

    // Show all admin and trainer accounts
    echo "Current Admin & Trainer Accounts:\n";
    echo "---------------------------------\n";
    $stmt = $pdo->query("SELECT id, role_id, first_name, last_name, email, created_at FROM users WHERE role_id IN (1, 2) ORDER BY role_id, id");
    while ($row = $stmt->fetch()) {
        $role = $row['role_id'] == 1 ? 'Admin' : 'Trainer';
        echo "🔑 {$role}: {$row['first_name']} {$row['last_name']} ({$row['email']})\n";
    }

    echo "\n🎯 LOGIN INSTRUCTIONS:\n";
    echo "=====================\n";
    echo "1. Go to: http://localhost/Capstone-latest/public/login.php\n";
    echo "2. Use any of the accounts above\n";
    echo "3. Default password for all accounts: password123\n";
    echo "4. Admin accounts have full access to admin panel\n";
    echo "5. Trainer accounts have limited access\n\n";
    
    echo "🔧 Google OAuth Status: DISABLED (for development)\n";
    echo "To enable Google OAuth, update the credentials in config/google_config.php\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>