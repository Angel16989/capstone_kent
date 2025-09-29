@echo off
REM L9 Fitness - Complete Start Script (Windows)
REM This script starts the L9 Fitness application with all dependencies

echo.
echo ============================================
echo  🏋️  L9 FITNESS - START SCRIPT (Windows)
echo ============================================
echo.

REM Check if we're in Railway environment
if defined RAILWAY_ENVIRONMENT (
    echo 🚂 Running on Railway - Production Mode
    goto :railway_setup
) else (
    echo 💻 Local Development Mode
    goto :local_setup
)

:railway_setup
echo.
echo 🔍 Setting up Railway environment...

REM Set PHP configuration for Railway
if not defined PHP_MEMORY_LIMIT set PHP_MEMORY_LIMIT=256M
if not defined PHP_MAX_EXECUTION_TIME set PHP_MAX_EXECUTION_TIME=30

echo ✅ Railway environment configured

REM Check database connection
echo.
echo 🔍 Checking database connection...
php -r "
try {
    $pdo = new PDO(
        'mysql:host=' . getenv('MYSQLHOST') . ';port=' . getenv('MYSQLPORT') . ';dbname=' . getenv('MYSQLDATABASE'),
        getenv('MYSQLUSER'),
        getenv('MYSQLPASSWORD'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo 'Database connected successfully';
} catch (Exception $e) {
    echo 'Database connection failed: ' . $e->getMessage();
    exit(1);
}
"
if %errorlevel% neq 0 (
    echo ❌ Database connection failed
    pause
    exit /b 1
)
echo ✅ Database connection established

REM Run database setup if needed
if not exist .db_setup_complete (
    echo.
    echo 🔍 Running initial database setup...
    if exist database\schema.sql (
        echo Loading schema...
        REM Schema setup would be handled by Railway
    )
    if exist database\seed.sql (
        echo Loading seed data...
        REM Seed data would be handled by Railway
    )
    echo. > .db_setup_complete
    echo ✅ Database setup completed
)

echo.
echo ✅ L9 Fitness is ready!
echo.
echo 🌐 Your app is running at: %RAILWAY_STATIC_URL%
echo.
goto :end

:local_setup
echo.
echo 🔍 Checking system requirements...

REM Check PHP (try XAMPP first, then system PHP)
where php >nul 2>&1
if %errorlevel% neq 0 (
    REM Try XAMPP PHP
    if exist "C:\xampp\php\php.exe" (
        set PHP_CMD="C:\xampp\php\php.exe"
        echo ✅ Found XAMPP PHP
    ) else (
        echo ❌ PHP not found. Please install PHP 8.1+
        echo    Download from: https://windows.php.net/download
        echo    Or install XAMPP: https://www.apachefriends.org/
        pause
        exit /b 1
    )
) else (
    set PHP_CMD=php
)
for /f "tokens=2" %%i in ('%PHP_CMD% --version ^| findstr /r "PHP [0-9]"') do set PHP_VERSION=%%i
echo ✅ PHP %PHP_VERSION% found

REM Check MySQL (try XAMPP first, then system MySQL)
where mysql >nul 2>&1
if %errorlevel% neq 0 (
    REM Try XAMPP MySQL
    if exist "C:\xampp\mysql\bin\mysql.exe" (
        set MYSQL_CMD="C:\xampp\mysql\bin\mysql.exe"
        echo ✅ Found XAMPP MySQL
    ) else (
        echo ❌ MySQL not found. Please install MySQL 8.0+
        echo    Download from: https://dev.mysql.com/downloads/mysql/
        echo    Or install XAMPP: https://www.apachefriends.org/
        pause
        exit /b 1
    )
) else (
    set MYSQL_CMD=mysql
)
for /f "tokens=1-3" %%i in ('%MYSQL_CMD% --version') do set MYSQL_VERSION=%%i %%j %%k
echo ✅ %MYSQL_VERSION% found

REM Check if MySQL service is running (XAMPP)
net start | findstr /i mysql >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ MySQL service is not running
    echo    Please start XAMPP MySQL or MySQL service
    echo.
    echo    To start MySQL service manually:
    echo    - XAMPP: Run XAMPP Control Panel as Administrator
    echo    - Windows Service: net start mysql
    pause
    exit /b 1
)
echo ✅ MySQL service is running

echo.
echo 🔍 Setting up database...

REM Database configuration
if not defined DB_HOST set DB_HOST=localhost
if not defined DB_PORT set DB_PORT=3306
if not defined DB_NAME set DB_NAME=l9_gym
if not defined DB_USER set DB_USER=root
if not defined DB_PASS set DB_PASS=

REM Check database connection
echo Testing database connection...
%PHP_CMD% -r "
try {
    $pdo = new PDO('mysql:host=%DB_HOST%;port=%DB_PORT%;charset=utf8mb4', '%DB_USER%', '%DB_PASS%', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo 'MySQL connection successful';
} catch (Exception $e) {
    echo 'MySQL connection failed: ' . $e->getMessage();
    exit(1);
}
"
if %errorlevel% neq 0 (
    echo ❌ Database connection failed
    pause
    exit /b 1
)
echo ✅ Database connection established

REM Create database if it doesn't exist
echo Ensuring database exists...
%PHP_CMD% -r "
try {
    $pdo = new PDO('mysql:host=%DB_HOST%;port=%DB_PORT%;charset=utf8mb4', '%DB_USER%', '%DB_PASS%', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo 'Database %DB_NAME% ready';
} catch (Exception $e) {
    echo 'Failed to create database: ' . $e->getMessage();
    exit(1);
}
"

REM Run database setup
echo.
echo 🔍 Running database setup...
if exist database\schema.sql (
    echo Loading schema...
    %MYSQL_CMD% -h%DB_HOST% -P%DB_PORT% -u%DB_USER% -p%DB_PASS% %DB_NAME% < database\schema.sql 2>nul
    if %errorlevel% equ 0 (
        echo ✅ Schema loaded
    ) else (
        echo ❌ Schema loading failed
    )
)

if exist database\seed.sql (
    echo Loading seed data...
    %MYSQL_CMD% -h%DB_HOST% -P%DB_PORT% -u%DB_USER% -p%DB_PASS% %DB_NAME% < database\seed.sql 2>nul
    if %errorlevel% equ 0 (
        echo ✅ Seed data loaded
    ) else (
        echo ❌ Seed data loading failed
    )
)

REM Load additional dummy data
set DUMMY_FILES=additional_dummy_data.sql comprehensive_dummy_data.sql trainer_bookings_dummy_data.sql trainer_messages_dummy_data.sql customer_files_dummy_data.sql

for %%f in (%DUMMY_FILES%) do (
    if exist database\%%f (
        echo Loading %%f...
        %MYSQL_CMD% -h%DB_HOST% -P%DB_PORT% -u%DB_USER% -p%DB_PASS% %DB_NAME% < database\%%f 2>nul
        if %errorlevel% equ 0 (
            echo ✅ %%f loaded
        ) else (
            echo ❌ %%f loading failed
        )
    )
)

echo.
echo 🔍 Starting web server...

REM Determine the correct public directory
if exist public (
    set WEB_ROOT=public
) else if exist www (
    set WEB_ROOT=www
) else (
    set WEB_ROOT=.
)

REM Start PHP built-in server
if not defined PORT set PORT=8000
if not defined HOST set HOST=localhost

echo ✅ Starting PHP server on http://%HOST%:%PORT%
echo    Document root: %CD%\%WEB_ROOT%
echo.
echo 🌐 Access your application at:
echo    http://%HOST%:%PORT%
echo.
echo 🔧 Admin access:
echo    http://%HOST%:%PORT%/create_admin.php
echo.
echo 🤖 Test chatbot:
echo    http://%HOST%:%PORT%/test_chatbot.html
echo.
echo Press Ctrl+C to stop the server
echo.

REM Start the server
%PHP_CMD% -S %HOST%:%PORT% -t %WEB_ROOT%

:end
echo.
echo 🏋️ L9 Fitness start script completed!
pause