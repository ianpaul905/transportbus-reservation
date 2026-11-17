FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype-dev \
        libz-dev libicu-dev icu-devtools libxml2-dev libsqlite3-dev \
        git unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql gd zip intl pdo_sqlite bcmath opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_NAME="TransportBus Gabon" \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_LOCALE=fr \
    APP_FAKER_LOCALE=fr_FR \
    DB_CONNECTION=pgsql \
    LOG_CHANNEL=stderr \
    SESSION_DRIVER=database \
    QUEUE_CONNECTION=sync \
    CACHE_STORE=database \
    DOCUMENTS_PATH=/tmp/transportbus-documents

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

RUN npm ci --no-audit --no-fund && npm run build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && a2enmod rewrite headers

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2dissite 000-default && a2ensite 000-default

EXPOSE 80

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
