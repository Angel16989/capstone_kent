<?php
// clean_membership_plans.php - Remove duplicate membership plans
require_once __DIR__ . '/config/config.php';

echo "<h2>Cleaning Membership Plans</h2>";

try {
    // First, let's see what we currently have
    $stmt = $pdo->query('SELECT * FROM membership_plans ORDER BY price ASC');
    $current_plans = $stmt->fetchAll();
    
    echo "<h3>Current Plans:</h3>";
    foreach ($current_plans as $plan) {
        echo "ID: {$plan['id']}, Name: {$plan['name']}, Price: \${$plan['price']}, Duration: {$plan['duration_days']} days<br>";
    }
    
    // Clear all existing plans
    $pdo->exec('DELETE FROM membership_plans');
    echo "<br>✅ Cleared all existing plans<br>";
    
    // Insert exactly 3 clean plans
    $clean_plans = [
        ['Monthly Beast', 'Unleash your inner warrior with 30 days of pure domination', 30, 49.00],
        ['Quarterly Savage', 'Transform into an unstoppable force over 90 days of brutal training', 90, 129.00],
        ['Yearly Champion', 'Become the ultimate beast with 365 days of unlimited destruction', 365, 399.00]
    ];
    
    $stmt = $pdo->prepare('INSERT INTO membership_plans (name, description, duration_days, price, is_active) VALUES (?, ?, ?, ?, 1)');
    
    foreach ($clean_plans as $plan) {
        $stmt->execute($plan);
        echo "✅ Added: {$plan[0]} - \${$plan[3]}<br>";
    }
    
    echo "<br><h3>Final Plans:</h3>";
    $stmt = $pdo->query('SELECT * FROM membership_plans ORDER BY price ASC');
    $final_plans = $stmt->fetchAll();
    
    foreach ($final_plans as $plan) {
        echo "ID: {$plan['id']}, Name: {$plan['name']}, Price: \${$plan['price']}, Duration: {$plan['duration_days']} days<br>";
    }
    
    echo "<br><div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<strong>🎉 Membership plans cleaned successfully!</strong><br>";
    echo "Now you have exactly 3 unique membership plans with no duplicates.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>
