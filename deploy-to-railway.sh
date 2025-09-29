#!/bin/bash

# L9 Fitness - Railway Deployment Script
# This script helps deploy the L9 Fitness application to Railway

echo "🚂 L9 Fitness - Railway Deployment Script"
echo "========================================"

# Check if Railway CLI is installed
if ! command -v railway &> /dev/null; then
    echo "❌ Railway CLI not found. Installing..."
    curl -fsSL https://railway.app/install.sh | sh
fi

# Login to Railway
echo "🔐 Logging into Railway..."
railway login

# Create new project
echo "📦 Creating Railway project..."
railway init l9-fitness

# Link to existing project or create new one
echo "🔗 Linking to project..."
railway link

# Set environment variables for production
echo "⚙️ Setting up environment variables..."
railway variables set NODE_ENV=production
railway variables set APP_ENV=production

# Deploy the application
echo "🚀 Deploying application..."
railway up

# Get the deployment URL
echo "🌐 Getting deployment URL..."
DEPLOY_URL=$(railway domain)

echo ""
echo "✅ Deployment Complete!"
echo "======================"
echo "🌍 Your site is live at: $DEPLOY_URL"
echo ""
echo "📋 Next Steps:"
echo "1. Visit $DEPLOY_URL/create_admin.php to create admin account"
echo "2. Run database setup scripts in Railway terminal"
echo "3. Test all features"
echo ""
echo "📚 Useful Railway Commands:"
echo "- railway logs          # View application logs"
echo "- railway variables     # Manage environment variables"
echo "- railway connect       # Connect to database"
echo "- railway status        # Check deployment status"
echo ""
echo "🎉 Happy deploying! Your L9 Fitness gym is now online!"