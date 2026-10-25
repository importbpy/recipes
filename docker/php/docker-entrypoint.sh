#!/bin/sh
set -e

# Pre-create required writable directories
mkdir -p /var/www/recipes/public/images/original \
         /var/www/recipes/public/images/small \
         /var/www/recipes/var/cache \
         /var/www/recipes/var/log

# Set appropriate permissions
chown -R www-data:www-data /var/www/recipes/public/images /var/www/recipes/var 2>/dev/null || true
chmod -R a+rw /var/www/recipes/public/images /var/www/recipes/var 2>/dev/null || true

# Install dependencies if vendor directory is missing
if [ ! -f "/var/www/recipes/vendor/autoload.php" ]; then
    echo "[INFO] Vendor directory missing. Running composer install..."
    composer install --prefer-dist --no-progress --no-interaction
fi

# If command is php-fpm or running console migrations
if [ "$1" = "php-fpm" ] || [ "$#" -eq 0 ]; then
    # Wait for database connection if DATABASE_URL or database host is configured
    echo "[INFO] Checking database connection..."
    MAX_TRIES=30
    TRIES=0
    until php -r '
        $dbUrl = getenv("DATABASE_URL") ?: "";
        $url = parse_url($dbUrl);
        $host = $url["host"] ?? getenv("MYSQL_HOST") ?? "database";
        $port = $url["port"] ?? 3306;
        $fp = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($fp) { fclose($fp); exit(0); }
        exit(1);
    ' 2>/dev/null; do
        TRIES=$((TRIES + 1))
        if [ "$TRIES" -ge "$MAX_TRIES" ]; then
            echo "[WARN] Database not reachable after $MAX_TRIES attempts. Proceeding anyway..."
            break
        fi
        sleep 2
    done

    # Run database migrations
    if [ -f "/var/www/recipes/bin/console" ]; then
        echo "[INFO] Running database migrations..."
        php /var/www/recipes/bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || echo "[WARN] Migrations skipped or failed."
        
        # Cache warmup for production environment
        if [ "${APP_ENV:-prod}" = "prod" ]; then
            echo "[INFO] Clearing and warming up cache for production..."
            php /var/www/recipes/bin/console cache:clear --no-debug 2>/dev/null || true
            php /var/www/recipes/bin/console cache:warmup --no-debug 2>/dev/null || true
        fi
    fi
fi

exec "$@"
