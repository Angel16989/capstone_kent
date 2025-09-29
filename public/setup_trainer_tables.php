<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>🔧 Setting Up Trainer Dashboard Tables</h2>";

try {
    // Read the SQL file
    $sql_file = __DIR__ . '/../database/trainer_dashboard_tables.sql';
    $sql = file_get_contents($sql_file);
    
    if (!$sql) {
        throw new Exception("Could not read SQL file");
    }
    
    // Split into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($queries as $query) {
        if (empty($query) || strpos($query, '--') === 0) {
            continue; // Skip empty queries and comments
        }
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $success_count++;
            echo "<p style='color: green;'>✅ Executed: " . substr($query, 0, 50) . "...</p>";
        } catch (Exception $e) {
            $error_count++;
            echo "<p style='color: orange;'>⚠️ Warning: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>📊 Results:</h3>";
    echo "<p><strong>Successful:</strong> $success_count queries</p>";
    echo "<p><strong>Warnings:</strong> $error_count queries</p>";
    
    if ($success_count > 0) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h4 style='color: #155724;'>🎉 Trainer Dashboard Tables Ready!</h4>";
        echo "<p>The database has been set up with all necessary tables for the trainer dashboard functionality.</p>";
        echo "<a href='trainer_dashboard.php' class='btn' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Go to Trainer Dashboard</a>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>