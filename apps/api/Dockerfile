FROM php:8.4-cli-alpine AS base

RUN apk add --no-cache libpq-dev sqlite-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql pdo_sqlite pcntl \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts

COPY . .
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
