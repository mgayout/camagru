FROM php:8.4-apache

RUN docker-php-ext-install pdo pdo_mysql

COPY src/ /var/www/html/
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

EXPOSE 80