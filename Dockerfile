FROM php:8.3-apache

# NewsDesk needs MySQL PDO and mbstring; fileinfo/session are included in PHP.
RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod headers
COPY . /var/www/html/

# The only web-accessible folder is public/, keeping application code private.
RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\nOptions -Indexes\nAllowOverride All\nRequire all granted\n</Directory>\n' > /etc/apache2/conf-available/newsdesk.conf \
    && a2enconf newsdesk \
    && rm -f /var/www/html/public/check.php \
    && chown -R www-data:www-data /var/www/html/public/uploads

EXPOSE 10000
COPY docker-start.sh /usr/local/bin/docker-start.sh
RUN chmod +x /usr/local/bin/docker-start.sh
CMD ["docker-start.sh"]
