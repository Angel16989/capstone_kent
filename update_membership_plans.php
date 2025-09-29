<?php
/**
 * Update Membership Plans - Fix pricing without deleting
 * PHP 8+ Compatible - L9 Fitness Gym
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

echo "<h2>🔧 Updating Membership Plans Pricing</h2>";

try {
    // Start transaction
    $pdo->beginTransaction();
    
    echo "<p>📊 Current plans in database:</p>";
    $stmt = $pdo->query('SELECT id, name, price, duration_days FROM membership_plans ORDER BY price ASC');
    $existingPlans = $stmt->fetchAll();
    
    foreach ($existingPlans as $plan) {
        echo "<p>ID: {$plan['id']} - {$plan['name']} - \${$plan['price']} - {$plan['duration_days']} days</p>";
    }
    
    echo "<hr><p>🔄 Updating to correct pricing...</p>";
    
    // First, deactivate all existing plans
    $pdo->exec("UPDATE membership_plans SET is_active = 0");
    
    // Update or create the 3 main plans we want
    $targetPlans = [
        [
            'name' => 'Monthly Beast',
            'description' => 'Perfect for warriors starting their journey. Full gym access with essential training tools.',
            'duration_days' => 30,
            'price' => 49.99
        ],
        [
            'name' => 'Quarterly Savage', 
            'description' => 'For serious athletes ready to dominate. Includes premium equipment and guest passes.',
            'duration_days' => 90,
            'price' => 129.99
        ],
        [
            'name' => 'Yearly Champion',
            'description' => 'Ultimate beast mode package. Personal training, 24/7 access, and VIP treatment.',
            'duration_days' => 365,
            'price' => 399.99
        ]
    ];
    
    foreach ($targetPlans as $index => $targetPlan) {
        // Try to find an existing plan to update
        $stmt = $pdo->prepare("
            SELECT id FROM membership_plans 
            WHERE duration_days = ? 
            ORDER BY id ASC 
            LIMIT 1
        ");
        $stmt->execute([$targetPlan['duration_days']]);
        $existingId = $stmt->fetchColumn();
        
        if ($existingId) {
            // Update existing plan
            $stmt = $pdo->prepare("
                UPDATE membership_plans 
                SET name = ?, description = ?, price = ?, is_active = 1
                WHERE id = ?
            ");
            $stmt->execute([
                $targetPlan['name'],
                $targetPlan['description'],
                $targetPlan['price'],
                $existingId
            ]);
            
            echo "<div style='background: #d4edda; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
            echo "✅ Updated <strong>{$targetPlan['name']}</strong> (ID: {$existingId}) - \${$targetPlan['price']} for {$targetPlan['duration_days']} days";
            echo "</div>";
        } else {
            // Create new plan
            $stmt = $pdo->prepare("
                INSERT INTO membership_plans 
                (name, description, duration_days, price, is_active) 
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $targetPlan['name'],
                $targetPlan['description'],
                $targetPlan['duration_days'],
                $targetPlan['price']
            ]);
            
            $newId = $pdo->lastInsertId();
            echo "<div style='background: #d1ecf1; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
            echo "➕ Created <strong>{$targetPlan['name']}</strong> (ID: {$newId}) - \${$targetPlan['price']} for {$targetPlan['duration_days']} days";
            echo "</div>";
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "<div style='background: #d1ecf1; padding: 20px; margin: 20px 0; border-radius: 10px; border-left: 4px solid #0c5460;'>";
    echo "<h3>🎉 Membership Plans Updated Successfully!</h3>";
    echo "<ul>";
    echo "<li><strong>Monthly Beast:</strong> $49.99 for 30 days</li>";
    echo "<li><strong>Quarterly Savage:</strong> $129.99 for 90 days (Best Value!)</li>"; 
    echo "<li><strong>Yearly Champion:</strong> $399.99 for 365 days (Premium Package!)</li>";
    echo "</ul>";
    echo "</div>";
    
    // Verify the active plans
    echo "<h3>📊 Active Plans Now:</h3>";
    $stmt = $pdo->query('SELECT * FROM membership_plans WHERE is_active=1 ORDER BY price ASC');
    while($row = $stmt->fetch()) {
        echo "<p>🏋️‍♂️ <strong>{$row['name']}</strong> - \${$row['price']} - {$row['duration_days']} days</p>";
    }
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<div style='text-align: center; margin: 30px 0; padding: 20px; background: linear-gradient(135deg, #FF4444, #FFD700); color: white; border-radius: 10px;'>";
echo "<h2>💪 MEMBERSHIP PLANS UPDATED!</h2>";
echo "<p>Prices are now different for each plan!</p>";
echo "<p><a href='public/memberships.php' style='color: white; text-decoration: underline;'>➡️ View Memberships Page</a></p>";
echo "</div>";
?>