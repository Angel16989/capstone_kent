<?php
/**
 * Fix Membership Plans - Remove duplicates and set proper pricing
 * PHP 8+ Compatible - L9 Fitness Gym
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

echo "<h2>🔧 Fixing Membership Plans</h2>";

try {
    // Start transaction
    $pdo->beginTransaction();
    
    echo "<p>🗑️ Removing all existing membership plans...</p>";
    
    // Delete all existing membership plans
    $pdo->exec("DELETE FROM membership_plans");
    
    echo "<p>✅ All existing plans removed</p>";
    
    echo "<p>➕ Creating new membership plans with proper pricing...</p>";
    
    // Create exactly 3 membership plans with proper pricing
    $plans = [
        [
            'name' => 'Monthly Beast',
            'description' => 'Perfect for warriors starting their journey. Full gym access with essential training tools.',
            'duration_days' => 30,
            'price' => 49.99,
            'is_active' => 1
        ],
        [
            'name' => 'Quarterly Savage', 
            'description' => 'For serious athletes ready to dominate. Includes premium equipment and guest passes.',
            'duration_days' => 90,
            'price' => 129.99,
            'is_active' => 1
        ],
        [
            'name' => 'Yearly Champion',
            'description' => 'Ultimate beast mode package. Personal training, 24/7 access, and VIP treatment.',
            'duration_days' => 365,
            'price' => 399.99,
            'is_active' => 1
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO membership_plans 
        (name, description, duration_days, price, is_active) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($plans as $plan) {
        $stmt->execute([
            $plan['name'],
            $plan['description'], 
            $plan['duration_days'],
            $plan['price'],
            $plan['is_active']
        ]);
        
        echo "<div style='background: #d4edda; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
        echo "✅ <strong>{$plan['name']}</strong> - \${$plan['price']} for {$plan['duration_days']} days";
        echo "</div>";
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "<div style='background: #d1ecf1; padding: 20px; margin: 20px 0; border-radius: 10px; border-left: 4px solid #0c5460;'>";
    echo "<h3>🎉 Membership Plans Fixed Successfully!</h3>";
    echo "<ul>";
    echo "<li><strong>Monthly Beast:</strong> $49.99 for 30 days</li>";
    echo "<li><strong>Quarterly Savage:</strong> $129.99 for 90 days (Best Value!)</li>"; 
    echo "<li><strong>Yearly Champion:</strong> $399.99 for 365 days (Premium Package!)</li>";
    echo "</ul>";
    echo "</div>";
    
    // Verify the plans
    echo "<h3>📊 Verification - Current Plans:</h3>";
    $stmt = $pdo->query('SELECT * FROM membership_plans WHERE is_active=1 ORDER BY price ASC');
    while($row = $stmt->fetch()) {
        echo "<p>🏋️‍♂️ <strong>{$row['name']}</strong> - \${$row['price']} - {$row['duration_days']} days</p>";
    }
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<div style='text-align: center; margin: 30px 0; padding: 20px; background: linear-gradient(135deg, #FF4444, #FFD700); color: white; border-radius: 10px;'>";
echo "<h2>💪 MEMBERSHIP PLANS UPDATED!</h2>";
echo "<p>Now go check your memberships page - prices should be different!</p>";
echo "<p><a href='public/memberships.php' style='color: white; text-decoration: underline;'>➡️ View Memberships Page</a></p>";
echo "</div>";
?>