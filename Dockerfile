FROM php:8.3-apache

RUN docker-php-ext-install mysqli

RUN apt-get update && apt-get install -y default-mysql-client && rm -rf /var/lib/apt/lists/*

RUN echo "PassEnv DB_HOST DB_USER DB_PASS DB_NAME APP_URL" >> /etc/apache2/apache2.conf

COPY . /var/www/html/json_converter/

RUN chown -R www-data:www-data /var/www/html

RUN printf '#!/bin/bash\nset -e\nmysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/json_converter/sql/setup.sql 2>/dev/null || true\nexec apache2-foreground\n' > /entrypoint.sh \
    && chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
