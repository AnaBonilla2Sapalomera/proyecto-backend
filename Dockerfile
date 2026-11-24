FROM docker.io/library/php:8.6-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libonig5 \
        libonig-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        mbstring \
        zip \
    && apt-get purge -y --auto-remove libonig-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=docker.io/library/composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
