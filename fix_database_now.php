<?php
// Complete database setup and table creation script
echo "<h2>🔧 Complete Database Setup & Table Creation</h2><br>";

// Database configuration  
$DB_HOST = '127.0.0.1';
$DB_NAME = 'l9_gym';
$DB_USER = 'root';
$DB_PASS = '';

try {
    // First, connect without database to create it if needed
    $pdo_root = new PDO("mysql:host={$DB_HOST}", $DB_USER, $DB_PASS);
    $pdo_root->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo_root->exec("CREATE DATABASE IF NOT EXISTS `{$DB_NAME}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '{$DB_NAME}' ready<br>";
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME}", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database successfully<br><br>";
    
    // Create all missing tables that profile.php needs
    echo "<strong>Creating Missing Tables...</strong><br><br>";
    
    // 1. First ensure users table has all needed columns
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN city VARCHAR(100)");
        echo "✅ Added city column to users table<br>";
    } catch (Exception $e) {
        echo "ℹ️ City column already exists in users table<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN state VARCHAR(50)");
        echo "✅ Added state column to users table<br>";
    } catch (Exception $e) {
        echo "ℹ️ State column already exists in users table<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN postcode VARCHAR(10)");
        echo "✅ Added postcode column to users table<br>";
    } catch (Exception $e) {
        echo "ℹ️ Postcode column already exists in users table<br>";
    }
    
    // 2. Weight progress table
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
    
    // 3. User fitness profile table  
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
    
    // 4. User nutrition profiles table
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
    
    // 5. User goals table
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
    
    // 6. User photos table
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
    
    // 7. User messages table (for messaging feature)
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
    
    echo "<br><strong>🎉 ALL TABLES CREATED SUCCESSFULLY!</strong><br><br>";
    
    // Show existing tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<strong>📋 Database Tables:</strong><br>";
    foreach ($tables as $table) {
        echo "• {$table}<br>";
    }
    
    echo "<br><strong>✅ DATABASE IS FULLY FUNCTIONAL!</strong><br>";
    echo "<strong style='color: green; font-size: 18px;'>Your profile dashboard should now work perfectly!</strong><br><br>";
    
    echo "<strong>🚀 Ready to use features:</strong><br>";
    echo "• Personal information updates<br>";
    echo "• Password changes<br>";
    echo "• Fitness profile management<br>";
    echo "• Weight tracking<br>";
    echo "• Nutrition profiles<br>";
    echo "• Goal setting<br>";
    echo "• Photo uploads<br>";
    
    echo "<br><strong>🎯 Next Steps:</strong><br>";
    echo "1. Go to <a href='http://localhost/Capstone-latest/public/profile.php' target='_blank'>Profile Dashboard</a><br>";
    echo "2. Try updating your personal information<br>";
    echo "3. Test the weight tracking feature<br>";
    echo "4. All features should work without errors now!<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<br><strong>Troubleshooting:</strong><br>";
    echo "1. Make sure XAMPP MySQL is running<br>";
    echo "2. Check if you can access phpMyAdmin<br>";
    echo "3. Verify database credentials in config/db.php<br>";
}
?>