<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Capstone Latest - Ngrok Ready!</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            color: white;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(0,0,0,0.2);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .status-card {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid rgba(255,255,255,0.2);
        }
        .success { border-color: #28a745; }
        .warning { border-color: #ffc107; }
        .error { border-color: #dc3545; }
        .links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        .link-btn {
            display: block;
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .link-btn:hover {
            transform: translateY(-2px);
            color: white;
        }
        .ngrok-url {
            background: rgba(0,255,0,0.2);
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 18px;
            margin: 20px 0;
            border: 2px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 L9 FITNESS - CAPSTONE LATEST</h1>
            <h2>Ngrok Demo Ready!</h2>
            <div class="ngrok-url">
                <strong>🌐 Your Public URL:</strong><br>
                <code>https://4ad2d1b6dd70.ngrok-free.app/Capstone-latest/public/</code>
            </div>
        </div>

        <div class="status-grid">
            <?php
            require_once __DIR__ . '/../config/config.php';
            
            // Check database connection  
            echo '<div class="status-card success">';
            echo '<h3>📊 Database Status</h3>';
            try {
                require_once __DIR__ . '/../config/db.php';
                $test = $pdo->query("SELECT COUNT(*) FROM membership_plans WHERE is_active=1")->fetchColumn();
                echo "<p>✅ Connected to database</p>";
                echo "<p>✅ Found {$test} active membership plans</p>";
                
                // Check pricing
                $plans = $pdo->query('SELECT name, price FROM membership_plans WHERE is_active=1 ORDER BY price ASC')->fetchAll();
                foreach ($plans as $plan) {
                    echo "<p>💪 {$plan['name']}: \${$plan['price']}</p>";
                }
                
            } catch (Exception $e) {
                echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
            }
            echo '</div>';
            
            // Config status
            echo '<div class="status-card success">';
            echo '<h3>⚙️ Configuration</h3>';
            echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
            if (defined('ASSET_URL')) {
                echo "<p><strong>ASSET_URL:</strong> " . ASSET_URL . "</p>";
            }
            echo "<p><strong>Cache Busting:</strong> " . (defined('ASSET_VERSION') ? '✅ Enabled' : '❌ Disabled') . "</p>";
            echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
            echo '</div>';
            
            // Environment detection
            echo '<div class="status-card success">';
            echo '<h3>🌍 Environment</h3>';
            echo "<p><strong>Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
            echo "<p><strong>Protocol:</strong> " . (strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false ? 'HTTPS (ngrok)' : 'HTTP (local)') . "</p>";
            echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "</p>";
            echo '</div>';
            ?>
        </div>

        <div class="links">
            <a href="<?php echo BASE_URL; ?>" class="link-btn">🏠 Home Page</a>
            <a href="<?php echo BASE_URL; ?>memberships.php" class="link-btn">💪 Memberships</a>
            <a href="<?php echo BASE_URL; ?>classes.php" class="link-btn">🏃‍♂️ Classes</a>
            <a href="<?php echo BASE_URL; ?>login.php" class="link-btn">🔐 Login</a>
            <a href="<?php echo BASE_URL; ?>register.php" class="link-btn">📝 Register</a>
            <a href="<?php echo BASE_URL; ?>dashboard.php" class="link-btn">📊 Dashboard</a>
        </div>

        <div style="margin-top: 40px; text-align: center; opacity: 0.8;">
            <p><strong>🎯 For Lecturer Demo:</strong></p>
            <p>Share this ngrok URL with your lecturer for instant access!</p>
            <p><code>https://4ad2d1b6dd70.ngrok-free.app/Capstone-latest/public/ngrok_demo.php</code></p>
        </div>
    </div>
</body>
</html>