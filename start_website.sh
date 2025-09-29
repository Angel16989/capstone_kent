#!/bin/bash
# L9 Fitness Website Launcher (Linux/Mac)

echo "🏋️ L9 FITNESS WEBSITE LAUNCHER 🏋️"
echo "=================================="
echo

# Check if XAMPP is running (for Linux XAMPP)
if command -v /opt/lampp/lampp &> /dev/null; then
    echo "Starting XAMPP services..."
    sudo /opt/lampp/lampp start
else
    echo "Note: Please make sure your web server is running"
fi

echo
echo "Opening L9 Fitness website..."
echo "URL: http://localhost/Capstone-latest/public/"

# Open in default browser (cross-platform)
if command -v xdg-open &> /dev/null; then
    xdg-open "http://localhost/Capstone-latest/public/"
elif command -v open &> /dev/null; then
    open "http://localhost/Capstone-latest/public/"
else
    echo "Please open: http://localhost/Capstone-latest/public/ in your browser"
fi

echo "✅ Website should now be open!"