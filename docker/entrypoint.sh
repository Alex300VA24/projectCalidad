#!/bin/sh
set -e

cd /var/www/html

# Ensure storage permissions (bind mount may be owned by root/host)
mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Ensure .env exists so key:generate can write to it (image excludes .env via .dockerignore)
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
    chown www-data:www-data .env 2>/dev/null || true
fi

if [ -z "$(printenv APP_KEY)" ]; then
    if [ -f .env ]; then
        php artisan key:generate --force --ansi
    else
        # No .env file available (e.g. env vars only): generate key and export for this run
        GENERATED_KEY=$(php artisan key:generate --show 2>/dev/null)
        if [ -n "$GENERATED_KEY" ]; then
            export APP_KEY="$GENERATED_KEY"
            echo "Generated APP_KEY on the fly (no .env to persist): $GENERATED_KEY"
        fi
    fi
fi

php artisan storage:link --force || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
