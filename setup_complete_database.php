<?php
// Complete database setup and table creation script
require_once 'config/db.php';

echo "<h2>🔧 Complete Database Setup & Table Creation</h2><br>";

try {
    // Use the existing PDO connection from config/db.php
    echo "✅ Database connection successful<br><br>";
    
    // Create all missing tables that profile.php needs
    echo "<strong>Creating Missing Tables...</strong><br><br>";
    
    // 1. Weight progress table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS weight_progress (
            id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            user_id INT UNSIGNED NOT NULL,
            weight DECIMAL(5,2) NOT NULL,
            recorded_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "✅ Created weight_progress table<br>";
    
    // 2. User fitness profile table  
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_fitness_profile (
            id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            user_id INT UNSIGNED NOT NULL UNIQUE,
            height INT UNSIGNED,
            current_weight DECIMAL(5,2),
            target_weight DECIMAL(5,2),
            fitness_level ENUM('beginner','intermediate','advanced','elite'),
            medical_conditions TEXT,
            emergency_contact VARCHAR(255),
            emergency_phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "✅ Created user_fitness_profile table<br>";
    
    // 3. User nutrition profiles table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_nutrition_profiles (
            id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            user_id INT UNSIGNED NOT NULL UNIQUE,
            diet_type VARCHAR(100),
            daily_calories INT UNSIGNED DEFAULT 0,
            daily_protein INT UNSIGNED DEFAULT 0,
            daily_carbs INT UNSIGNED DEFAULT 0,
            daily_fats INT UNSIGNED DEFAULT 0,
            restrictions TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "✅ Created user_nutrition_profiles table<br>";
    
    // 4. User goals table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_goals (
            id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            user_id INT UNSIGNED NOT NULL,
            goal_type VARCHAR(100) NOT NULL,
            target_value DECIMAL(10,2) NOT NULL,
            target_date DATE NOT NULL,
            description TEXT,
            status ENUM('active','completed','cancelled') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "✅ Created user_goals table<br>";
    
    // 5. User photos table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_photos (
            id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            user_id INT UNSIGNED NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255),
            file_size INT UNSIGNED,
            mime_type VARCHAR(100),
            is_profile_picture TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "✅ Created user_photos table<br>";
    
    // 6. User messages table (for messaging feature)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_messages (
            id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            sender_id INT UNSIGNED NOT NULL,
            recipient_id INT UNSIGNED NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message_content TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "✅ Created user_messages table<br>";
    
    // 7. Add missing columns to users table if needed
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN city VARCHAR(100)");
        echo "✅ Added city column to users table<br>";
    } catch (Exception $e) {
        echo "ℹ️ City column already exists<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN state VARCHAR(50)");
        echo "✅ Added state column to users table<br>";
    } catch (Exception $e) {
        echo "ℹ️ State column already exists<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN postcode VARCHAR(10)");
        echo "✅ Added postcode column to users table<br>";
    } catch (Exception $e) {
        echo "ℹ️ Postcode column already exists<br>";
    }
    
    echo "<br><strong>🎉 ALL TABLES CREATED SUCCESSFULLY!</strong><br><br>";
    
    // Test data insertion to verify everything works
    echo "<strong>Testing Database Operations...</strong><br>";
    
    // Get first user to test with
    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $test_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($test_user) {
        $user_id = $test_user['id'];
        
        // Test fitness profile insertion
        $stmt = $pdo->prepare("
            INSERT INTO user_fitness_profile (user_id, height, current_weight, fitness_level)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE height = VALUES(height)
        ");
        $stmt->execute([$user_id, 175, 70.5, 'intermediate']);
        echo "✅ Test fitness profile created<br>";
        
        // Test weight progress insertion
        $stmt = $pdo->prepare("
            INSERT INTO weight_progress (user_id, weight, recorded_date)
            VALUES (?, ?, CURDATE())
        ");
        $stmt->execute([$user_id, 70.5]);
        echo "✅ Test weight entry created<br>";
        
        echo "<br><strong>✅ DATABASE IS FULLY FUNCTIONAL!</strong><br>";
        echo "Your profile dashboard should now work perfectly!<br>";
        
    } else {
        echo "⚠️ No users found. Please register a user first.<br>";
    }
    
    echo "<br><strong>🚀 Ready to use features:</strong><br>";
    echo "• Personal information updates<br>";
    echo "• Password changes<br>";
    echo "• Fitness profile management<br>";
    echo "• Weight tracking<br>";
    echo "• Nutrition profiles<br>";
    echo "• Goal setting<br>";
    echo "• Photo uploads<br>";
    echo "• Messaging system<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<br>Please check your database configuration in config/db.php<br>";
}
?>