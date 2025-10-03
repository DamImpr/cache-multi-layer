FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    libzip-dev \
    zlib-dev \
    oniguruma-dev \
    && apk add --no-cache --virtual .build-deps \
    gcc \
    make \
    autoconf \
    libc-dev \
    && pecl install apcu redis memcache \
    && docker-php-ext-enable apcu redis memcache \
    && apk del .build-deps

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
RUN echo "apc.enable_cli=1" >> /usr/local/etc/php/php.ini
RUN echo "apc.enable=1" >> /usr/local/etc/php/php.ini
WORKDIR /app

COPY app/composer.json ./
COPY app/composer.lock ./

RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY ./app .

RUN composer dump-autoload --optimize

COPY ./entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
