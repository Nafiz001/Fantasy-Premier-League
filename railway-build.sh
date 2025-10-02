#!/bin/bash

echo "🚀 Railway Deployment Script"
echo "=============================="

# Exit on error
set -e

echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "📦 Installing NPM dependencies..."
npm ci

echo "🏗️ Building frontend assets..."
npm run build

echo "🔧 Optimizing Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "✅ Build completed successfully!"
