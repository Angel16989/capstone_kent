<?php
/**
 * Test Payment Success Page
 */

require_once __DIR__ . '/config/config.php';

// Create a fake payment record for testing
$fakePayment = [
    'first_name' => 'Demo',
    'plan_name' => 'Premium',
    'duration_days' => 365,
    'start_date' => date('Y-m-d'),
    'end_date' => date('Y-m-d', strtotime('+1 year')),
    'amount' => 99.99,
    'method' => 'Credit Card',
    'txn_ref' => 'TXN' . strtoupper(uniqid()),
    'paid_at' => date('Y-m-d H:i:s'),
    'invoice_no' => 'INV' . strtoupper(uniqid())
];

echo "<h2>🧪 Test Payment Success Pages</h2>";
echo "<hr>";

echo "<h3>📄 Styled Pages Test Links</h3>";
echo "<div style='display: grid; gap: 20px; max-width: 800px; margin: 20px 0;'>";

echo "<div style='background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 25px; border-radius: 15px; text-align: center;'>";
echo "<h4>💬 Contact Us Page</h4>";
echo "<p>Beautiful contact form with modern gradient design, interactive elements, and professional layout</p>";
echo "<a href='public/contact.php' target='_blank' style='background: white; color: #667eea; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; margin-top: 10px;'>View Contact Page</a>";
echo "</div>";

echo "<div style='background: linear-gradient(135deg, #00C851, #4CAF50); color: white; padding: 25px; border-radius: 15px; text-align: center;'>";
echo "<h4>🎉 Payment Success Page</h4>";
echo "<p>Luxurious success page with confetti animation, payment details, and next steps</p>";
echo "<a href='public/checkout-success.php?invoice=" . $fakePayment['invoice_no'] . "' target='_blank' style='background: white; color: #00C851; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; margin-top: 10px;'>Test Success Page</a>";
echo "</div>";

echo "<div style='background: linear-gradient(135deg, #2c3e50, #34495e); color: white; padding: 25px; border-radius: 15px; text-align: center;'>";
echo "<h4>📋 Terms of Service</h4>";
echo "<p>Professional legal document with table of contents, smooth scrolling, and modern design</p>";
echo "<a href='public/terms.php' target='_blank' style='background: white; color: #2c3e50; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; margin-top: 10px;'>View Terms</a>";
echo "</div>";

echo "<div style='background: linear-gradient(135deg, #8e44ad, #9b59b6); color: white; padding: 25px; border-radius: 15px; text-align: center;'>";
echo "<h4>🔒 Privacy Policy</h4>";
echo "<p>Comprehensive privacy policy with interactive sections, highlighting, and professional styling</p>";
echo "<a href='public/privacy.php' target='_blank' style='background: white; color: #8e44ad; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; margin-top: 10px;'>View Privacy Policy</a>";
echo "</div>";

echo "</div>";

echo "<hr>";
echo "<h3>✨ Design Features</h3>";
echo "<ul style='line-height: 1.8; color: #555;'>";
echo "<li><strong>🌈 Beautiful Gradients:</strong> Modern gradient backgrounds with floating animations</li>";
echo "<li><strong>💫 Smooth Animations:</strong> Subtle shimmer effects, hover animations, and transitions</li>";
echo "<li><strong>📱 Fully Responsive:</strong> Perfect on all devices from mobile to desktop</li>";
echo "<li><strong>🎨 Professional Design:</strong> Clean, modern aesthetic with proper typography</li>";
echo "<li><strong>⚡ Interactive Elements:</strong> Hover effects, glowing buttons, and animated icons</li>";
echo "<li><strong>♿ Accessible:</strong> Proper contrast, focus states, and screen reader support</li>";
echo "</ul>";

echo "<hr>";
echo "<p style='text-align: center; color: #666; font-style: italic;'>";
echo "All pages feature ultra-modern designs with beautiful CSS animations, gradients, and interactive elements!";
echo "</p>";

// For testing payment success, we need to temporarily insert a record
try {
    $checkStmt = $pdo->prepare("SELECT id FROM payments WHERE invoice_no = ? LIMIT 1");
    $checkStmt->execute([$fakePayment['invoice_no']]);
    
    if (!$checkStmt->fetch()) {
        // Insert fake payment for demo
        $insertStmt = $pdo->prepare("
            INSERT INTO payments (member_id, membership_id, amount, method, txn_ref, invoice_no, paid_at, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')
        ");
        $insertStmt->execute([
            $_SESSION['user']['id'] ?? 1, // Use current user or default to 1
            1, // Default membership ID
            $fakePayment['amount'],
            $fakePayment['method'],
            $fakePayment['txn_ref'],
            $fakePayment['invoice_no'],
            $fakePayment['paid_at']
        ]);
        
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-top: 20px;'>";
        echo "<strong>✅ Test payment record created!</strong> You can now test the payment success page.";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-top: 20px;'>";
    echo "<strong>⚠️ Note:</strong> Payment success page may not work fully without proper database setup.";
    echo "</div>";
}
?>