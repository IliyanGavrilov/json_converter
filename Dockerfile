FROM php:8.3-apache

RUN docker-php-ext-install mysqli

# Pass Docker environment variables through to PHP scripts
RUN echo "PassEnv DB_HOST DB_USER DB_PASS DB_NAME APP_URL" >> /etc/apache2/apache2.conf

COPY . /var/www/html/json_converter/

RUN chown -R www-data:www-data /var/www/html
