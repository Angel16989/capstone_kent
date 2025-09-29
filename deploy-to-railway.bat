@echo off
REM L9 Fitness - Railway Deployment Script (Windows)
REM This script helps deploy the L9 Fitness application to Railway

echo 🚂 L9 Fitness - Railway Deployment Script
echo ========================================
echo.

REM Check if Railway CLI is installed
railway --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Railway CLI not found. Installing...
    powershell -Command "Invoke-WebRequest -Uri 'https://railway.app/install.sh' -OutFile 'install-railway.sh'"
    bash install-railway.sh
    del install-railway.sh
)

REM Login to Railway
echo 🔐 Logging into Railway...
railway login

REM Create new project
echo 📦 Creating Railway project...
railway init l9-fitness

REM Link to existing project or create new one
echo 🔗 Linking to project...
railway link

REM Set environment variables for production
echo ⚙️ Setting up environment variables...
railway variables set NODE_ENV=production
railway variables set APP_ENV=production

REM Deploy the application
echo 🚀 Deploying application...
railway up

REM Get the deployment URL
echo 🌐 Getting deployment URL...
for /f "tokens=*" %%i in ('railway domain') do set DEPLOY_URL=%%i

echo.
echo ✅ Deployment Complete!
echo ======================
echo 🌍 Your site is live at: %DEPLOY_URL%
echo.
echo 📋 Next Steps:
echo 1. Visit %DEPLOY_URL%/create_admin.php to create admin account
echo 2. Run database setup scripts in Railway terminal
echo 3. Test all features
echo.
echo 📚 Useful Railway Commands:
echo - railway logs          # View application logs
echo - railway variables     # Manage environment variables
echo - railway connect       # Connect to database
echo - railway status        # Check deployment status
echo.
echo 🎉 Happy deploying! Your L9 Fitness gym is now online!
echo.
pause