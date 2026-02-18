#!/bin/bash
set -e

echo "🚀 Starting deployment..."

# Install PHP dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader

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

# Fix permissions BEFORE caching
chmod -R 775 storage bootstrap/cache

# Clear old caches first
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-cache (view:cache MUST come before route:cache to avoid routes-v7.php conflict)
php artisan config:cache
php artisan view:cache
php artisan route:cache

# Run migrations
php artisan migrate --force

echo "✅ Deploy complete!"
