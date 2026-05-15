#!/bin/sh
set -e

echo "==> Starting AntriYuk initialization..."

# Create SQLite database if not exists
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "==> Creating SQLite database..."
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
fi

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "==> Generating application key..."
    php artisan key:generate --force
fi

# Cache config, routes, views
echo "==> Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Initialization complete. Starting services..."

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
