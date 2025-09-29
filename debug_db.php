<?php
// Debug database connection
$DB_HOST = '127.0.0.1';
$DB_NAME = 'l9_gym';
$DB_USER = 'root';
$DB_PASS = '';

echo "Testing connection with:\n";
echo "Host: $DB_HOST\n";
echo "Database: $DB_NAME\n";
echo "User: $DB_USER\n";
echo "Password: " . ($DB_PASS ? "[hidden]" : "[empty]") . "\n\n";

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "MySQL Version: " . $result['version'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
?>
