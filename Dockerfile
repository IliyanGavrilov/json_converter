FROM php:8.3-apache

RUN docker-php-ext-install mysqli

RUN chown -R www-data:www-data /var/www/html
