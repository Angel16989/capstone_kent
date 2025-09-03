<?php
// Simple test script to verify authentication system
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/helpers/auth.php';
require_once __DIR__ . '/app/helpers/csrf.php';

echo "Testing Authentication System...\n\n";

// Test 1: Check if CSRF token is generated
echo "1. CSRF Token Test:\n";
if (isset($_SESSION['csrf_token']) && !empty($_SESSION['csrf_token'])) {
    echo "   ✅ CSRF token generated successfully: " . substr($_SESSION['csrf_token'], 0, 10) . "...\n";
} else {
    echo "   ❌ CSRF token not generated\n";
}

// Test 2: Check database connection
echo "\n2. Database Connection Test:\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Database connected successfully\n";
    echo "   📊 Total users in database: " . $result['user_count'] . "\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 3: Check if authentication functions exist
echo "\n3. Authentication Functions Test:\n";
if (function_exists('is_logged_in')) {
    echo "   ✅ is_logged_in() function exists\n";
} else {
    echo "   ❌ is_logged_in() function missing\n";
}

if (function_exists('login_user')) {
    echo "   ✅ login_user() function exists\n";
} else {
    echo "   ❌ login_user() function missing\n";
}

if (function_exists('verify_csrf')) {
    echo "   ✅ verify_csrf() function exists\n";
} else {
    echo "   ❌ verify_csrf() function missing\n";
}

// Test 4: Check login status
echo "\n4. Current Login Status:\n";
if (is_logged_in()) {
    $user = current_user();
    echo "   ✅ User is logged in: " . $user['name'] . " (" . $user['email'] . ")\n";
} else {
    echo "   ℹ️  No user currently logged in\n";
}

echo "\n🎉 Authentication system test complete!\n";
?>
