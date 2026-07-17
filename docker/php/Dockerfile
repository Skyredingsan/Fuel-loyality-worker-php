# syntax=docker/dockerfile:1.8

# ─── Base ─────────────────────────────────────────────────
FROM php:8.5-fpm-alpine AS base

# Системные зависимости + минимальный набор PHP-расширений для Laravel 13
# В PHP 8.5-fpm-alpine уже встроены: pdo, opcache, zip, gd (нет), sockets (нет)
# Ставим только то, чего реально нет и нужно для проекта:
#   - pdo_pgsql  ← драйвер PostgreSQL
#   - bcmath     ← точная арифметика (баллы, вычисления)
#   - pcntl      ← нужен для queue:work (шаг 11)
RUN apk add --no-cache \
        postgresql-dev \
        libzip-dev \
        zip \
        unzip \
        git \
        curl \
        bash \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        bcmath \
        pcntl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/cache/apk/* /tmp/*

# ─── Development ──────────────────────────────────────────
FROM base AS development

# Xdebug для локальной разработки
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/cache/apk/*

USER root
WORKDIR /var/www/html
EXPOSE 9000
CMD ["php-fpm", "-F", "-R"]

# ─── Production ───────────────────────────────────────────
FROM base AS production
WORKDIR /var/www/html

COPY --chown=www-data:www-data . /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm", "-F"]