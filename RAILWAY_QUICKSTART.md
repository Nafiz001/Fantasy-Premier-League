# 🎯 Railway Quick Start

## One-Time Setup (5 minutes)

### 1. Push to GitHub
```bash
git add .
git commit -m "Railway deployment setup"
git push origin main
```

### 2. Deploy to Railway
1. Go to **https://railway.app**
2. Click **"Start a New Project"**
3. Select **"Deploy from GitHub repo"**
4. Choose **`Fantasy-Premier-League`** repository
5. Click **"New Service"** → **"Database"** → **"Add MySQL"**

### 3. Set Environment Variables
Click your web service → **Variables** tab → **RAW Editor**:

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

### 4. Generate APP_KEY
Run locally:
```bash
php artisan key:generate --show
```
Copy output (e.g., `base64:xxx...`) and add as `APP_KEY` variable in Railway.

### 5. Update APP_URL
After first deployment, Railway gives you a URL. Update `APP_URL` with that URL.

## ✅ Done!
Your app will be live at: `https://your-app-name.up.railway.app`

---

## 🔄 Deploy Updates
Just push to GitHub:
```bash
git add .
git commit -m "Updates"
git push
```
Railway auto-deploys! 🚀

## 📊 Monitor
Railway Dashboard → **Logs** tab for real-time application logs

## 🐛 Troubleshoot
```bash
# Install Railway CLI
npm i -g @railway/cli

# Login and link
railway login
railway link

# View logs
railway logs

# Run commands
railway run php artisan migrate
railway run php artisan cache:clear
```

## 💰 Cost
- **$5 free monthly credit** (no card required)
- Typical usage: **$5-15/month**

---

**That's it!** 🎉 Your Laravel app is now deployed on Railway with automatic deployments from GitHub!
