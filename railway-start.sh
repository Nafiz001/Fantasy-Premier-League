#!/bin/bash

echo "🚀 Starting Laravel Application..."
echo "=================================="

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Cache configuration for better performance
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP server
echo "✅ Starting PHP server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
