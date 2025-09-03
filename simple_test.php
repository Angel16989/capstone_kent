<?php
echo "Simple PHP test\n";
require_once __DIR__ . '/config/config.php';
echo "Config loaded\n";
echo "CSRF token exists: " . (isset($_SESSION['csrf_token']) ? 'YES' : 'NO') . "\n";
?>
