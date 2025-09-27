#!/bin/bash

# L9 Fitness Website Setup Script
echo "🏋️ Setting up L9 Fitness Website..."

# Check if we're in the right directory
if [ ! -f "setup_db.php" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP is not installed or not in PATH"
    echo "Please install PHP and make sure it's accessible from command line"
    exit 1
fi

# Setup database
echo "📊 Setting up database..."
php setup_db.php

# Test database connection
echo "🔍 Testing database connection..."
php test_db.php

echo "✅ L9 Fitness Website setup complete!"
echo ""
echo "🌐 Access your website at:"
echo "   http://localhost/[your-project-folder]/public/"
echo ""
echo "🤖 Features available:"
echo "   - User registration and login"
echo "   - Membership plans and checkout"
echo "   - Class booking system"
echo "   - AI chatbot assistant"
echo "   - Admin panel"
echo ""
echo "👑 To create an admin user, visit:"
echo "   http://localhost/[your-project-folder]/public/create_admin.php"