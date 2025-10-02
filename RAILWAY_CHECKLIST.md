# 🚀 Railway Deployment Checklist

## ✅ Pre-Deployment Checklist

### Code Preparation
- [ ] All changes committed to Git
- [ ] `composer.lock` is committed
- [ ] `package-lock.json` is committed
- [ ] `.env.example` is up to date
- [ ] Code pushed to GitHub main branch

### Configuration Files (Already Created ✓)
- [x] `railway.json` - Railway configuration
- [x] `nixpacks.toml` - Build configuration
- [x] `Procfile` - Process definition
- [x] `.env.railway` - Environment template
- [x] `RAILWAY_DEPLOYMENT.md` - Full guide
- [x] `RAILWAY_QUICKSTART.md` - Quick reference

### Database
- [ ] Migrations are working locally
- [ ] Database schema is finalized
- [ ] Seeders are optional/safe for production

## 🎯 Deployment Steps

### 1. GitHub Setup
- [ ] Repository is public or Railway has access
- [ ] Main branch is the default branch
- [ ] All code is pushed

### 2. Railway Project Setup
- [ ] Created new Railway project
- [ ] Connected to GitHub repository
- [ ] Added MySQL database service
- [ ] Web service detected Laravel automatically

### 3. Environment Variables
- [ ] `APP_NAME` set
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generated and set
- [ ] `APP_URL` set (after first deploy, update with Railway URL)
- [ ] `LOG_LEVEL=error`
- [ ] Database variables auto-injected by Railway

### 4. First Deployment
- [ ] Build succeeded (check logs)
- [ ] Migrations ran successfully
- [ ] Application is accessible
- [ ] No 500 errors on homepage

### 5. Post-Deployment Verification
- [ ] Homepage loads correctly
- [ ] CSS/JS assets loading
- [ ] Can register/login
- [ ] Database operations working
- [ ] League creation working
- [ ] Points calculation working

## 🔧 Environment Variables to Set

Copy these to Railway → Variables → RAW Editor:

```env
APP_NAME="Fantasy Premier League"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.up.railway.app
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
LOG_LEVEL=error
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

## 🔑 Generate APP_KEY

Run locally and copy output:
```bash
php artisan key:generate --show
```

## 📊 Post-Deployment Tasks

### Immediate
- [ ] Update `APP_URL` with Railway's assigned URL
- [ ] Test user registration
- [ ] Test squad selection
- [ ] Test league creation
- [ ] Test points calculation

### Optional
- [ ] Set up custom domain
- [ ] Configure email service (if needed)
- [ ] Set up monitoring/alerts
- [ ] Schedule database backups

## 🚨 Common Issues & Solutions

### Build Fails
```bash
# Check that these are committed:
- composer.lock
- package-lock.json
- All required files
```

### 500 Error
```bash
# Temporarily enable debug
APP_DEBUG=true

# Check logs in Railway dashboard
# Clear caches
railway run php artisan config:clear
railway run php artisan cache:clear
```

### Database Connection Error
```bash
# Verify MySQL service is running
# Check that Railway auto-injected DB variables
# Verify .env uses correct variable names:
DB_HOST=${MYSQLHOST}
DB_PORT=${MYSQLPORT}
DB_DATABASE=${MYSQLDATABASE}
DB_USERNAME=${MYSQLUSER}
DB_PASSWORD=${MYSQLPASSWORD}
```

### Assets Not Loading
- Ensure `npm run build` succeeded in logs
- Check `APP_URL` is correct
- Clear browser cache
- Check `public/build` was created

## 💡 Tips

### Auto-Deployments
- Every push to main branch auto-deploys
- Railway shows build progress in real-time
- Can pause auto-deploy in settings if needed

### Database Backups
- Railway Pro includes automated backups
- Free tier: Use Railway CLI to export:
  ```bash
  railway connect
  mysqldump -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE > backup.sql
  ```

### Monitoring
- Railway dashboard shows metrics
- Set up log alerts for errors
- Monitor response times

### Scaling
- Railway auto-scales based on usage
- Can adjust resources in settings
- Upgrade to Pro for more resources

## 📞 Support

- **Railway Docs**: https://docs.railway.app
- **Railway Discord**: https://discord.gg/railway
- **Railway Status**: https://status.railway.app

## 🎉 Ready to Deploy!

Once all items are checked, you're ready to deploy! 🚀

**Next Step**: Follow the **RAILWAY_QUICKSTART.md** guide!
