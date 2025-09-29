<?php
/**
 * Setup Demo Google Accounts
 * Creates some fake Google users for demonstration
 */

require_once __DIR__ . '/config/config.php';

echo "Setting up Demo Google Accounts...\n";
echo "==================================\n\n";

$demoAccounts = [
    [
        'email' => 'demo@gmail.com',
        'first_name' => 'Demo',
        'last_name' => 'User',
        'role_id' => 4, // member
        'description' => 'Basic demo account'
    ],
    [
        'email' => 'john.doe@gmail.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'role_id' => 4, // member
        'description' => 'Regular member account'
    ],
    [
        'email' => 'sarah.wilson@gmail.com',
        'first_name' => 'Sarah',
        'last_name' => 'Wilson',
        'role_id' => 4, // member
        'description' => 'Female member account'
    ],
    [
        'email' => 'admin.demo@gmail.com',
        'first_name' => 'Admin',
        'last_name' => 'Demo',
        'role_id' => 1, // admin
        'description' => 'Demo admin account via Google'
    ],
    [
        'email' => 'trainer.mike@gmail.com',
        'first_name' => 'Mike',
        'last_name' => 'Trainer',
        'role_id' => 2, // trainer
        'description' => 'Demo trainer account'
    ]
];

try {
    foreach ($demoAccounts as $account) {
        // Check if account already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$account['email']]);
        
        if ($stmt->fetch()) {
            echo "⚠️  Account {$account['email']} already exists - skipping\n";
            continue;
        }
        
        // Create the account
        $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $googleId = 'fake_' . md5($account['email']);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (role_id, first_name, last_name, email, google_id, password_hash, email_verified, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, 'active', NOW(), NOW())
        ");
        
        $stmt->execute([
            $account['role_id'],
            $account['first_name'],
            $account['last_name'],
            $account['email'],
            $googleId,
            $passwordHash
        ]);
        
        $roleNames = [1 => 'Admin', 2 => 'Trainer', 4 => 'Member'];
        $roleName = $roleNames[$account['role_id']] ?? 'User';
        
        echo "✅ Created {$roleName}: {$account['first_name']} {$account['last_name']} ({$account['email']})\n";
    }
    
    echo "\n🎯 DEMO GOOGLE ACCOUNTS READY!\n";
    echo "==============================\n";
    echo "Now when users click 'Continue with Google', they'll see:\n\n";
    
    // Show the accounts that were created
    $stmt = $pdo->query("
        SELECT u.*, 
               CASE u.role_id 
                   WHEN 1 THEN 'Admin' 
                   WHEN 2 THEN 'Trainer' 
                   ELSE 'Member' 
               END as role_name
        FROM users u 
        WHERE google_id LIKE 'fake_%' 
        ORDER BY role_id, first_name
    ");
    
    while ($user = $stmt->fetch()) {
        echo "🔑 {$user['role_name']}: {$user['first_name']} {$user['last_name']} ({$user['email']})\n";
    }
    
    echo "\n📝 HOW IT WORKS:\n";
    echo "================\n";
    echo "1. User clicks 'Continue with Google' on login/register page\n";
    echo "2. They see a list of existing Google accounts (like real Google)\n";
    echo "3. They can click any account for instant login\n";
    echo "4. Or click 'Use another account' to create a new one\n";
    echo "5. New accounts are created and remembered for future logins\n\n";
    
    echo "🚀 Ready to test at: " . BASE_URL . "login.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>