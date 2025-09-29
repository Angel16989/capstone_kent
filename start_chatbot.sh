#!/bin/bash

echo "🤖 L9 Fitness Chatbot Status Check"
echo "=================================="
echo ""

# Check MySQL
echo "📊 Database Status:"
if sudo service mysql status > /dev/null 2>&1; then
    echo "✅ MySQL Server: Running"
    mysql_version=$(mysql --version 2>/dev/null | cut -d' ' -f1-3)
    echo "   Version: $mysql_version"
else
    echo "❌ MySQL Server: Not running"
fi

# Check PHP
echo ""
echo "🐘 PHP Status:"
php_version=$(php8.3 --version | head -1)
echo "✅ PHP: $php_version"

# Check database connection
echo ""
echo "🔗 Database Connection:"
if php8.3 -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;dbname=l9_gym;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo 'Connected to l9_gym database successfully\n';
    \$stmt = \$pdo->query('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = \"l9_gym\"');
    \$count = \$stmt->fetch()['count'];
    echo \"Found \$count tables in database\n\";
} catch (Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage() . \"\n\";
    exit(1);
}
"; then
    echo "✅ Database Connection: Working"
else
    echo "❌ Database Connection: Failed"
fi

# Test Chatbot API
echo ""
echo "🤖 Chatbot API Test:"
cd /workspaces/Capstone/public

# Test API directly with PHP
api_response=$(php8.3 -r "
\$_SERVER['REQUEST_METHOD'] = 'POST';
file_put_contents('php://input', '{\"message\":\"Hello\"}');
ob_start();
include 'chatbot_api.php';
\$output = ob_get_clean();
echo \$output;
" 2>/dev/null)

if echo "$api_response" | grep -q "success.*true"; then
    echo "✅ Chatbot API: Working"
    echo "   Response: $(echo "$api_response" | php8.3 -r "echo json_decode(file_get_contents('php://stdin'), true)['response'] ?? 'Unknown';")"
else
    echo "❌ Chatbot API: Failed"
    echo "   Error: $api_response"
fi

# Start web server for testing
echo ""
echo "🌐 Starting Web Server:"
echo "   Starting PHP development server on port 8000..."
echo "   You can access the chatbot at:"
echo "   - https://CODESPACE_NAME-8000.app.github.dev/"
echo "   - Or use port forwarding in VS Code"
echo ""
echo "🚀 Starting server..."

php8.3 -S 0.0.0.0:8000 -t /workspaces/Capstone/public
