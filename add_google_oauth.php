<?php
// add_google_oauth.php - Add Google OAuth support to database
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Add Google OAuth Support</h2>";

try {
    require_once __DIR__ . '/config/config.php';
    
    // Add google_id column to users table
    $sql = "ALTER TABLE users ADD COLUMN google_id VARCHAR(100) NULL UNIQUE AFTER email";
    
    try {
        $pdo->exec($sql);
        echo "✅ Added google_id column to users table<br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✅ google_id column already exists<br>";
        } else {
            throw $e;
        }
    }
    
    // Create user_profiles table for extended profile data
    $sql = "
    CREATE TABLE IF NOT EXISTS user_profiles (
        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        bio TEXT,
        fitness_goals TEXT,
        medical_conditions TEXT,
        preferences JSON,
        avatar_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY (user_id)
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($sql);
    echo "✅ Created user_profiles table<br>";
    
    // Create oauth_tokens table for storing OAuth tokens
    $sql = "
    CREATE TABLE IF NOT EXISTS oauth_tokens (
        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        provider VARCHAR(50) NOT NULL,
        provider_user_id VARCHAR(100) NOT NULL,
        access_token TEXT,
        refresh_token TEXT,
        expires_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_provider_user (provider, provider_user_id)
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($sql);
    echo "✅ Created oauth_tokens table<br>";
    
    // Create login_history table for tracking logins
    $sql = "
    CREATE TABLE IF NOT EXISTS login_history (
        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        login_method ENUM('password', 'google', 'facebook') DEFAULT 'password',
        ip_address VARCHAR(45),
        user_agent TEXT,
        success BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_date (user_id, created_at),
        INDEX idx_method (login_method)
    ) ENGINE=InnoDB;
    ";
    
    $pdo->exec($sql);
    echo "✅ Created login_history table<br>";
    
    echo "<br><div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<strong>🎉 Google OAuth database setup completed!</strong><br>";
    echo "✅ google_id column added to users table<br>";
    echo "✅ user_profiles table created for extended profiles<br>";
    echo "✅ oauth_tokens table created for OAuth token storage<br>";
    echo "✅ login_history table created for login tracking<br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>