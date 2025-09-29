# 🚂 L9 Fitness - Railway Deployment (Recommended)

## Why Railway?
Railway is perfect for PHP/MySQL applications like L9 Fitness:
- ✅ Native PHP 8+ support
- ✅ Managed MySQL databases
- ✅ GitHub integration
- ✅ Automatic deployments
- ✅ Free tier available
- ✅ Production-ready infrastructure

## 📋 Prerequisites
- GitHub repository with your code
- Railway account (free)

## 🚀 5-Minute Deployment Steps

### Step 1: Create Railway Account
1. Go to https://railway.app
2. Sign up with GitHub (recommended)
3. Verify your email

### Step 2: Deploy from GitHub
1. Click **"New Project"** → **"Deploy from GitHub repo"**
2. Connect your GitHub account
3. Select your `Capstone` repository
4. Click **"Deploy"**

### Step 3: Automatic Setup
Railway will automatically:
- ✅ Detect PHP application
- ✅ Provision MySQL database
- ✅ Set up environment variables
- ✅ Configure PHP runtime
- ✅ Deploy your application

### Step 4: Database Setup
After deployment, run the database setup:

1. Open Railway project dashboard
2. Go to **"Variables"** tab
3. Add these environment variables:
```
DB_HOST=${{ MYSQLHOST }}
DB_PORT=${{ MYSQLPORT }}
DB_NAME=${{ MYSQLDATABASE }}
DB_USER=${{ MYSQLUSER }}
DB_PASS=${{ MYSQLPASSWORD }}
```

4. Go to Railway **terminal** and run:
```bash
php database/schema.sql
php database/seed.sql
php database/additional_dummy_data.sql
php database/trainer_bookings_dummy_data.sql
php database/trainer_messages_dummy_data.sql
php database/customer_files_dummy_data.sql
```

### Step 5: Access Your Site
- **Production URL**: Provided by Railway (e.g., `https://l9-fitness.up.railway.app`)
- **Admin Access**: `https://your-url.up.railway.app/create_admin.php`

## 🔧 Configuration

### Environment Variables
Railway automatically provides database credentials. Your `config/db.php` should use:

```php
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: 3306;
$dbname = getenv('MYSQLDATABASE') ?: 'l9_gym';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
```

### Custom Domain (Optional)
1. Go to **"Settings"** → **"Domains"**
2. Add your custom domain
3. Update DNS records

## 📊 Railway Features for L9 Fitness

### Database Management
- **Automatic backups** (daily)
- **Database monitoring**
- **Query performance insights**
- **Easy scaling**

### Deployment Features
- **GitHub integration** - Auto-deploy on push
- **Environment variables** - Secure configuration
- **Build logs** - Debug deployment issues
- **Rollback** - Easy version management

### Scaling & Performance
- **Horizontal scaling** - Add more instances
- **Database scaling** - Upgrade MySQL resources
- **CDN integration** - Faster global delivery
- **Monitoring** - Performance metrics

## 🛠️ Troubleshooting

### Common Issues:

**1. Database Connection Failed**
```bash
# Check environment variables in Railway dashboard
# Ensure DB credentials are set correctly
```

**2. PHP Errors**
```bash
# Check Railway build logs
# Verify PHP version compatibility
```

**3. File Permissions**
```bash
# Railway handles permissions automatically
# No manual chmod needed
```

## 💰 Pricing

### Free Tier (Perfect for testing)
- 512MB RAM
- 1GB storage
- 1GB bandwidth/month
- MySQL included

### Hobby Plan ($5/month)
- 1GB RAM
- 5GB storage
- 10GB bandwidth/month
- Perfect for small gym

### Pro Plans (Scale as needed)
- More resources as your gym grows
- Advanced monitoring
- Priority support

## 🎯 Next Steps

1. **Deploy to Railway** (5 minutes)
2. **Run database setup** scripts
3. **Create admin account**
4. **Test all features**
5. **Go live!**

## 📞 Support

- **Railway Docs**: https://docs.railway.app/
- **PHP Deployment**: https://docs.railway.app/deploy/php
- **Database Setup**: https://docs.railway.app/databases/mysql

---

**Ready to deploy?** Railway makes it incredibly easy for PHP applications! 🚀