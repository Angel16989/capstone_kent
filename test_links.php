<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>🔗 Testing BASE_URL and Links</h2>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
echo "<p><strong>Full Terms URL:</strong> " . BASE_URL . "terms.php</p>";
echo "<p><strong>Full Privacy URL:</strong> " . BASE_URL . "privacy.php</p>";

// Test if files exist
$terms_file = __DIR__ . '/public/terms.php';
$privacy_file = __DIR__ . '/public/privacy.php';

echo "<h3>📁 File System Check:</h3>";
echo "<p>Terms file exists: " . (file_exists($terms_file) ? "✅ YES" : "❌ NO") . "</p>";
echo "<p>Privacy file exists: " . (file_exists($privacy_file) ? "✅ YES" : "❌ NO") . "</p>";

// Test actual URL construction
echo "<h3>🌐 URL Construction Test:</h3>";
echo "<p>Terms URL would be: <code>" . BASE_URL . "terms.php</code></p>";
echo "<p>Privacy URL would be: <code>" . BASE_URL . "privacy.php</code></p>";

echo "<div style='background: #e7f3ff; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
echo "<h4>📋 Links in Checkout Page:</h4>";
echo "<p>From checkout.php, the links should resolve to:</p>";
echo "<ul>";
echo "<li><strong>Terms:</strong> <a href='" . BASE_URL . "terms.php' target='_blank'>" . BASE_URL . "terms.php</a></li>";
echo "<li><strong>Privacy:</strong> <a href='" . BASE_URL . "privacy.php' target='_blank'>" . BASE_URL . "privacy.php</a></li>";
echo "</ul>";
echo "</div>";
?>