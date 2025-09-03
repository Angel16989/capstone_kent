<?php
// setup_db.php - Database setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Setup</h2>";

try {
    // Connect to MySQL without specifying database
    $dsn = "mysql:host=127.0.0.1;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "✅ Connected to MySQL<br>";
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    if ($schema === false) {
        throw new Exception('Could not read schema.sql file');
    }
    
    echo "✅ Schema file loaded<br>";
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    echo "✅ Found " . count($statements) . " SQL statements<br>";
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
            try {
                $pdo->exec($statement);
                if (preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches)) {
                    echo "&nbsp;&nbsp;✅ Created table: {$matches[1]}<br>";
                } elseif (preg_match('/INSERT INTO\s+(\w+)/i', $statement, $matches)) {
                    echo "&nbsp;&nbsp;✅ Inserted data into: {$matches[1]}<br>";
                } elseif (preg_match('/CREATE DATABASE/i', $statement)) {
                    echo "&nbsp;&nbsp;✅ Created database<br>";
                } elseif (preg_match('/USE\s+(\w+)/i', $statement, $matches)) {
                    echo "&nbsp;&nbsp;✅ Using database: {$matches[1]}<br>";
                }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "&nbsp;&nbsp;⚠️ Warning: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    echo "<br><h3>Database Status Check</h3>";
    
    // Switch to our database
    $pdo->exec('USE l9_gym');
    
    // Check tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✅ Found " . count($tables) . " tables:<br>";
    foreach ($tables as $table) {
        echo "&nbsp;&nbsp;- {$table}<br>";
    }
    
    // Check user roles
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM user_roles');
    $count = $stmt->fetchColumn();
    echo "<br>✅ User roles table has {$count} records<br>";
    
    if ($count > 0) {
        $stmt = $pdo->query('SELECT * FROM user_roles');
        $roles = $stmt->fetchAll();
        foreach ($roles as $role) {
            echo "&nbsp;&nbsp;- ID: {$role['id']}, Name: {$role['name']}<br>";
        }
    }
    
    echo "<br><div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<strong>🎉 Database setup completed successfully!</strong><br>";
    echo "You can now test registration at: <a href='test_register.php'>test_register.php</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>
