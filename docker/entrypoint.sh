#!/bin/sh
set -e

cd /var/www/html

if [ -z "$(printenv APP_KEY)" ]; then
    php artisan key:generate --force --ansi
fi

php artisan storage:link --force || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
