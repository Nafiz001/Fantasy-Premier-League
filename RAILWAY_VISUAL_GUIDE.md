# 🎨 Visual Railway Deployment Guide

## 📸 Step-by-Step Screenshots Guide

This visual guide walks you through deploying your Fantasy Premier League app to Railway.app.

---

## Step 1: Sign Up / Login to Railway

1. Visit **https://railway.app**
2. Click **"Login"** or **"Start a New Project"**
3. Sign in with your **GitHub account**

```
┌─────────────────────────────────────┐
│         Railway.app                 │
│                                     │
│   [Login with GitHub]               │
│                                     │
│   Deploy your apps with ease        │
└─────────────────────────────────────┘
```

---

## Step 2: Create New Project

1. Click **"Start a New Project"**
2. Select **"Deploy from GitHub repo"**
3. Authorize Railway to access your GitHub repos

```
┌─────────────────────────────────────┐
│   New Project                       │
│                                     │
│   ○ Deploy from GitHub repo         │
│   ○ Deploy from template            │
│   ○ Empty project                   │
│                                     │
└─────────────────────────────────────┘
```

---

## Step 3: Select Your Repository

1. Search for **"Fantasy-Premier-League"**
2. Click on your repository
3. Railway will detect it's a Laravel app

```
┌─────────────────────────────────────┐
│   Select Repository                 │
│                                     │
│   🔍 Search: Fantasy-Premier-League │
│                                     │
│   ✓ Nafiz001/Fantasy-Premier-League│
│     Laravel 12 • Updated now        │
│                                     │
│   [Deploy Now]                      │
└─────────────────────────────────────┘
```

---

## Step 4: Add MySQL Database

1. In project dashboard, click **"New Service"**
2. Select **"Database"**
3. Choose **"Add MySQL"**
4. Railway creates database and injects credentials automatically

```
┌─────────────────────────────────────┐
│   Project: Fantasy-Premier-League   │
│                                     │
│   Services:                         │
│   ├─ 🚂 web (Laravel app)          │
│   └─ 🗄️ MySQL (database)          │
│                                     │
│   [+ New Service]                   │
└─────────────────────────────────────┘
```

---

## Step 5: Configure Environment Variables

1. Click on your **web service** (Laravel app)
2. Go to **"Variables"** tab
3. Click **"RAW Editor"**
4. Paste the following:

```env
APP_NAME="Fantasy Premier League"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.up.railway.app
LOG_LEVEL=error
SESSION_DRIVER=database
CACHE_STORE=database
```

5. Click **"Add Variable"** and add:
   - **Name**: `APP_KEY`
   - **Value**: Run `php artisan key:generate --show` locally, copy output

```
┌─────────────────────────────────────┐
│   Environment Variables             │
│                                     │
│   APP_NAME="Fantasy Premier League" │
│   APP_ENV=production                │
│   APP_DEBUG=false                   │
│   APP_KEY=base64:xxx...             │
│   APP_URL=https://...               │
│   LOG_LEVEL=error                   │
│                                     │
│   🗄️ MySQL variables (auto):       │
│   MYSQLHOST=containers-us...        │
│   MYSQLPORT=3306                    │
│   MYSQLDATABASE=railway             │
│   MYSQLUSER=root                    │
│   MYSQLPASSWORD=xxx...              │
└─────────────────────────────────────┘
```

---

## Step 6: Watch Build & Deploy

Railway automatically:
1. ✅ Detects Laravel project
2. ✅ Installs PHP 8.2 and extensions
3. ✅ Runs `composer install`
4. ✅ Runs `npm install && npm run build`
5. ✅ Runs database migrations
6. ✅ Starts application

```
┌─────────────────────────────────────┐
│   Deployment Logs                   │
│                                     │
│   ✓ Installing PHP 8.2              │
│   ✓ Installing dependencies         │
│   ✓ Building frontend assets        │
│   ✓ Running migrations              │
│   ✓ Starting application            │
│                                     │
│   🎉 Deployed successfully!         │
│   https://fantasy-premier-league... │
└─────────────────────────────────────┘
```

---

## Step 7: Get Your URL

1. Railway assigns a URL like:
   ```
   https://fantasy-premier-league-production-xxxx.up.railway.app
   ```

2. **IMPORTANT**: Copy this URL

3. Go back to **Variables** tab

4. Update **`APP_URL`** with your Railway URL

