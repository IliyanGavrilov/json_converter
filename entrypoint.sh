#!/bin/bash
set -e

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    < /var/www/html/json_converter/sql/setup.sql \
    || echo "DB setup skipped (tables may already exist)"

exec apache2-foreground
