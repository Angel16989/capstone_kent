<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=l9_gym', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if comprehensive features tables exist
    $tables = [
        'user_photos', 'announcements', 'payment_receipts', 'gym_check_logs',
        'trainer_attendance', 'user_messages', 'trainer_class_updates',
        'waitlist_notifications', 'user_preferences'
    ];
    
    echo "Checking comprehensive features database tables:\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' missing\n";
        }
    }
    
    // Get user count for testing
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "\nTotal users: $userCount\n";
    
    // Check if we have test data
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM classes');
    $classCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Total classes: $classCount\n";
    
    echo "\nComprehensive L9 Fitness system ready!\n";
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>