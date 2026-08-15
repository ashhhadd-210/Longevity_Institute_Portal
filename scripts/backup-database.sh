#!/usr/bin/env bash
#
# Nightly logical backup, independent of the DigitalOcean managed backups
# (doc 07 §5): provider-portability applies to data too. Install on the
# application server via cron (docs/RUNBOOK.md):
#
#   0 3 * * * /var/www/portal/scripts/backup-database.sh >> /var/log/portal-backup.log 2>&1
#
# Requires: postgresql-client, s3cmd configured for the Institute's Spaces
# bucket, and DATABASE_URL plus BACKUP_BUCKET in /etc/portal-backup.env.

set -euo pipefail

source /etc/portal-backup.env   # DATABASE_URL, BACKUP_BUCKET

STAMP="$(date -u +%Y%m%d-%H%M%S)"
FILE="/tmp/portal-${STAMP}.dump"

pg_dump --format=custom --no-owner --file="$FILE" "$DATABASE_URL"

s3cmd put "$FILE" "s3://${BACKUP_BUCKET}/database/portal-${STAMP}.dump"
rm -f "$FILE"

# Retain 30 days of nightly dumps.
CUTOFF="$(date -u -d '30 days ago' +%Y%m%d)"
s3cmd ls "s3://${BACKUP_BUCKET}/database/" | while read -r _ _ _ key; do
    name="$(basename "$key")"
    stamp="${name#portal-}"
    stamp="${stamp%%-*}"
    if [[ "$stamp" =~ ^[0-9]{8}$ ]] && [[ "$stamp" < "$CUTOFF" ]]; then
        s3cmd del "$key"
    fi
done

echo "[$(date -u '+%F %T')] backup portal-${STAMP}.dump complete"
