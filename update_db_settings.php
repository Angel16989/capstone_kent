<?php
// update_db_settings.php - Add settings table for admin controls
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Add Settings Table for Admin Controls</h2>";

try {
    require_once __DIR__ . '/config/config.php';
    
    // Create settings table
    $sql = "
    CREATE TABLE IF NOT EXISTS site_settings (
      id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      setting_key VARCHAR(50) NOT NULL UNIQUE,
      setting_value TEXT,
      description TEXT,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($sql);
    echo "✅ Settings table created<br>";
    
    // Insert default settings
    $settings = [
        ['visual_effects', '0', 'Enable visual effects like shake, glitch animations'],
        ['password_strength_check', '1', 'Enable real-time password strength checking'],
        ['matrix_background', '0', 'Enable matrix rain background effect'],
        ['typing_sparks', '0', 'Enable typing spark effects'],
        ['screen_glitch', '0', 'Enable random screen glitch effects'],
        ['shake_animation', '0', 'Enable shake animations on form errors']
    ];
    
    foreach ($settings as $setting) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        $stmt->execute($setting);
        echo "✅ Added setting: {$setting[0]} = {$setting[1]}<br>";
    }
    
    echo "<br><div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<strong>🎉 Settings table created successfully!</strong><br>";
    echo "Admin can now control visual effects from the admin dashboard.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>
