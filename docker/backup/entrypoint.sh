#!/bin/bash
set -e

# Export relevant environment variables for cron
rm -f /etc/backup.env
for var in $(env | grep -E '^(MYSQL_|BACKUP_|IMAGES_|CRON_|PATH)' | cut -d= -f1); do
    printf 'export %s="%s"\n' "$var" "${!var}" >> /etc/backup.env
done
chmod 600 /etc/backup.env

CRON_SCHEDULE="${CRON_SCHEDULE:-0 2 * * *}"

echo "[INFO] Setting up cron job with schedule: '${CRON_SCHEDULE}'"

cat <<EOF > /etc/cron.d/recipe_backup
SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

${CRON_SCHEDULE} root /backup.sh >> /var/log/cron_backup.log 2>&1
# Line feed required by cron
EOF

chmod 644 /etc/cron.d/recipe_backup

# Run backup on startup if requested
if [ "${BACKUP_ON_START:-true}" = "true" ] || [ "${BACKUP_ON_START:-1}" = "1" ]; then
    echo "[INFO] Running initial backup on container start..."
    /backup.sh || echo "[WARN] Initial backup failed or database not ready yet."
fi

echo "[INFO] Starting cron daemon..."
exec "$@"
