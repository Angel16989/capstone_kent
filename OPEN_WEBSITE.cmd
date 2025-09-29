@echo off
title L9 Fitness - Website Launcher
color 0C
echo.
echo ========================================
echo    🏋️ L9 FITNESS WEBSITE LAUNCHER 🏋️
echo ========================================
echo.
echo If this opened in VS Code, right-click the file
echo and select "Open with" ^> "Command Prompt"
echo.
echo Starting website...
echo.

REM Force open in browser
start "" "http://localhost/Capstone-latest/public/"

echo Website opening in browser...
echo URL: http://localhost/Capstone-latest/public/
echo.
echo ✅ Done! Check your browser.
echo.
echo Press any key to close this window...
pause >nul
exit