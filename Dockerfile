FROM dunglas/frankenphp:1-php8.5-trixie

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl git gnupg unzip \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_24.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && install-php-extensions intl pdo_mysql zip redis \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY . .
COPY Caddyfile /etc/frankenphp/Caddyfile

RUN npm ci \
    && npm run build

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

RUN DEBUG=false bin/cake plugin assets symlink

RUN mkdir -p logs tmp tmp/cache/models tmp/cache/persistent tmp/cache/views tmp/sessions tmp/tests /config/caddy /data/caddy \
    && chown -R www-data:www-data logs tmp /config/caddy /data/caddy \
    && chmod -R 775 logs tmp

USER www-data

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
