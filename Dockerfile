FROM php:8.3-apache

RUN docker-php-ext-install mysqli

RUN apt-get update && apt-get install -y default-mysql-client && rm -rf /var/lib/apt/lists/*

RUN echo "PassEnv DB_HOST DB_USER DB_PASS DB_NAME APP_URL" >> /etc/apache2/apache2.conf

COPY . /var/www/html/json_converter/

RUN chown -R www-data:www-data /var/www/html

RUN printf '%s\n' \
    '#!/bin/bash' \
    'set -e' \
    'DBHOST="${MARIADB_HOST:-$DB_HOST}"' \
    'DBUSER="${MARIADB_USER:-$DB_USER}"' \
    'DBPASS="${MARIADB_PASSWORD:-$DB_PASS}"' \
    'DBNAME="${MARIADB_DATABASE:-$DB_NAME}"' \
    'mysql -h "$DBHOST" -u "$DBUSER" -p"$DBPASS" "$DBNAME" < /var/www/html/json_converter/sql/setup.sql 2>/dev/null || true' \
    'exec apache2-foreground' \
    > /entrypoint.sh && chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
