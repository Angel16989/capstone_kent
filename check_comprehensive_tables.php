<?php
$pdo = new PDO('mysql:host=localhost;dbname=l9_gym', 'root', '');
echo "Checking which comprehensive feature tables exist:\n\n";

$tables_to_check = [
    'user_photos', 'user_messages', 'gym_check_logs', 
    'weight_progress', 'user_nutrition_profiles', 'user_goals', 
    'workout_progress', 'user_fitness_profile'
];

foreach ($tables_to_check as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ $table exists\n";
            // Show structure
            $stmt = $pdo->query("DESCRIBE $table");
            echo "  Columns: ";
            $cols = [];
            while ($row = $stmt->fetch()) {
                $cols[] = $row['Field'];
            }
            echo implode(', ', $cols) . "\n\n";
        } else {
            echo "✗ $table missing\n\n";
        }
    } catch (Exception $e) {
        echo "✗ $table error: " . $e->getMessage() . "\n\n";
    }
}
?>