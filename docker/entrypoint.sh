#!/bin/bash
set -e

# Set Nginx to listen on Railway's PORT or default to 80
PORT=${PORT:-80}
sed -i "s/__PORT__/${PORT}/" /etc/nginx/sites-available/default
echo "==> Nginx listening on port ${PORT}"

# Railway MySQL plugin provides these variables directly
if [ -n "$MYSQLHOST" ]; then
    echo "==> Using Railway MySQL plugin variables..."
    export DB_HOST="$MYSQLHOST"
    export DB_PORT="$MYSQLPORT"
    export DB_DATABASE="$MYSQLDATABASE"
    export DB_USERNAME="$MYSQLUSER"
    export DB_PASSWORD="$MYSQLPASSWORD"
elif [ -n "$DATABASE_URL" ]; then
    echo "==> Parsing DATABASE_URL..."
    eval $(php -r "
        \$url = parse_url('$DATABASE_URL');
        echo 'export DB_HOST=' . \$url['host'] . PHP_EOL;
        echo 'export DB_PORT=' . (\$url['port'] ?? 3306) . PHP_EOL;
        echo 'export DB_DATABASE=' . ltrim(\$url['path'] ?? '/railway', '/') . PHP_EOL;
        echo 'export DB_USERNAME=' . (\$url['user'] ?? 'root') . PHP_EOL;
        echo 'export DB_PASSWORD=' . (\$url['pass'] ?? '') . PHP_EOL;
    ")
fi

# Create .env file if it doesn't exist (Railway provides env vars directly, not via file)
if [ ! -f /var/www/html/.env ]; then
    echo "==> Creating .env file from environment variables..."
    touch /var/www/html/.env
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

# Remove storage symlink/directory so nginx falls through to PHP route for file serving
rm -rf /var/www/html/public/storage

# Restore COT plan files into storage volume if missing
if [ ! -f /var/www/html/storage/cot_plan/cot_plan_index.json ]; then
    echo "==> Restoring COT plan files to storage volume..."
    mkdir -p /var/www/html/storage/cot_plan
    cp -r /cot_plan_seed/* /var/www/html/storage/cot_plan/
fi

# Ensure storage directories exist (Railway volume may start empty)
mkdir -p /var/www/html/storage/app/public/cargo_pictures
mkdir -p /var/www/html/storage/app/public/cot_plans
mkdir -p /var/www/html/storage/logs

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
php artisan route:clear
php artisan route:cache || echo "Warning: route:cache failed (closure routes present), running without cache"
php artisan view:cache

echo "==> Application ready. Starting services..."
exec "$@"
