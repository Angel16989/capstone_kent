<?php
// Quick script to create a test user
require_once __DIR__ . '/config/config.php';

try {
    // Create test user
    $email = 'test@test.com';
    $password = 'password123';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if user already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo "Test user already exists!\n";
        echo "Email: test@test.com\n";
        echo "Password: password123\n";
    } else {
        // Insert test user
        $stmt = $pdo->prepare('INSERT INTO users (role_id, first_name, last_name, email, password_hash, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([4, 'Test', 'User', $email, $password_hash, 'active']); // role_id 4 = member
        
        echo "Test user created successfully!\n";
        echo "Email: test@test.com\n";
        echo "Password: password123\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure MySQL is running and the database 'l9_gym' exists.\n";
}
?>
