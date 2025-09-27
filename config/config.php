<?php
define('APP_NAME', 'L9 Fitness Gym');

// Auto-detect BASE_URL for universal compatibility
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');

// Remove '/public' from the end if it exists, then add it back
$baseDir = rtrim($baseDir, '/public');
define('BASE_URL', $baseDir . '/public/');

require_once __DIR__ . '/db.php';
session_start();

// Generate CSRF token if not exists - single source of truth
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
