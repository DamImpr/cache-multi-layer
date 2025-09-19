FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    libzip-dev \
    zlib-dev \
    oniguruma-dev \
    gcc \
    make \
    autoconf \
    libc-dev \
    && pecl install apcu redis \
    && docker-php-ext-enable apcu redis \
    && apk del gcc make autoconf libc-dev 
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer 
COPY ./app /app
WORKDIR /app
RUN composer install
#CMD ['./vendor/bin/phpunit','./tests']
