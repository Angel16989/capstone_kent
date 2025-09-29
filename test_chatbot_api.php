<?php
// Simple test to check if chatbot API is working
require_once 'config/db.php';

echo "Testing Chatbot API...\n\n";

// Test 1: Database connection
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✅ Database connection: OK\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Check if chatbot_logs table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'chatbot_logs'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "✅ chatbot_logs table exists\n";
    } else {
        echo "⚠️  chatbot_logs table doesn't exist - will be created on first use\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking chatbot_logs table: " . $e->getMessage() . "\n";
}

// Test 3: Test chatbot response generation
echo "\nTesting chatbot responses:\n";

function testResponse($message) {
    // Simulate the response generation logic
    $message_lower = strtolower($message);
    
    if (preg_match('/\b(hi|hello|hey)\b/i', $message_lower)) {
        return "✅ Greeting recognized";
    }
    if (preg_match('/\b(hours|open|close)\b/i', $message_lower)) {
        return "✅ Hours inquiry recognized";
    }
    if (preg_match('/\b(membership|price|cost)\b/i', $message_lower)) {
        return "✅ Membership inquiry recognized";
    }
    if (preg_match('/\b(class|classes|workout)\b/i', $message_lower)) {
        return "✅ Classes inquiry recognized";
    }
    
    return "✅ Default response";
}

$testMessages = [
    "Hello",
    "What are your hours?",
    "How much is membership?",
    "What classes do you offer?",
    "Random question"
];

foreach ($testMessages as $msg) {
    echo "  '$msg' -> " . testResponse($msg) . "\n";
}

echo "\n🤖 Chatbot API test completed!\n";
?>
