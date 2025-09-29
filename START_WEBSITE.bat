@echo off
title L9 Fitness - Starting Website
echo.
echo ========================================
echo    🏋️ L9 FITNESS WEBSITE LAUNCHER 🏋️
echo ========================================
echo.
echo Starting XAMPP services...
echo.

REM Start XAMPP Apache and MySQL
net start | find "Apache" > nul
if %errorlevel% neq 0 (
    echo Starting Apache...
    "C:\xampp\apache\bin\httpd.exe" -k start
) else (
    echo Apache is already running
)

net start | find "MySQL" > nul
if %errorlevel% neq 0 (
    echo Starting MySQL...
    "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone --console
) else (
    echo MySQL is already running
)

echo.
echo Waiting 3 seconds for services to start...
timeout /t 3 /nobreak > nul

echo.
echo Opening L9 Fitness website in your browser...
echo URL: http://localhost/Capstone-latest/public/
echo.

REM Open the website in default browser
start "L9 Fitness Website" "http://localhost/Capstone-latest/public/"

echo.
echo ✅ Website should now be open in your browser!
echo.
echo Available pages:
echo - Homepage: http://localhost/Capstone-latest/public/
echo - Login: http://localhost/Capstone-latest/public/login.php  
echo - Register: http://localhost/Capstone-latest/public/register.php
echo - Admin: http://localhost/Capstone-latest/public/admin.php
echo - Google OAuth Setup: http://localhost/Capstone-latest/public/google_oauth_setup.php?password=l9fitness123
echo.
echo Press any key to exit...
pause > nul