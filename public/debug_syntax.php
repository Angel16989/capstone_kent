<?php
// Simple test to debug the index.php error
echo "Testing PHP syntax...\n";

// Test the config include
try {
    require_once __DIR__ . '/../config/config.php';
    echo "✅ config.php loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "\n";
}

// Test the header include
try {
    ob_start();
    include __DIR__ . '/../app/views/layouts/header.php';
    $headerContent = ob_get_clean();
    echo "✅ header.php loaded successfully\n";
    echo "Header length: " . strlen($headerContent) . " characters\n";
} catch (Exception $e) {
    echo "❌ Header error: " . $e->getMessage() . "\n";
}

echo "Testing complete.\n";
?>