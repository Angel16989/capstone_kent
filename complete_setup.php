<?php
/**
 * Setup Script for L9 Fitness Gym
 * Configures Google OAuth and PayPal credentials
 * PHP 8+ Compatible
 */

declare(strict_types=1);

echo "<h1>L9 Fitness Gym - Complete Setup</h1>";
echo "<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; margin: 40px; background: #f5f5f5; }
    .section { background: white; padding: 30px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; }
    .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545; }
    .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; }
    .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    .highlight { color: #007bff; font-weight: bold; }
    .step { margin: 20px 0; padding: 20px; background: #f8f9fa; border-left: 4px solid #007bff; }
    h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    h3 { color: #555; }
</style>";

try {
    // Check if we can include config
    require_once __DIR__ . '/config/config.php';
    echo "<div class='success'>✅ Database connection successful!</div>";
    
    // Check database tables
    echo "<div class='section'>";
    echo "<h2>🗄️ Database Status</h2>";
    
    $tables = [
        'user_roles' => 'User roles (admin, member, etc.)',
        'users' => 'User accounts',
        'user_profiles' => 'Extended user profiles (for OAuth)',
        'oauth_tokens' => 'OAuth token storage',
        'login_history' => 'Login tracking',
        'membership_plans' => 'Membership plans',
        'memberships' => 'User memberships',
        'payments' => 'Payment records'
    ];
    
    foreach ($tables as $table => $description) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $count = $stmt->fetchColumn();
            echo "<div class='success'>✅ {$table}: {$count} records - {$description}</div>";
        } catch (PDOException $e) {
            echo "<div class='error'>❌ {$table}: Missing or error - {$description}</div>";
        }
    }
    echo "</div>";
    
    // Configuration Instructions
    echo "<div class='section'>";
    echo "<h2>⚙️ Configuration Required</h2>";
    
    echo "<div class='step'>";
    echo "<h3>1. Google OAuth Setup</h3>";
    echo "<p>To enable Google login, you need to:</p>";
    echo "<ol>";
    echo "<li>Go to <a href='https://console.developers.google.com/' target='_blank'>Google Cloud Console</a></li>";
    echo "<li>Create a new project or select existing one</li>";
    echo "<li>Enable Google+ API</li>";
    echo "<li>Create OAuth 2.0 credentials</li>";
    echo "<li>Set authorized redirect URI to: <span class='highlight'>" . BASE_URL . "auth/google_callback.php</span></li>";
    echo "</ol>";
    echo "<p>Then update these values in <strong>config/google_config.php</strong>:</p>";
    echo "<div class='code'>
define('GOOGLE_CLIENT_ID', 'your-google-client-id.googleusercontent.com');<br>
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');
</div>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>2. PayPal Integration Setup</h3>";
    echo "<p>To enable PayPal payments:</p>";
    echo "<ol>";
    echo "<li>Go to <a href='https://developer.paypal.com/' target='_blank'>PayPal Developer</a></li>";
    echo "<li>Create a new app in Sandbox (for testing)</li>";
    echo "<li>Get your Client ID and Client Secret</li>";
    echo "<li>For production, create a live app</li>";
    echo "</ol>";
    echo "<p>Update these values in <strong>config/paypal_config.php</strong>:</p>";
    echo "<div class='code'>
define('PAYPAL_CLIENT_ID', 'your-sandbox-client-id');<br>
define('PAYPAL_CLIENT_SECRET', 'your-sandbox-client-secret');
</div>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>3. Test the Website</h3>";
    echo "<p>Once configured, test these features:</p>";
    echo "<ul>";
    echo "<li><a href='" . BASE_URL . "' target='_blank'>🏠 Homepage</a></li>";
    echo "<li><a href='" . BASE_URL . "register.php' target='_blank'>📝 Registration</a></li>";
    echo "<li><a href='" . BASE_URL . "login.php' target='_blank'>🔐 Login (with Google OAuth)</a></li>";
    echo "<li><a href='" . BASE_URL . "memberships.php' target='_blank'>💪 Memberships</a></li>";
    echo "<li><a href='" . BASE_URL . "checkout.php?plan_id=1' target='_blank'>💳 Checkout (PayPal)</a></li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
    // Features Overview
    echo "<div class='section'>";
    echo "<h2>🚀 Features Implemented</h2>";
    echo "<div class='success'>";
    echo "<h3>✅ Authentication & Security</h3>";
    echo "<ul>";
    echo "<li>PHP 8+ compatible code with strict typing</li>";
    echo "<li>Google OAuth 2.0 integration</li>";
    echo "<li>CSRF protection</li>";
    echo "<li>Secure password hashing</li>";
    echo "<li>Session management</li>";
    echo "<li>Login history tracking</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='success'>";
    echo "<h3>✅ Payment Processing</h3>";
    echo "<ul>";
    echo "<li>PayPal integration for secure payments</li>";
    echo "<li>Multiple membership plans</li>";
    echo "<li>Automatic membership activation</li>";
    echo "<li>Invoice generation</li>";
    echo "<li>Payment history</li>";
    echo "<li>Membership extension for existing users</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='success'>";
    echo "<h3>✅ User Experience</h3>";
    echo "<ul>";
    echo "<li>Responsive modern design</li>";
    echo "<li>W3C compliant HTML</li>";
    echo "<li>Enhanced checkout process</li>";
    echo "<li>Progress indicators</li>";
    echo "<li>Form validation</li>";
    echo "<li>Success animations</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
    // Admin Setup
    echo "<div class='section'>";
    echo "<h2>👑 Admin Account</h2>";
    echo "<p>Create an admin account to manage the system:</p>";
    echo "<div class='code'>";
    echo "<a href='create_admin.php' class='highlight'>➡️ Create Admin Account</a>";
    echo "</div>";
    echo "</div>";
    
    // Security Notes
    echo "<div class='section'>";
    echo "<h2>🔒 Security Recommendations</h2>";
    echo "<div class='warning'>";
    echo "<ul>";
    echo "<li>Change default database credentials in production</li>";
    echo "<li>Use HTTPS in production environment</li>";
    echo "<li>Set up regular database backups</li>";
    echo "<li>Monitor error logs regularly</li>";
    echo "<li>Keep PHP and dependencies updated</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>📞 Support</h2>";
    echo "<p>Your L9 Fitness Gym website is ready! If you need help:</p>";
    echo "<ul>";
    echo "<li>Check the error logs in <code>xampp/apache/logs/</code></li>";
    echo "<li>Review configuration files in <code>config/</code></li>";
    echo "<li>Test features step by step</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Setup Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>🔧 Troubleshooting</h2>";
    echo "<p>If you see database connection errors:</p>";
    echo "<ol>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Check database credentials in <code>config/db.php</code></li>";
    echo "<li>Run the database setup: <code>php setup_db.php</code></li>";
    echo "<li>Import the schema: <code>database/schema.sql</code></li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div style='text-align: center; margin: 40px 0; padding: 20px; background: linear-gradient(135deg, #FF4444, #FFD700); color: white; border-radius: 10px;'>";
echo "<h2>🏋️‍♂️ L9 FITNESS GYM IS READY! 🏋️‍♀️</h2>";
echo "<p>Beast Mode: ACTIVATED | No Excuses: ENABLED | Limits: DESTROYED</p>";
echo "</div>";
?>