#!/bin/sh
set -e
cd /var/www/html

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY must be set (e.g. base64:... from php artisan key:generate --show)" >&2
  exit 1
fi

php artisan package:discover --ansi >/dev/null 2>&1 || true

if [ "$RUN_MIGRATIONS" = "true" ] || [ "$RUN_MIGRATIONS" = "1" ]; then
  php artisan migrate --force
  # Seeders are idempotent: they only add or update reference data, never duplicate it.
  php artisan db:seed --force
fi

if [ "$APP_ENV" = "production" ]; then
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
fi

# Artisan commands above may create files as root (entrypoint runs as root).
# Ensure runtime (www-data) can always write logs/cache.
touch storage/logs/laravel.log 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

# Run Laravel's scheduler in production (or when explicitly enabled).
# schedule:work invokes schedule:run every minute; baked into the image so deploys keep it.
if [ "$APP_ENV" = "production" ] || [ "$ENABLE_SCHEDULER" = "true" ] || [ "$ENABLE_SCHEDULER" = "1" ]; then
  cp /usr/local/etc/supervisor/laravel-scheduler.conf /etc/supervisor/conf.d/laravel-scheduler.program.conf
  echo "Laravel scheduler enabled (schedule:work)"
else
  rm -f /etc/supervisor/conf.d/laravel-scheduler.program.conf
  echo "Laravel scheduler disabled (APP_ENV=$APP_ENV)"
fi

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
