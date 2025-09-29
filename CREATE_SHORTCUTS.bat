@echo off
title L9 Fitness - Desktop Shortcut Creator
echo.
echo ========================================
echo   🏋️ L9 FITNESS SHORTCUT CREATOR 🏋️
echo ========================================
echo.
echo This will create desktop shortcuts for easy website access
echo.

set DESKTOP=%USERPROFILE%\Desktop

echo Creating desktop shortcuts...

REM Create shortcut for main website
echo Set oWS = WScript.CreateObject("WScript.Shell") > "%TEMP%\CreateShortcut.vbs"
echo sLinkFile = "%DESKTOP%\L9 Fitness Website.lnk" >> "%TEMP%\CreateShortcut.vbs"
echo Set oLink = oWS.CreateShortcut(sLinkFile) >> "%TEMP%\CreateShortcut.vbs"
echo oLink.TargetPath = "http://localhost/Capstone-latest/public/" >> "%TEMP%\CreateShortcut.vbs"
echo oLink.Description = "L9 Fitness Gym Website" >> "%TEMP%\CreateShortcut.vbs"
echo oLink.IconLocation = "C:\Windows\System32\shell32.dll,14" >> "%TEMP%\CreateShortcut.vbs"
echo oLink.Save >> "%TEMP%\CreateShortcut.vbs"
cscript "%TEMP%\CreateShortcut.vbs" > nul

REM Create shortcut for admin panel
echo Set oWS = WScript.CreateObject("WScript.Shell") > "%TEMP%\CreateShortcut2.vbs"
echo sLinkFile = "%DESKTOP%\L9 Fitness Admin.lnk" >> "%TEMP%\CreateShortcut2.vbs"
echo Set oLink = oWS.CreateShortcut(sLinkFile) >> "%TEMP%\CreateShortcut2.vbs"
echo oLink.TargetPath = "http://localhost/Capstone-latest/public/admin.php" >> "%TEMP%\CreateShortcut2.vbs"
echo oLink.Description = "L9 Fitness Admin Panel" >> "%TEMP%\CreateShortcut2.vbs"
echo oLink.IconLocation = "C:\Windows\System32\shell32.dll,1" >> "%TEMP%\CreateShortcut2.vbs"
echo oLink.Save >> "%TEMP%\CreateShortcut2.vbs"
cscript "%TEMP%\CreateShortcut2.vbs" > nul

REM Create shortcut for launcher
echo Set oWS = WScript.CreateObject("WScript.Shell") > "%TEMP%\CreateShortcut3.vbs"
echo sLinkFile = "%DESKTOP%\L9 Fitness Launcher.lnk" >> "%TEMP%\CreateShortcut3.vbs"
echo Set oLink = oWS.CreateShortcut(sLinkFile) >> "%TEMP%\CreateShortcut3.vbs"
echo oLink.TargetPath = "%~dp0START_WEBSITE.bat" >> "%TEMP%\CreateShortcut3.vbs"
echo oLink.Description = "Launch L9 Fitness Website with XAMPP" >> "%TEMP%\CreateShortcut3.vbs"
echo oLink.IconLocation = "C:\Windows\System32\shell32.dll,25" >> "%TEMP%\CreateShortcut3.vbs"
echo oLink.Save >> "%TEMP%\CreateShortcut3.vbs"
cscript "%TEMP%\CreateShortcut3.vbs" > nul

REM Cleanup
del "%TEMP%\CreateShortcut.vbs" > nul 2>&1
del "%TEMP%\CreateShortcut2.vbs" > nul 2>&1
del "%TEMP%\CreateShortcut3.vbs" > nul 2>&1

echo.
echo ✅ Desktop shortcuts created successfully!
echo.
echo Created shortcuts:
echo - L9 Fitness Website.lnk (Direct website access)
echo - L9 Fitness Admin.lnk (Admin panel access)  
echo - L9 Fitness Launcher.lnk (Full launcher with XAMPP)
echo.
echo You can now access your website directly from desktop icons!
echo.
pause