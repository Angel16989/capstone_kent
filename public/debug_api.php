<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting debug...\n";

try {
    echo "1. Loading database config...\n";
    require_once '../config/db.php';
    echo "Database connected successfully!\n";
    
    echo "2. Loading auth helper...\n";
    require_once '../app/helpers/auth.php';
    echo "Auth helper loaded!\n";
    
    echo "3. Loading AI Service...\n";
    require_once '../app/services/AIService.php';
    echo "AI Service loaded!\n";
    
    echo "4. Testing AI response...\n";
    $response = AIService::generateResponse("What are your gym hours?", null);
    echo "AI Response: " . $response . "\n";
    
    echo "5. Testing JSON encoding...\n";
    $json = json_encode(['response' => $response], JSON_UNESCAPED_UNICODE);
    echo "JSON: " . $json . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
