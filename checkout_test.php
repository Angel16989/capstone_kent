<?php
echo "🛒 L9 FITNESS CHECKOUT SYSTEM TEST\n";
echo "===================================\n";

require_once 'config/config.php';

// Test 1: Check if membership plans exist
echo "\n📋 MEMBERSHIP PLANS:\n";
try {
    $stmt = $pdo->query('SELECT id, name, price, duration_days FROM membership_plans WHERE is_active = 1 ORDER BY price ASC');
    $plans = $stmt->fetchAll();
    
    foreach($plans as $plan) {
        echo "✅ {$plan['name']} - \${$plan['price']} ({$plan['duration_days']} days)\n";
        echo "   Checkout URL: http://localhost/Capstone-latest/public/checkout.php?plan_id={$plan['id']}\n";
    }
} catch(Exception $e) {
    echo "❌ Error loading plans: " . $e->getMessage() . "\n";
}

// Test 2: Check if checkout page is accessible
echo "\n🔒 CHECKOUT SYSTEM:\n";
$checkoutUrl = 'http://localhost/Capstone-latest/public/checkout.php?plan_id=1';
$ch = curl_init($checkoutUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 302) {
    echo "✅ Checkout page properly redirects non-logged users to login\n";
} else {
    echo "❌ Checkout page issue - HTTP $httpCode\n";
}

// Test 3: Check database tables for checkout functionality
echo "\n🗄️ DATABASE CHECKOUT TABLES:\n";
$tables = ['memberships', 'payments', 'membership_plans'];

foreach($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetchColumn();
        echo "✅ $table: $count records\n";
    } catch(Exception $e) {
        echo "❌ $table: Error - " . $e->getMessage() . "\n";
    }
}

// Test 4: Check recent transactions
echo "\n💳 RECENT TRANSACTIONS:\n";
try {
    $stmt = $pdo->query('
        SELECT p.invoice_no, p.amount, p.method, p.paid_at, mp.name as plan_name, u.first_name
        FROM payments p
        JOIN memberships m ON p.membership_id = m.id  
        JOIN membership_plans mp ON m.plan_id = mp.id
        JOIN users u ON p.member_id = u.id
        ORDER BY p.paid_at DESC
        LIMIT 5
    ');
    $transactions = $stmt->fetchAll();
    
    if ($transactions) {
        foreach($transactions as $txn) {
            echo "✅ {$txn['invoice_no']} - {$txn['first_name']} - \${$txn['amount']} ({$txn['plan_name']}) - {$txn['paid_at']}\n";
        }
    } else {
        echo "ℹ️ No transactions yet - system ready for first purchase\n";
    }
} catch(Exception $e) {
    echo "❌ Transaction check error: " . $e->getMessage() . "\n";
}

echo "\n🛍️ PURCHASE FLOW:\n";
echo "1. Visit: http://localhost/Capstone-latest/public/memberships.php\n";
echo "2. Login with: admin@l9.local / Password123\n";
echo "3. Click any 'CLAIM POWER' button\n";
echo "4. Fill checkout form with test payment details\n";
echo "5. Complete purchase and see success page\n";

echo "\n🎯 CHECKOUT URLs:\n";
echo "Memberships: http://localhost/Capstone-latest/public/memberships.php\n";
echo "Checkout: http://localhost/Capstone-latest/public/checkout.php?plan_id=1\n";
echo "Success: http://localhost/Capstone-latest/public/checkout-success.php\n";

echo "\n🎉 CHECKOUT SYSTEM READY!\n";
?>