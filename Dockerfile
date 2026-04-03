FROM node:24-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js ./
RUN npm run build


FROM php:8.4-apache AS app

WORKDIR /var/www/html

ENV APACHE_DOCUMENT_ROOT=/var/www/html/webroot

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev \
    && docker-php-ext-install intl pdo_mysql zip \
    && a2enmod rewrite headers expires \
    && sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY . .
COPY --from=assets /app/webroot/ ./webroot/

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

RUN mkdir -p logs tmp tmp/cache/models tmp/cache/persistent tmp/cache/views tmp/sessions tmp/tests \
    && chown -R www-data:www-data logs tmp \
    && chmod -R 775 logs tmp

EXPOSE 80

CMD ["apache2-foreground"]
