# 🚀 Deploying to Railway.app

This guide will help you deploy your Fantasy Premier League Laravel application to Railway.app with GitHub integration.

## 📋 Prerequisites

1. A GitHub account
2. A Railway.app account (sign up at https://railway.app with GitHub)
3. This repository pushed to GitHub

## 🎯 Step-by-Step Deployment Guide

### Step 1: Push Your Code to GitHub

If you haven't already:

```bash
# Initialize git (if not already done)
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit for Railway deployment"

# Add your GitHub remote
git remote add origin https://github.com/Nafiz001/Fantasy-Premier-League.git

# Push to GitHub
git push -u origin main
```

### Step 2: Create a Railway Project

1. Go to https://railway.app
2. Click "Start a New Project"
3. Select "Deploy from GitHub repo"
4. Authorize Railway to access your GitHub account
5. Select your `Fantasy-Premier-League` repository

### Step 3: Add MySQL Database

1. In your Railway project, click "New Service"
2. Select "Database" → "Add MySQL"
3. Railway will automatically create a MySQL database and inject the credentials

### Step 4: Configure Environment Variables

Railway will auto-inject MySQL credentials, but you need to set:

1. In Railway dashboard, click on your web service
2. Go to "Variables" tab
3. Click "RAW Editor" and paste:

```env
APP_NAME="Fantasy Premier League"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.up.railway.app
LOG_LEVEL=error
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

4. Generate APP_KEY:
   - Click "New Variable"
   - Name: `APP_KEY`
   - Run locally: `php artisan key:generate --show`
   - Copy the output (e.g., `base64:xxx...`) and paste as value

### Step 5: Update APP_URL

After deployment:
1. Railway will give you a URL like `https://fantasy-premier-league-production-xxxx.up.railway.app`
2. Update the `APP_URL` variable in Railway with this URL

### Step 6: Deploy

Railway will automatically:
- ✅ Detect Laravel application
- ✅ Install Composer dependencies
- ✅ Build frontend assets with Vite
- ✅ Run database migrations
- ✅ Cache configurations
- ✅ Start the application

## 🔧 Configuration Files Added

The following files have been created for Railway deployment:

- **`railway.json`** - Railway service configuration
- **`nixpacks.toml`** - Build and deployment instructions
- **`Procfile`** - Alternative process definition
- **`.env.railway`** - Production environment template

## 📊 Monitoring Your Deployment

In Railway dashboard:
- **Deployments tab**: See build logs and deployment history
- **Metrics tab**: Monitor CPU, memory, and network usage
- **Logs tab**: View application logs in real-time

## 🔄 Automatic Deployments

Railway will automatically deploy when you push to GitHub:

```bash
git add .
git commit -m "Your changes"
git push origin main
```

Railway detects the push and redeploys automatically!

## 🗄️ Database Management

### Running Migrations Manually

If needed, you can run commands in Railway:

1. Go to your service → "Settings" tab
2. Find "Service Domains" section
3. Or use Railway CLI:

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link to your project
railway link

# Run migrations
railway run php artisan migrate

# Or any other command
railway run php artisan db:seed
```

### Accessing Database

In Railway dashboard:
1. Click on MySQL service
2. Go to "Data" tab to view tables
3. Or use "Connect" tab for connection details

## 🚨 Troubleshooting

### Build Fails

Check the deployment logs in Railway dashboard. Common issues:
- Missing PHP extensions: Already configured in `nixpacks.toml`
- Composer dependencies: Ensure `composer.lock` is committed
- Node/NPM issues: Check `package.json` scripts

### Database Connection Issues

- Ensure MySQL service is running
- Variables are auto-injected: `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`
- Check `.env.railway` for correct variable names

### 500 Error After Deployment

1. Set `APP_DEBUG=true` temporarily to see errors
2. Check logs: Railway dashboard → Logs tab
3. Common fixes:
   ```bash
   railway run php artisan config:clear
   railway run php artisan cache:clear
   railway run php artisan key:generate
   railway run php artisan migrate --force
   ```

### Static Assets Not Loading

If CSS/JS don't load:
1. Ensure `npm run build` succeeded in build logs
2. Check `APP_URL` is set correctly
3. Clear browser cache

## 🔐 Security Checklist

Before going live:

- ✅ Set `APP_DEBUG=false`
- ✅ Set `APP_ENV=production`
- ✅ Use strong `APP_KEY`
- ✅ Use strong database password
- ✅ Set proper `APP_URL`
- ✅ Review all environment variables

## 💰 Pricing

Railway offers:
- **$5 free credit per month** (no credit card required)
- **Pay-as-you-go** after free credit
- **~$5-20/month** for typical Laravel app + MySQL

## 📚 Additional Resources

- [Railway Documentation](https://docs.railway.app/)
- [Railway Laravel Template](https://railway.app/template/laravel)
- [Railway CLI](https://docs.railway.app/develop/cli)
- [Railway Community Discord](https://discord.gg/railway)

## 🎉 You're Done!

Your Fantasy Premier League app should now be live on Railway! Share your URL and enjoy! 🚀

## 🔄 Optional: Custom Domain

To use a custom domain:

1. Railway dashboard → Your service → "Settings"
2. Scroll to "Domains"
3. Click "Custom Domain"
4. Follow instructions to add DNS records
5. Update `APP_URL` environment variable

---

**Need Help?** Check Railway logs or reach out in their Discord community!
