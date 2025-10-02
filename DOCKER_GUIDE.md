# 🐳 Docker Setup for Fantasy Premier League

This project includes Docker configuration for easy deployment on Render.com and local development.

## 📁 Docker Files Created

- **`Dockerfile`** - Production Docker image with PHP 8.2, Apache, PostgreSQL support
- **`docker-compose.yml`** - Local development environment with PostgreSQL
- **`docker/apache.conf`** - Apache virtual host configuration
- **`.dockerignore`** - Files to exclude from Docker builds

---

## 🚀 Deploy to Render.com (FREE)

### Quick Steps:

1. **Push to GitHub**:
   ```bash
   git add .
   git commit -m "Add Docker configuration for Render"
   git push origin main
   ```

2. **Go to Render.com**:
   - Visit https://render.com
   - Sign up with GitHub (FREE, no credit card!)

3. **Create Web Service**:
   - Click "New +" → "Web Service"
   - Connect your `Fantasy-Premier-League` repo
   - **Language**: Select **"Docker"**
   - Leave Build Command and Start Command empty
   - Plan: **Free**

4. **Create PostgreSQL Database**:
   - Click "New +" → "PostgreSQL"
   - Name: `fpl-database`
   - Region: Oregon
   - Plan: **Free**

5. **Connect Database**:
   - Go to your Web Service → Environment
   - Click "Add from Database" → Select your PostgreSQL
   - This auto-adds DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

6. **Add Environment Variables**:
   ```
   APP_NAME=Fantasy Premier League
   APP_ENV=production
   APP_KEY=[Run: php artisan key:generate --show]
   APP_DEBUG=false
   DB_CONNECTION=pgsql
   SESSION_DRIVER=database
   CACHE_STORE=database
   LOG_LEVEL=error
   ```

7. **Deploy!**
   - Render will automatically build Docker image and deploy

**Your app will be live at**: `https://fantasy-premier-league.onrender.com` 🎉

---

## 💻 Local Development with Docker

### Option 1: Using Docker Compose (Recommended)

Run the entire stack (Laravel + PostgreSQL) with one command:

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

Your app will be available at: http://localhost:8000

### Option 2: Build and Run Manually

```bash
# Build the image
docker build -t fpl-app .

# Run the container
docker run -p 8000:80 --env-file .env fpl-app
```

---

## 🔧 Docker Commands Cheat Sheet

### View Running Containers
```bash
docker ps
```

### View Logs
```bash
# All logs
docker-compose logs

# Follow logs (live)
docker-compose logs -f

# Specific service
docker-compose logs app
```

### Execute Commands Inside Container
```bash
# Run artisan commands
docker-compose exec app php artisan migrate

# Clear cache
docker-compose exec app php artisan cache:clear

# Access bash shell
docker-compose exec app bash
```

### Restart Services
```bash
docker-compose restart
```

### Stop and Remove Everything
```bash
docker-compose down -v
```

---

## 📊 What's Included in Docker Image?

- ✅ PHP 8.2 with Apache
- ✅ PostgreSQL PDO extension
- ✅ Composer (for PHP dependencies)
- ✅ Node.js & NPM (for frontend assets)
- ✅ All required PHP extensions (GD, BCMath, etc.)
- ✅ Automatic migrations on startup
- ✅ Cached routes, views, and config

---

## 🐛 Troubleshooting

### Container Won't Start?
```bash
# Check logs
docker-compose logs app

# Rebuild image
docker-compose build --no-cache
docker-compose up -d
```

### Database Connection Issues?
```bash
# Check database is running
docker-compose ps

# Verify connection
docker-compose exec app php artisan migrate:status
```

### Permission Errors?
```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Need to Reset Everything?
```bash
# Stop and remove everything
docker-compose down -v

# Remove images
docker rmi $(docker images -q fpl-app)

# Start fresh
docker-compose up -d --build
```

---

## 🎯 Production vs Development

### Development (docker-compose.yml)
- Uses mounted volumes (live code changes)
- APP_DEBUG=true
- Local PostgreSQL container
- Accessible at localhost:8000

### Production (Render.com)
- Uses Dockerfile only
- APP_DEBUG=false
- Managed PostgreSQL database
- Automatic HTTPS
- Auto-deploys from GitHub

---

## 📚 Additional Resources

- **Docker Documentation**: https://docs.docker.com
- **Docker Compose Reference**: https://docs.docker.com/compose/
- **Laravel with Docker**: https://laravel.com/docs/sail
- **Render Docker Deployment**: https://render.com/docs/docker

---

## ✅ Quick Checklist

Before deploying:
- [ ] Docker files committed to Git
- [ ] .dockerignore properly configured
- [ ] Environment variables ready
- [ ] APP_KEY generated
- [ ] Database migrations tested
- [ ] Assets built successfully

---

**Ready to Deploy? Run `deploy-to-render.bat` and follow the guide!** 🚀
