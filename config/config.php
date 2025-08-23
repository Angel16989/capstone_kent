<?php
define('APP_NAME', 'L9 Fitness Gym');
define('BASE_URL', '/CAPSTONE/public'); // <-- change to your folder name
require_once __DIR__ . '/db.php';
session_start();

// Detect base path automatically (works for localhost & production)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');

// define('BASE_URL', $baseDir . '/'); // e.g. /CAPSTONE/public/