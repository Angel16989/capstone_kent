<?php
// Prevent caching for ngrok compatibility
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

define('APP_NAME', 'L9 Fitness Gym');

// Auto-detect BASE_URL for universal compatibility
if (php_sapi_name() === 'cli') {
    // Running from command line - use default for testing
    define('BASE_URL', '/Capstone-latest/public/');
} else {
    // Running from web server - auto-detect
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Special handling for ngrok
    if (strpos($host, 'ngrok') !== false) {
        // For ngrok, use full URL with protocol and host
        define('BASE_URL', $protocol . $host . '/Capstone-latest/public/');
        define('ASSET_URL', $protocol . $host . '/Capstone-latest/public/assets/');
    } else {
        // Local development - use relative paths
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // Get the directory path and clean it up
        $baseDir = dirname($script);
        $baseDir = str_replace('\\', '/', $baseDir);
        
        // If we're in a subdirectory of public, go up one level
        if (basename($baseDir) === 'public') {
            $baseDir = dirname($baseDir) . '/public';
        } else {
            // We're probably in the project root, add /public
            $baseDir = $baseDir . '/public';
        }
        
        // Clean up the path
        $baseDir = '/' . trim($baseDir, '/') . '/';
        define('BASE_URL', $baseDir);
        define('ASSET_URL', $baseDir . 'assets/');
    }
}

// Cache busting for assets
define('ASSET_VERSION', time());

require_once __DIR__ . '/db.php';

// Include additional configurations
if (file_exists(__DIR__ . '/google_config.php')) {
    require_once __DIR__ . '/google_config.php';
}

if (file_exists(__DIR__ . '/paypal_config.php')) {
    require_once __DIR__ . '/paypal_config.php';
}

// Initialize secure session management
require_once __DIR__ . '/../app/helpers/session.php';
SessionManager::initialize();

// Generate CSRF token if not exists - single source of truth
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
