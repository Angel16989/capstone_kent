<?php
echo "<h1>🏋️ Check-in System Test</h1>";

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=l9_gym", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Database Connected</h2>";
    
    // Check if member_checkins table exists
    $stmt = $pdo->query("DESCRIBE member_checkins");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>📋 member_checkins table structure:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>✓ $column</li>";
    }
    echo "</ul>";
    
    // Check for recent check-ins
    $stmt = $pdo->query("SELECT COUNT(*) FROM member_checkins");
    $checkin_count = $stmt->fetchColumn();
    echo "<h3>📊 Total check-ins: $checkin_count</h3>";
    
    // Check for active check-ins
    $stmt = $pdo->query("SELECT COUNT(*) FROM member_checkins WHERE checkout_time IS NULL");
    $active_count = $stmt->fetchColumn();
    echo "<h3>🔥 Currently checked in: $active_count members</h3>";
    
    // Test celebrity users
    echo "<h2>🌟 Celebrity Users Ready for Check-in:</h2>";
    $stmt = $pdo->query("SELECT id, first_name, last_name, email FROM users WHERE email LIKE '%@l9fitness.com%' ORDER BY id");
    $celebrities = $stmt->fetchAll();
    
    if (count($celebrities) > 0) {
        echo "<div style='background: #0a0a0a; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        foreach ($celebrities as $user) {
            echo "<p style='font-size: 16px;'>👤 <strong>{$user['first_name']} {$user['last_name']}</strong> - {$user['email']}</p>";
        }
        echo "</div>";
        
        echo "<h2>🚀 Ready to Test!</h2>";
        echo "<div style='background: #004d00; padding: 30px; border-radius: 15px; margin: 20px 0; text-align: center;'>";
        echo "<h3>🔑 Login Steps:</h3>";
        echo "<ol style='text-align: left;'>";
        echo "<li><strong>Go to:</strong> <a href='login.php' style='color: #00ff00;'>login.php</a></li>";
        echo "<li><strong>Email:</strong> mj@l9fitness.com</li>";
        echo "<li><strong>Password:</strong> beast123</li>";
        echo "<li><strong>Go to Dashboard</strong> and click '<strong>Check In</strong>' button</li>";
        echo "</ol>";
        echo "<a href='login.php' style='background: linear-gradient(135deg, #ff4444, #ff6666); color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; font-size: 18px; font-weight: bold; margin: 10px;'>🚀 LOGIN AS MICHAEL JACKSON</a>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ No celebrity users found. Please run the ultimate_dummy_data.php script first!</p>";
        echo "<a href='ultimate_dummy_data.php' style='background: #ff4444; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Create Celebrity Users</a>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: #ff4444; background: #220000; padding: 20px; border-radius: 10px;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>