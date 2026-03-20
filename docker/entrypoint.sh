#!/bin/bash
set -e

# Set Nginx to listen on Railway's PORT or default to 80
PORT=${PORT:-80}
sed -i "s/__PORT__/${PORT}/" /etc/nginx/sites-available/default
echo "==> Nginx listening on port ${PORT}"

# Parse DATABASE_URL if provided (Railway MySQL plugin)
if [ -n "$DATABASE_URL" ]; then
    echo "==> Parsing DATABASE_URL..."
    # DATABASE_URL format: mysql://user:password@host:port/database
    export DB_HOST=$(echo "$DATABASE_URL" | sed -n 's|.*@\(.*\):\([0-9]*\)/.*|\1|p')
    export DB_PORT=$(echo "$DATABASE_URL" | sed -n 's|.*@\(.*\):\([0-9]*\)/.*|\2|p')
    export DB_DATABASE=$(echo "$DATABASE_URL" | sed -n 's|.*/\([^?]*\).*|\1|p')
    export DB_USERNAME=$(echo "$DATABASE_URL" | sed -n 's|.*://\(.*\):.*@.*|\1|p')
    export DB_PASSWORD=$(echo "$DATABASE_URL" | sed -n 's|.*://[^:]*:\(.*\)@.*|\1|p')
fi

echo "==> Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD') ?: ''); echo 'ok'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
    sleep 2
done
echo "==> MySQL is ready."

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "==> Generating APP_KEY..."
    php artisan key:generate --force
fi

# Create storage link
php artisan storage:link --force 2>/dev/null || true

# Restore COT plan files into storage volume if missing
if [ ! -f /var/www/html/storage/cot_plan/cot_plan_index.json ]; then
    echo "==> Restoring COT plan files to storage volume..."
    mkdir -p /var/www/html/storage/cot_plan
    cp -r /cot_plan_seed/* /var/www/html/storage/cot_plan/
fi

# Ensure correct permissions on storage
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run migrations
echo "==> Running migrations..."
php artisan migrate --force

# Seed database if admin table is empty
ADMIN_COUNT=$(php artisan tinker --execute="echo \App\Models\Admin::count();" 2>/dev/null || echo "0")
if [ "$ADMIN_COUNT" = "0" ]; then
    echo "==> Seeding database..."
    php artisan db:seed --force
fi

# Cache configuration for performance
php artisan config:cache
php artisan route:cache || echo "Warning: route:cache failed (duplicate route names), skipping"
php artisan view:cache

echo "==> Application ready. Starting services..."
exec "$@"
