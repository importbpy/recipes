FROM mysql:8.0.33

# Install zip, unzip, and cronie for scheduled backups
RUN microdnf install -y zip unzip cronie \
    && microdnf clean all

WORKDIR /backups

COPY backup.sh /backup.sh
COPY restore.sh /restore.sh
COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /backup.sh /restore.sh /entrypoint.sh

ENV MYSQL_HOST=database \
    MYSQL_PORT=3306 \
    MYSQL_DATABASE=recipes \
    MYSQL_USER=root \
    BACKUP_DIR=/backups \
    IMAGES_DIR=/images \
    CRON_SCHEDULE="0 2 * * *" \
    BACKUP_RETENTION_DAYS=7 \
    BACKUP_ON_START=true

ENTRYPOINT ["/entrypoint.sh"]
CMD ["crond", "-n", "-s"]
