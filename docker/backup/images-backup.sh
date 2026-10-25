#!/bin/sh
set -eu

BACKUP_DIR="${BACKUP_DIR:-/backups}"
IMAGES_DIR="${IMAGES_DIR:-/images}"
BACKUP_RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-7}"

TIMESTAMP="$(date +%Y-%m-%d_%H%M%S)"
ARCHIVE="$BACKUP_DIR/images_${TIMESTAMP}.tar.gz"

mkdir -p "$BACKUP_DIR"
tar -czf "$ARCHIVE" -C "$(dirname "$IMAGES_DIR")" "$(basename "$IMAGES_DIR")"
cp -f "$ARCHIVE" "$BACKUP_DIR/images_latest.tar.gz"

find "$BACKUP_DIR" -maxdepth 1 -name 'images_*.tar.gz' ! -name 'images_latest.tar.gz' -mtime "+$BACKUP_RETENTION_DAYS" -delete

echo "[$(date -Iseconds)] Backed up images to $ARCHIVE"
