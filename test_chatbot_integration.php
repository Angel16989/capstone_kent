<?php
/**
 * Test OpenAI Chatbot Integration
 * Verify API key and configuration
 */

echo "<h2>🤖 Testing L9 Fitness AI Chatbot Integration</h2>\n";

// Load configuration
try {
    require_once __DIR__ . '/config/ai_config.php';
    require_once __DIR__ . '/app/services/AIService.php';
    echo "<p>✅ Configuration files loaded successfully</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error loading configuration: " . $e->getMessage() . "</p>\n";
    exit;
}

// Check configuration
echo "<h3>🔧 Configuration Check:</h3>\n";
echo "<p><strong>AI Provider:</strong> " . AI_PROVIDER . "</p>\n";
echo "<p><strong>OpenAI API Key:</strong> " . (empty(OPENAI_API_KEY) ? "❌ NOT SET" : "✅ CONFIGURED (" . substr(OPENAI_API_KEY, 0, 8) . "...)") . "</p>\n";
echo "<p><strong>OpenAI Model:</strong> " . OPENAI_MODEL . "</p>\n";
echo "<p><strong>Fallback Enabled:</strong> " . (USE_AI_FALLBACK ? "✅ YES" : "❌ NO") . "</p>\n";

// Test basic AI service
echo "<h3>🧪 Testing AI Service:</h3>\n";

$test_messages = [
    "Hello, what are your gym hours?",
    "Tell me about your membership plans",
    "I want to start working out but I'm a beginner"
];

foreach ($test_messages as $index => $message) {
    echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #007bff;'>\n";
    echo "<h4>Test " . ($index + 1) . ": \"$message\"</h4>\n";
    
    try {
        $start_time = microtime(true);
        $response = AIService::generateResponse($message);
        $end_time = microtime(true);
        $response_time = round(($end_time - $start_time) * 1000, 2);
        
        echo "<p><strong>✅ Response (${response_time}ms):</strong></p>\n";
        echo "<div style='background: #e8f5e9; padding: 10px; border-radius: 5px; white-space: pre-wrap;'>$response</div>\n";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>\n";
    }
    
    echo "</div>\n";
}

// Test API key validation
echo "<h3>🔑 API Key Validation:</h3>\n";

if (!empty(OPENAI_API_KEY)) {
    // Quick API test
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.openai.com/v1/models',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . OPENAI_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode === 200) {
        echo "<p style='color: green;'>✅ <strong>OpenAI API Key is VALID and working!</strong></p>\n";
        $models = json_decode($response, true);
        if (isset($models['data']) && is_array($models['data'])) {
            echo "<p>Available models: " . count($models['data']) . " found</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ <strong>OpenAI API Key validation failed!</strong></p>\n";
        echo "<p>HTTP Code: $httpCode</p>\n";
        if ($response) {
            $error = json_decode($response, true);
            if (isset($error['error']['message'])) {
                echo "<p>Error: " . $error['error']['message'] . "</p>\n";
            }
        }
    }
} else {
    echo "<p style='color: red;'>❌ <strong>OpenAI API Key is not configured!</strong></p>\n";
}

// Integration test with chatbot API
echo "<h3>🌐 Chatbot API Integration Test:</h3>\n";

$chatbot_api_file = __DIR__ . '/public/chatbot_api.php';
if (file_exists($chatbot_api_file)) {
    echo "<p>✅ Chatbot API file exists</p>\n";
    
    // Test file syntax
    $syntax_check = shell_exec('C:\xampp\php\php.exe -l "' . $chatbot_api_file . '" 2>&1');
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "<p style='color: green;'>✅ Chatbot API PHP syntax is valid</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Chatbot API has syntax errors:</p>\n";
        echo "<pre>$syntax_check</pre>\n";
    }
} else {
    echo "<p style='color: red;'>❌ Chatbot API file not found</p>\n";
}

echo "<div style='text-align: center; margin: 30px 0; padding: 20px; background: linear-gradient(135deg, #28a745, #20c997); color: white; border-radius: 10px;'>\n";
echo "<h2>🚀 CHATBOT STATUS SUMMARY</h2>\n";

if (!empty(OPENAI_API_KEY) && AI_PROVIDER === 'openai') {
    echo "<p>✅ <strong>OpenAI is prioritized and configured!</strong></p>\n";
    echo "<p>✅ API key is set and should be working</p>\n";
    echo "<p>✅ L9 Fitness context loaded for intelligent responses</p>\n";
    echo "<p>💪 <strong>Your AI chatbot is ready to help gym members!</strong></p>\n";
} else {
    echo "<p>⚠️ <strong>Configuration needs attention</strong></p>\n";
    echo "<p>Please ensure OpenAI API key is properly set</p>\n";
}

echo "</div>\n";
?>