<?php
/**
 * Database Schema Validator
 * Check if database matches the expected schema
 */

require_once __DIR__ . '/config/config.php';

echo "<h2>🔍 Database Schema Validation</h2>";

try {
    // Check if tables exist
    $tables = ['user_roles', 'users', 'membership_plans', 'memberships', 'classes', 'bookings', 'payments', 'trainers'];
    
    echo "<h3>📋 Table Existence Check:</h3>";
    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $result->fetchColumn();
            echo "<p>✅ Table '$table' exists with $count records</p>";
        } catch (Exception $e) {
            echo "<p>❌ Table '$table' missing or error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Check users table structure
    echo "<h3>👤 Users Table Structure:</h3>";
    $columns = $pdo->query("DESCRIBE users")->fetchAll();
    foreach ($columns as $col) {
        echo "<p>• {$col['Field']} ({$col['Type']}) " . ($col['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . "</p>";
    }
    
    // Check for role data
    echo "<h3>🏷️ User Roles:</h3>";
    $roles = $pdo->query("SELECT * FROM user_roles")->fetchAll();
    foreach ($roles as $role) {
        echo "<p>• ID {$role['id']}: {$role['name']}</p>";
    }
    
    // Test creating a user
    echo "<h3>🧪 Test User Creation:</h3>";
    
    // Get member role ID
    $stmt = $pdo->prepare("SELECT id FROM user_roles WHERE name = 'member'");
    $stmt->execute();
    $member_role = $stmt->fetch();
    
    if ($member_role) {
        $role_id = $member_role['id'];
        $test_email = 'schema_test_' . time() . '@l9fitness.com';
        
        $stmt = $pdo->prepare("INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $role_id,
            'Schema',
            'Test',
            $test_email,
            password_hash('testpass123', PASSWORD_DEFAULT)
        ]);
        
        if ($result) {
            $user_id = $pdo->lastInsertId();
            echo "<p>✅ Successfully created test user with ID: $user_id</p>";
            
            // Clean up
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
            echo "<p>✅ Test user cleaned up</p>";
        } else {
            echo "<p>❌ Failed to create test user</p>";
        }
    } else {
        echo "<p>❌ Member role not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Schema Validation Complete</h4>";
echo "<p>If all tables show ✅ and test user creation succeeded, your database schema is correct.</p>";
echo "</div>";
?>