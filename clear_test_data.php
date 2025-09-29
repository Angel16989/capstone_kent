<?php
$pdo = new PDO('mysql:host=localhost;dbname=l9_gym', 'root', '');

echo "Clearing existing test data...\n";

$tables = ['payment_receipts', 'payment_history', 'announcements', 'gym_check_logs', 'user_messages', 'weight_progress', 'user_goals', 'workout_progress'];

foreach ($tables as $table) {
    try {
        $pdo->exec("DELETE FROM $table WHERE 1=1");
        echo "✓ Cleared $table\n";
    } catch (Exception $e) {
        echo "⚠️ $table: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Test data cleared! Now you can run the comprehensive test data script.\n";
?>