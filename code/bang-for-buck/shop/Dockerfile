FROM composer:2.9.8 AS composer

FROM php:8.5.13-fpm-bookworm AS shop

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip \
    && docker-php-ext-install -j2 pcntl pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install redis-6.3.0 && docker-php-ext-enable redis

RUN printf '%s\n' \
      'opcache.enable=${SHOP_OPCACHE:-1}' \
      'opcache.enable_cli=0' \
      'opcache.jit=disable' \
      'opcache.validate_timestamps=0' \
      > /usr/local/etc/php/conf.d/shop-opcache.ini \
    && printf '%s\n' \
      '[www]' \
      'pm = static' \
      'pm.max_children = 4' \
      'pm.status_path = /fpm-status' \
      'pm.status_listen = 9001' \
      > /usr/local/etc/php-fpm.d/zzz-shop.conf

WORKDIR /app

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-dev --no-scripts --no-autoloader

COPY --chown=www-data:www-data . .
RUN composer dump-autoload --no-dev --classmap-authoritative

FROM shop AS profiler

RUN pecl install xdebug-3.5.1 \
    && docker-php-ext-enable xdebug \
    && install -d -o www-data -g www-data /tmp/shop-profiles \
    && printf '%s\n' \
      'xdebug.mode=profile' \
      'xdebug.start_with_request=trigger' \
      'xdebug.trigger_value=shop-profile' \
      'xdebug.output_dir=/tmp/shop-profiles' \
      > /usr/local/etc/php/conf.d/zzz-shop-xdebug.ini

FROM shop AS runtime
