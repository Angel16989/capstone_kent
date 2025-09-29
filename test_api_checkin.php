<?php
// Quick test of the check-in API functionality
session_start();

echo "<h1>🧪 API Check-in Test</h1>";

// Simulate being logged in as Michael Jackson
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'Michael Jackson',
    'email' => 'mj@l9fitness.com'
];

echo "<p>✅ Simulated login as: <strong>{$_SESSION['user']['name']}</strong></p>";

// Test the API endpoint
$api_url = 'http://localhost/Capstone-latest/public/api/profile_api.php?action=check_in';

echo "<h2>🔗 Testing API: $api_url</h2>";

// Use cURL to test the API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>📥 API Response (HTTP $httpCode):</h3>";
echo "<div style='background: #000; padding: 15px; border-radius: 5px; color: #0f0; font-family: monospace;'>";
echo htmlspecialchars($response);
echo "</div>";

if ($response) {
    $data = json_decode($response, true);
    if ($data) {
        echo "<h3>🎯 Parsed Response:</h3>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
        print_r($data);
        echo "</pre>";
        
        if (isset($data['success']) && $data['success']) {
            echo "<div style='background: #004d00; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0;'>";
            echo "<h2>🎉 CHECK-IN API WORKING!</h2>";
            echo "<p>✅ " . ($data['message'] ?? 'Success') . "</p>";
            echo "<p><strong>Now test in the dashboard:</strong></p>";
            echo "<a href='login.php' style='background: #ff4444; color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px;'>🚀 LOGIN & CHECK-IN</a>";
            echo "</div>";
        } else {
            echo "<div style='background: #660000; padding: 20px; border-radius: 10px;'>";
            echo "<h3>❌ API Error:</h3>";
            echo "<p>" . ($data['message'] ?? $data['error'] ?? 'Unknown error') . "</p>";
            echo "</div>";
        }
    }
}

// Also test check-out
echo "<hr><h2>🚪 Testing Check-out API</h2>";
$checkout_url = 'http://localhost/Capstone-latest/public/api/profile_api.php?action=check_out';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $checkout_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$checkout_response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>📥 Checkout API Response (HTTP $httpCode):</h3>";
echo "<div style='background: #000; padding: 15px; border-radius: 5px; color: #0f0; font-family: monospace;'>";
echo htmlspecialchars($checkout_response);
echo "</div>";
?>