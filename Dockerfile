# Starting point for the production image; adjust to your own Docker setup.
# The PHP 8.5 FrankenPHP image already has every extension the app needs.

FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
RUN npm ci
COPY assets assets
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-req=php
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative

FROM dunglas/frankenphp:1-php8.5
ENV APP_ENV=prod APP_DEBUG=0 SERVER_NAME=":80"
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
WORKDIR /app
COPY --from=vendor /app /app
COPY --from=assets /app/public/build /app/public/build
RUN php bin/console cache:warmup && chown -R www-data:www-data var
