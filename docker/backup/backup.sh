#!/bin/bash
set -eo pipefail

# Source environment variables if running in cron
if [ -f /etc/backup.env ]; then
    # shellcheck disable=SC1091
    source /etc/backup.env
fi

MYSQL_HOST="${MYSQL_HOST:-database}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_DATABASE="${MYSQL_DATABASE:-recipes}"
MYSQL_USER="${MYSQL_USER:-root}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-password}"
BACKUP_DIR="${BACKUP_DIR:-/backups}"
IMAGES_DIR="${IMAGES_DIR:-/images}"
BACKUP_RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-7}"

TIMESTAMP="$(date +%Y-%m-%d_%H%M%S)"
ARCHIVE_NAME="recipes_backup_${TIMESTAMP}.zip"
TEMP_DIR="$(mktemp -d /tmp/recipe_backup_XXXXXX)"

cleanup() {
    rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

echo "[$(date -Iseconds)] [INFO] Starting backup: database='${MYSQL_DATABASE}', images='${IMAGES_DIR}'..."

mkdir -p "$BACKUP_DIR"

# Wait for MySQL to become ready
export MYSQL_PWD="$MYSQL_PASSWORD"
RETRIES=30
until mysqladmin ping -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" --silent > /dev/null 2>&1; do
    RETRIES=$((RETRIES - 1))
    if [ "$RETRIES" -le 0 ]; then
        echo "[$(date -Iseconds)] [ERROR] MySQL is not reachable at ${MYSQL_HOST}:${MYSQL_PORT}." >&2
        exit 1
    fi
    echo "[$(date -Iseconds)] [INFO] Waiting for MySQL (${RETRIES} retries left)..."
    sleep 2
done

# Dump MySQL database
echo "[$(date -Iseconds)] [INFO] Exporting database '${MYSQL_DATABASE}'..."
mysqldump -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    "$MYSQL_DATABASE" > "$TEMP_DIR/database.sql"

if [ ! -s "$TEMP_DIR/database.sql" ]; then
    echo "[$(date -Iseconds)] [ERROR] Database dump is empty or failed." >&2
    exit 1
fi
echo "[$(date -Iseconds)] [INFO] Database dump created successfully."

# Save standalone compressed SQL dump in db/ directory
mkdir -p "$BACKUP_DIR/db"
gzip -c "$TEMP_DIR/database.sql" > "$BACKUP_DIR/db/database_${TIMESTAMP}.sql.gz"
cp -f "$BACKUP_DIR/db/database_${TIMESTAMP}.sql.gz" "$BACKUP_DIR/db/database_latest.sql.gz"

# Stage images
mkdir -p "$TEMP_DIR/images"
if [ -d "$IMAGES_DIR" ] && [ "$(ls -A "$IMAGES_DIR" 2>/dev/null)" ]; then
    echo "[$(date -Iseconds)] [INFO] Packaging images from '${IMAGES_DIR}'..."
    cp -r "$IMAGES_DIR/." "$TEMP_DIR/images/"
else
    echo "[$(date -Iseconds)] [INFO] No images found in '${IMAGES_DIR}'. Packaging empty images folder."
fi

# Create single zip containing database.sql and images/
echo "[$(date -Iseconds)] [INFO] Creating combined backup archive '${ARCHIVE_NAME}'..."
(
    cd "$TEMP_DIR"
    zip -r -q "$BACKUP_DIR/$ARCHIVE_NAME" database.sql images
)

if [ ! -f "$BACKUP_DIR/$ARCHIVE_NAME" ]; then
    echo "[$(date -Iseconds)] [ERROR] Failed to generate backup zip archive." >&2
    exit 1
fi

# Copy to latest.zip and recipes_backup_latest.zip for easy remote fetch
cp -f "$BACKUP_DIR/$ARCHIVE_NAME" "$BACKUP_DIR/recipes_backup_latest.zip"
cp -f "$BACKUP_DIR/$ARCHIVE_NAME" "$BACKUP_DIR/latest.zip"

# Ensure permissions allow remote download by non-root users
chmod -R a+r "$BACKUP_DIR"

echo "[$(date -Iseconds)] [INFO] Created backup: $BACKUP_DIR/$ARCHIVE_NAME"
echo "[$(date -Iseconds)] [INFO] Updated: $BACKUP_DIR/latest.zip"

# Retention policy: remove backups older than specified days
if [ "$BACKUP_RETENTION_DAYS" -gt 0 ] 2>/dev/null; then
    find "$BACKUP_DIR" -maxdepth 1 -name "recipes_backup_*.zip" -type f -mtime +"$BACKUP_RETENTION_DAYS" -exec rm -f {} + 2>/dev/null || true
    find "$BACKUP_DIR/db" -maxdepth 1 -name "database_*.sql.gz" -type f -mtime +"$BACKUP_RETENTION_DAYS" -exec rm -f {} + 2>/dev/null || true
fi

echo "[$(date -Iseconds)] [SUCCESS] Backup completed successfully."
