<?php
// WAKI API Connection Test
require_once __DIR__ . '/../config/ai_config.php';

echo "🤖 WAKI API CONNECTION TEST\n";
echo "=" . str_repeat("=", 40) . "\n\n";

// Check if config is loaded
echo "1. Configuration Check:\n";
echo "   - AI Config loaded: " . (defined('OPENAI_API_KEY') ? "✅ YES" : "❌ NO") . "\n";
echo "   - API Key present: " . (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY) ? "✅ YES" : "❌ NO") . "\n";
echo "   - API Key length: " . (defined('OPENAI_API_KEY') ? strlen(OPENAI_API_KEY) : 0) . " characters\n";
echo "   - Model: " . (defined('OPENAI_MODEL') ? OPENAI_MODEL : "Not defined") . "\n\n";

// Test API call
echo "2. API Connection Test:\n";

$test_message = "Hello WAKI, are you working?";
$system_prompt = "You are WAKI, respond with 'WAKI IS ONLINE AND READY TO CRUSH IT! 💪🔥' if you receive this message.";

$data = [
    'model' => OPENAI_MODEL,
    'messages' => [
        ['role' => 'system', 'content' => $system_prompt],
        ['role' => 'user', 'content' => $test_message]
    ],
    'max_tokens' => 50,
    'temperature' => 0.7
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "   - HTTP Status: $http_code\n";
echo "   - cURL Error: " . ($error ? $error : "None") . "\n";

if ($http_code === 200 && $response) {
    $api_response = json_decode($response, true);
    if (isset($api_response['choices'][0]['message']['content'])) {
        echo "   - ✅ SUCCESS! API Response:\n";
        echo "     " . trim($api_response['choices'][0]['message']['content']) . "\n";
    } else {
        echo "   - ❌ FAILED to parse response\n";
        echo "   - Response: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "   - ❌ FAILED API call\n";
    if ($response) {
        echo "   - Error Response: " . substr($response, 0, 200) . "...\n";
    }
}

echo "\n3. Recommendations:\n";
if ($http_code === 200) {
    echo "   ✅ WAKI is ready to go! API connection successful.\n";
} elseif ($http_code === 401) {
    echo "   ❌ API Key issue - check if key is valid and has credits\n";
} elseif ($http_code === 429) {
    echo "   ⚠️  Rate limit exceeded - wait a moment and try again\n";
} elseif ($http_code === 0 || $error) {
    echo "   ❌ Network/cURL issue - check internet connection\n";
} else {
    echo "   ❌ Unexpected error - HTTP $http_code\n";
}

echo "\n🚀 Test Complete!\n";
?>