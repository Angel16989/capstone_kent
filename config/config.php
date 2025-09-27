<?php
define('APP_NAME', 'L9 Fitness Gym');

// Auto-detect BASE_URL for universal compatibility
if (php_sapi_name() === 'cli') {
    // Running from command line - use default for testing
    define('BASE_URL', '/Capstone-latest/public/');
} else {
    // Running from web server - auto-detect
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
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
}

require_once __DIR__ . '/db.php';
session_start();

// Generate CSRF token if not exists - single source of truth
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
