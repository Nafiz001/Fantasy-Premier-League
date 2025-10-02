#!/usr/bin/env bash

echo "Building Fantasy Premier League..."

# Install Composer dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Install NPM dependencies
npm ci

# Build frontend assets
npm run build

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "Build complete!"