```
┌─────────────────────────────────────┐
│   Domains                           │
│                                     │
│   🌐 Production Domain:             │
│   fantasy-premier-league-prod...    │
│   .up.railway.app                   │
│                                     │
│   [+ Add Custom Domain]             │
└─────────────────────────────────────┘
```

---

## Step 8: Verify Deployment

Visit your Railway URL and check:

✅ Homepage loads
✅ Can register/login
✅ Squad selection works
✅ CSS/JS assets load
✅ League creation works

```
┌─────────────────────────────────────┐
│   🏠 Fantasy Premier League         │
│                                     │
│   Welcome to FPL Clone!             │
│                                     │
│   [Register]  [Login]               │
│                                     │
│   ⚽ Select Your Squad               │
│   🏆 Join Leagues                   │
│   📊 View Points                    │
└─────────────────────────────────────┘
```

---

## Step 9: Automatic Deployments

Every time you push to GitHub:

```bash
git add .
git commit -m "New feature"
git push origin main
```

Railway automatically:
1. 🔔 Detects push
2. 🏗️ Rebuilds app
3. 🚀 Deploys new version
4. ✅ Zero downtime!

```
┌─────────────────────────────────────┐
│   Deployments                       │
│                                     │
│   ● main branch (2 mins ago)        │
│     Status: ✓ Active                │
│     Commit: "New feature"           │
│                                     │
│   ○ main branch (1 hour ago)        │
│     Status: Superseded              │
│     Commit: "Initial deploy"        │
└─────────────────────────────────────┘
```

---

## 📊 Monitoring Your App

### View Logs
```
┌─────────────────────────────────────┐
│   Logs (Live)                       │
│                                     │
│   [INFO] Server started on :8000    │
│   [INFO] Migration complete         │
│   [INFO] Cache cleared              │
│   [INFO] Request: GET /             │
│   [INFO] Response: 200 OK           │
└─────────────────────────────────────┘
```

### View Metrics
```
┌─────────────────────────────────────┐
│   Metrics                           │
│                                     │
│   CPU Usage:     ▂▄▆█▆▄▂  (25%)    │
│   Memory:        ████░░░░  (128MB)  │
│   Requests:      ▂▄▆█ (1.2k/hr)    │
│   Response Time: 150ms avg          │
└─────────────────────────────────────┘
```

---

## 🎉 You're Live!

Your Fantasy Premier League app is now:

✅ Deployed on Railway
✅ Using MySQL database
✅ Automatically deploys on push
✅ Has SSL/HTTPS enabled
✅ Monitored and scalable

**Share your URL**: `https://your-app.up.railway.app`

---

## 🛠️ Common Actions

### Clear Cache
```bash
railway run php artisan cache:clear
railway run php artisan config:clear
railway run php artisan view:clear
```

### Run Migrations
```bash
railway run php artisan migrate
```

### Import FPL Data
```bash
railway run php artisan fpl:import-all
```

### View Database
1. Click **MySQL service**
2. Go to **"Data"** tab
3. Browse tables and data

---

## 💡 Pro Tips

1. **Use Railway CLI** for faster operations:
   ```bash
   npm i -g @railway/cli
   railway login
   railway link
   railway logs
   ```

2. **Set up custom domain** in Settings → Domains

3. **Enable auto-backups** (Railway Pro) for database

4. **Monitor costs** in Usage tab (usually $5-15/month)

5. **Use environment groups** for different stages (dev/prod)

---

## 🆘 Troubleshooting

### Build Fails?
- Check `composer.lock` is committed
- Check `package-lock.json` is committed
- Review build logs for specific errors

### 500 Error?
- Set `APP_DEBUG=true` temporarily
- Check logs in Railway dashboard
- Run `railway run php artisan config:clear`

### Database Connection Error?
- Verify MySQL service is running
- Check DB variables are auto-injected
- Restart web service if needed

### Assets Not Loading?
- Check `npm run build` succeeded
- Verify `APP_URL` is correct
- Clear browser cache

---

## 📚 Next Steps

1. ✅ **Add custom domain** (optional)
2. ✅ **Invite users** to test
3. ✅ **Import FPL data** for current season
4. ✅ **Create leagues** and share codes
5. ✅ **Monitor** logs and metrics

---

**Happy Deploying! 🚀⚽**

For more details, see:
- [RAILWAY_QUICKSTART.md](RAILWAY_QUICKSTART.md)
- [RAILWAY_DEPLOYMENT.md](RAILWAY_DEPLOYMENT.md)
- [DEPLOYMENT_SUMMARY.md](DEPLOYMENT_SUMMARY.md)
