# 🚀 Deploy to Render.com (100% FREE!)

## 🎯 Why Render.com?

✅ **Completely FREE** - No credit card required
✅ **Free PostgreSQL** database included  
✅ **Free SSL/HTTPS** certificate  
✅ **Automatic deployments** from GitHub  
✅ **750 hours/month** free (enough for 24/7)  
✅ **100GB bandwidth** per month  

---

## ⚡ Quick Deploy (5 Minutes)

### Step 1: Push to GitHub
```bash
git add .
git commit -m "Ready for Render deployment"
git push origin main
```

### Step 2: Create Render Account
1. Go to **https://render.com**
2. Click **"Get Started for Free"**
3. Sign up with **GitHub** account

### Step 3: Create Web Service
1. Click **"New +"** → **"Web Service"**
2. Connect your GitHub repository: **`Fantasy-Premier-League`**
3. Render will detect it's a PHP app automatically

### Step 4: Configure Service
Fill in these details:

```
Name: fantasy-premier-league
Region: Oregon (US West)
Branch: main
Runtime: PHP
Build Command: composer install --no-dev --optimize-autoloader && npm install && npm run build && php artisan config:clear
Start Command: php artisan migrate --force && php artisan config:cache && php artisan serve --host=0.0.0.0 --port=$PORT
Plan: Free
```

### Step 5: Add PostgreSQL Database
1. Click **"New +"** → **"PostgreSQL"**
2. Name: **fpl-database**
3. Region: **Oregon (US West)**
4. Plan: **Free**
5. Click **"Create Database"**

### Step 6: Connect Database to Web Service
1. Go back to your Web Service
2. Click **"Environment"** tab
3. Add these environment variables:

```env
APP_NAME=Fantasy Premier League
APP_ENV=production
APP_DEBUG=false
APP_KEY=[Generate with: php artisan key:generate --show]
LOG_LEVEL=error
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
DB_CONNECTION=pgsql
```

4. Click **"Add from Database"** and select your PostgreSQL database
   - This auto-adds: DATABASE_URL, DATABASE_HOST, DATABASE_PORT, etc.

### Step 7: Deploy!
Click **"Create Web Service"** and Render will:
- ✅ Install PHP and dependencies
- ✅ Build frontend assets
- ✅ Run migrations
- ✅ Start your app

**Done!** Your app will be live at: `https://fantasy-premier-league.onrender.com`

---

## 🔑 Generate APP_KEY

Run this command locally:
```bash
php artisan key:generate --show
```

Copy the output (e.g., `base64:xxx...`) and add it to Render environment variables.

---

## 🎉 What You Get for FREE

✅ **Web Service** (Laravel app)
- 512 MB RAM
- 0.1 CPU
- 750 hours/month (24/7 for 31 days!)
- Auto-deploys from GitHub

✅ **PostgreSQL Database**
- 256 MB RAM
- 1 GB storage
- 90 days of backups
- Full SQL database

✅ **Extras**
- Free SSL certificate (HTTPS)
- Custom domain support
- Automatic health checks
- Build logs and monitoring

---

## 🔄 Automatic Deployments

Every time you push to GitHub, Render automatically rebuilds and redeploys:

```bash
git add .
git commit -m "New features"
git push origin main
```

Render detects the push and redeploys! 🚀

---

## 📊 Free Tier Limits

- **750 hours/month** per service (enough for 24/7)
- **100 GB bandwidth/month**
- **Services spin down after 15 min of inactivity** (first request wakes it up in ~30 seconds)
- **PostgreSQL: 1 GB storage, 256 MB RAM**

**Note**: Free services "sleep" after inactivity, but wake up automatically on first request!

---

## 🐛 Troubleshooting

### Build Fails?
Check the build logs in Render dashboard:
- Ensure `composer.lock` is committed
- Ensure `package-lock.json` is committed

### Database Connection Error?
- Verify PostgreSQL service is running
- Check environment variables are set
- Use `DB_CONNECTION=pgsql` not `mysql`

### 500 Error?
```bash
# Temporarily set in Render dashboard
APP_DEBUG=true
```
Check logs for error details.

### Assets Not Loading?
- Ensure `npm run build` succeeded
- Check `APP_URL` matches your Render URL
- Clear browser cache

---

## 💡 Pro Tips

### Keep Service Awake
Free services sleep after 15 min. To keep it awake:
1. Use a free monitoring service like [UptimeRobot](https://uptimerobot.com)
2. Ping your app every 10 minutes

### Custom Domain
1. Render dashboard → Your service → **"Settings"**
2. Scroll to **"Custom Domain"**
3. Add your domain and update DNS

### View Logs
- Render dashboard → Your service → **"Logs"** tab
- Real-time logs of your application

---

## 📚 Additional Resources

- **Render Docs**: https://render.com/docs
- **Render Community**: https://community.render.com
- **Laravel Docs**: https://laravel.com/docs

---

## ✅ Quick Checklist

- [ ] Code pushed to GitHub
- [ ] Render account created
- [ ] Web Service created
- [ ] PostgreSQL database created
- [ ] Database connected to web service
- [ ] Environment variables set
- [ ] APP_KEY generated
- [ ] Build successful
- [ ] App accessible

---

## 🎉 You're Done!

Your Fantasy Premier League app is now live on Render for **FREE**!

**Your URL**: `https://fantasy-premier-league.onrender.com`

Share it with friends and enjoy! ⚽🏆

---

<p align="center"><strong>Total Cost: $0.00 / month</strong> 💰</p>
<p align="center">No credit card required! 🎉</p>
