@echo off
REM L9 Fitness Website Setup Script for Windows

echo 🏋️ Setting up L9 Fitness Website...

REM Check if we're in the right directory
if not exist "setup_db.php" (
    echo ❌ Error: Please run this script from the project root directory
    pause
    exit /b 1
)

REM Check if PHP is available
php -v >nul 2>&1
if errorlevel 1 (
    echo ❌ Error: PHP is not installed or not in PATH
    echo Please install XAMPP or PHP and make sure it's accessible from command line
    pause
    exit /b 1
)

REM Setup database
echo 📊 Setting up database...
php setup_db.php

REM Test database connection
echo 🔍 Testing database connection...
php test_db.php

echo ✅ L9 Fitness Website setup complete!
echo.
echo 🌐 Access your website at:
echo    http://localhost/[your-project-folder]/public/
echo.
echo 🤖 Features available:
echo    - User registration and login
echo    - Membership plans and checkout
echo    - Class booking system
echo    - AI chatbot assistant
echo    - Admin panel
echo.
echo 👑 To create an admin user, visit:
echo    http://localhost/[your-project-folder]/public/create_admin.php
echo.
pause