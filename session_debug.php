<?php
// session_debug.php - Check session configuration
session_start();

echo "<h1>🔧 Session Debug Info</h1>";

echo "<h3>Session Configuration:</h3>";
echo "<ul>";
echo "<li><strong>Session ID:</strong> " . session_id() . "</li>";
echo "<li><strong>Session Status:</strong> " . session_status() . " (1=disabled, 2=enabled)</li>";
echo "<li><strong>Session Name:</strong> " . session_name() . "</li>";
echo "<li><strong>Session Save Path:</strong> " . session_save_path() . "</li>";
echo "<li><strong>Session Cookie Params:</strong></li>";
$params = session_get_cookie_params();
foreach ($params as $key => $value) {
    echo "<ul><li>{$key}: {$value}</li></ul>";
}
echo "</ul>";

echo "<h3>Current Session Data:</h3>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h3>PHP Info:</h3>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>Error Reporting:</strong> " . error_reporting() . "</li>";
echo "<li><strong>Display Errors:</strong> " . ini_get('display_errors') . "</li>";
echo "</ul>";

echo "<h3>Test Session Write:</h3>";
$_SESSION['test'] = 'This is a test value - ' . date('Y-m-d H:i:s');
echo "<p>✅ Set session test value: " . $_SESSION['test'] . "</p>";

// Generate and store CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo "<p>✅ Generated new CSRF token</p>";
} else {
    echo "<p>✅ CSRF token already exists</p>";
}

echo "<p><strong>CSRF Token:</strong> " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "</p>";

echo "<h3>Test Database Connection:</h3>";
try {
    require_once __DIR__ . '/config/config.php';
    echo "<p>✅ Config loaded successfully</p>";
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $count = $stmt->fetchColumn();
    echo "<p>✅ Database connected - found {$count} users</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<style>body { font-family: 'Courier New', monospace; background: #1a1a1a; color: white; padding: 20px; }</style>";
?>
