<?php
define('APP_NAME', 'L9 Fitness Gym');
define('BASE_URL', '/capsronenewedits/public/'); // Fixed for local development server
require_once __DIR__ . '/db.php';
session_start();

// Generate CSRF token if not exists - single source of truth
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Detect base path automatically (works for localhost & production)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');

// define('BASE_URL', $baseDir . '/'); // e.g. /CAPSTONE/public/