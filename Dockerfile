FROM php:8.4-apache

# Installation de GD
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install gd \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP
RUN docker-php-ext-install pdo pdo_mysql

# Configuration du projet
COPY src/ /var/www/html/
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Création du dossier uploads et configuration des permissions
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod 755 /var/www/html/uploads

# Installation de msmtp
RUN apt-get update && apt-get install -y --no-install-recommends msmtp-mta \
    && rm -rf /var/lib/apt/lists/*

COPY docker/msmtprc /etc/msmtprc

RUN chmod 644 /etc/msmtprc && printf '%s\n' 'sendmail_path = "/usr/sbin/sendmail -C /etc/msmtprc -t -i"' > /usr/local/etc/php/conf.d/mail.ini

# Activation de mod_rewrite
RUN a2enmod rewrite

EXPOSE 80
