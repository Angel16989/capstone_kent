<?php
require_once __DIR__ . '/config/config.php';

echo "Admin and Trainer Accounts:\n";
echo "===========================\n\n";

try {
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, role, created_at FROM users WHERE role IN ('admin', 'trainer') ORDER BY role, id");
    while ($row = $stmt->fetch()) {
        echo "Role: {$row['role']}\n";
        echo "Name: {$row['first_name']} {$row['last_name']}\n";
        echo "Email: {$row['email']}\n";
        echo "ID: {$row['id']}\n";
        echo "Created: {$row['created_at']}\n";
        echo "------------------------\n";
    }
    
    echo "\nAll users for reference:\n";
    echo "========================\n";
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, role FROM users ORDER BY role, id LIMIT 10");
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']}, Role: {$row['role']}, Email: {$row['email']}, Name: {$row['first_name']} {$row['last_name']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>