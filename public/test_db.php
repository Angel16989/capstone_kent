<?php
require_once __DIR__ . '/../config/config.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic connection
    echo "Database connection: OK<br>";
    
    // Check if users table exists
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Users table structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check user_roles table
    $stmt = $pdo->query("SELECT * FROM user_roles");
    $roles = $stmt->fetchAll();
    
    echo "<h3>User Roles:</h3>";
    foreach ($roles as $role) {
        echo "ID: " . $role['id'] . ", Name: " . $role['name'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
