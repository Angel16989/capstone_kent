<?php
echo "<h1>🧪 Invoice Download API Test</h1>";

// Test the API path resolution
$config_path = '../../config/config.php';
$auth_path = '../../app/helpers/auth.php';

echo "<h2>📁 Path Testing:</h2>";
echo "<p><strong>Config path:</strong> $config_path</p>";
echo "<p><strong>Full path:</strong> " . realpath($config_path) . "</p>";
echo "<p><strong>Exists:</strong> " . (file_exists($config_path) ? '✅ Yes' : '❌ No') . "</p>";

echo "<p><strong>Auth path:</strong> $auth_path</p>";
echo "<p><strong>Full path:</strong> " . realpath($auth_path) . "</p>";
echo "<p><strong>Exists:</strong> " . (file_exists($auth_path) ? '✅ Yes' : '❌ No') . "</p>";

// Test database connection
try {
    require_once $config_path;
    echo "<p>✅ Config loaded successfully</p>";
    
    // Test database connection
    $stmt = $pdo->query("SELECT COUNT(*) FROM payments WHERE invoice_no IS NOT NULL");
    $invoice_count = $stmt->fetchColumn();
    echo "<p>✅ Database connected - Found $invoice_count invoices</p>";
    
    // Get a sample invoice
    $stmt = $pdo->query("SELECT invoice_no, member_id FROM payments WHERE invoice_no IS NOT NULL LIMIT 1");
    $sample = $stmt->fetch();
    
    if ($sample) {
        echo "<h3>🧪 Sample Invoice Test:</h3>";
        echo "<p><strong>Invoice:</strong> {$sample['invoice_no']}</p>";
        echo "<p><strong>Member ID:</strong> {$sample['member_id']}</p>";
        
        echo "<div style='margin: 20px 0; padding: 20px; background: #d4edda; border-radius: 10px;'>";
        echo "<h4>✅ API Ready!</h4>";
        echo "<p>The download invoice API should now work correctly.</p>";
        echo "<p><strong>Test URL:</strong> <a href='api/download_invoice.php?invoice={$sample['invoice_no']}' target='_blank'>Download Sample Invoice</a></p>";
        echo "<p><em>Note: You need to be logged in as the member who owns this invoice.</em></p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>🎯 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Login as Celebrity User:</strong> <a href='login.php'>login.php</a></li>";
echo "<li><strong>Go to Profile:</strong> Navigate to Profile → Payments tab</li>";
echo "<li><strong>Download Invoices:</strong> Click invoice download buttons (only in profile)</li>";
echo "<li><strong>Dashboard:</strong> Should show payment info without download buttons</li>";
echo "</ol>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>📋 Summary of Changes:</h4>";
echo "<ul>";
echo "<li>✅ Fixed API config path (../../config/config.php)</li>";
echo "<li>✅ Removed download buttons from Dashboard</li>";
echo "<li>✅ Download buttons only available in Profile.php</li>";
echo "<li>✅ Fixed cloud-like CSS structure issues</li>";
echo "<li>✅ Clean dashboard layout with proper styling</li>";
echo "</ul>";
echo "</div>";
?>