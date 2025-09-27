<?php
echo "🏋️ L9 FITNESS WEBSITE COMPREHENSIVE TEST\n";
echo "==========================================\n";

require_once 'config/config.php';

echo "\n📄 PAGE TESTS:\n";
$pages = [
    'index.php' => 'Homepage',
    'login.php' => 'Login Page', 
    'register.php' => 'Registration',
    'memberships.php' => 'Memberships',
    'classes.php' => 'Classes',
    'contact.php' => 'Contact',
    'terms.php' => 'Terms',
    'privacy.php' => 'Privacy'
];

foreach($pages as $page => $name) {
    $url = 'http://localhost/Capstone-latest/public/' . $page;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode == 200) ? '✅' : (($httpCode == 302) ? '🔄' : '❌');
    echo "$status $name ($page) - HTTP $httpCode\n";
}

echo "\n🗄️ DATABASE TESTS:\n";
try {
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $userCount = $stmt->fetch()['count'];
    echo "✅ Users table: $userCount users\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM user_roles');
    $roleCount = $stmt->fetch()['count'];
    echo "✅ User roles: $roleCount roles\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM membership_plans');
    $planCount = $stmt->fetch()['count'];
    echo "✅ Membership plans: $planCount plans\n";
    
} catch(Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n🤖 CHATBOT TEST:\n";
$testMessage = json_encode(['message' => 'Hello']);
$context = stream_context_create([
    'http' => [
        'header' => 'Content-type: application/json',
        'method' => 'POST',
        'content' => $testMessage
    ]
]);

$result = @file_get_contents('http://localhost/Capstone-latest/public/simple_chatbot_api.php', false, $context);
if($result) {
    $response = json_decode($result, true);
    echo "✅ Chatbot API: " . ($response['success'] ? 'Working' : 'Failed') . "\n";
    echo "   AI Powered: " . ($response['ai_powered'] ? 'Yes' : 'No (Fallback)') . "\n";
} else {
    echo "❌ Chatbot API: Failed to connect\n";
}

echo "\n🎯 TEST ACCOUNTS:\n";
echo "Admin: admin@l9.local / Password123\n";
echo "User:  tina@l9.local / Password123\n";
echo "User:  mia@l9.local / Password123\n";

echo "\n🌐 ACCESS URLS:\n";
echo "Main Site: http://localhost/Capstone-latest/public/\n";
echo "Register:  http://localhost/Capstone-latest/public/register.php\n";
echo "Login:     http://localhost/Capstone-latest/public/login.php\n";

echo "\n🎉 TEST COMPLETE!\n";
?>