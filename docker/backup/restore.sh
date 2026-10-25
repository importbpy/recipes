#!/bin/bash
set -eo pipefail

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

BACKUP_FILE="${1:-$BACKUP_DIR/latest.zip}"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "[ERROR] Backup file '$BACKUP_FILE' does not exist!" >&2
    echo "Usage: /restore.sh [/path/to/recipes_backup_YYYY-MM-DD.zip]" >&2
    exit 1
fi

TEMP_DIR="$(mktemp -d /tmp/recipe_restore_XXXXXX)"
cleanup() {
    rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

echo "[$(date -Iseconds)] [INFO] Restoring from '$BACKUP_FILE'..."
unzip -q "$BACKUP_FILE" -d "$TEMP_DIR"

export MYSQL_PWD="$MYSQL_PASSWORD"

# Restore database
if [ -f "$TEMP_DIR/database.sql" ]; then
    echo "[$(date -Iseconds)] [INFO] Restoring database '${MYSQL_DATABASE}'..."
    mysql -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" "$MYSQL_DATABASE" < "$TEMP_DIR/database.sql"
    echo "[$(date -Iseconds)] [INFO] Database restored successfully."
else
    echo "[$(date -Iseconds)] [WARN] database.sql not found in backup archive."
fi

# Restore images
if [ -d "$TEMP_DIR/images" ]; then
    echo "[$(date -Iseconds)] [INFO] Restoring images to '${IMAGES_DIR}'..."
    mkdir -p "$IMAGES_DIR"
    cp -r "$TEMP_DIR/images/." "$IMAGES_DIR/"
    chmod -R a+rw "$IMAGES_DIR" 2>/dev/null || true
    echo "[$(date -Iseconds)] [INFO] Images restored successfully."
fi

echo "[$(date -Iseconds)] [SUCCESS] Restore completed successfully."
