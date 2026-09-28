#!/bin/sh
set -e

# Set dynamic port if PORT is provided by host (e.g. Render, Railway)
if [ -n "$PORT" ]; then
    sed -ri -e "s!Listen 80!Listen ${PORT}!g" /etc/apache2/ports.conf
    sed -ri -e "s!<VirtualHost \*:80>!<VirtualHost \*:${PORT}>!g" /etc/apache2/sites-available/*.conf
fi

# SQLite auto-creation if DB_CONNECTION is sqlite
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ ! -f "/var/www/html/database/database.sqlite" ]; then
        touch /var/www/html/database/database.sqlite
    fi
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
fi

# Cache configuration if APP_KEY is set
if [ -n "$APP_KEY" ]; then
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
fi

# Run migrations and seed/import if RUN_MIGRATIONS=true
if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force || true
    php artisan db:seed --force || true
    php artisan import:data-banjir || true
fi

exec "$@"
