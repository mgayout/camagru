FROM php:8.4-apache

RUN docker-php-ext-install pdo pdo_mysql

COPY src/ /var/www/html/
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN apt-get update && apt-get install -y --no-install-recommends msmtp-mta && rm -rf /var/lib/apt/lists/*

COPY docker/msmtprc /etc/msmtprc

RUN chmod 644 /etc/msmtprc && printf '%s\n' 'sendmail_path = "/usr/sbin/sendmail -C /etc/msmtprc -t -i"' > /usr/local/etc/php/conf.d/mail.ini

RUN a2enmod rewrite

EXPOSE 80