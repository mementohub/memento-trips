#!/bin/bash
set -e

echo "🚀 Starting deployment..."

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install and build frontend assets
npm ci
npm run build

# Generate APP_KEY if not set
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force
    echo "✅ APP_KEY generated"
fi

# Storage link (ignore if already exists)
php artisan storage:link 2>/dev/null || true

# Fix permissions
chmod -R 775 storage bootstrap/cache

# Cache config, routes, views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

echo "✅ Deploy complete!"
