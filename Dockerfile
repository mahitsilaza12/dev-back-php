FROM php:7.4-cli

RUN apt-get update && apt-get install -y \
        libxml2-dev \
        zlib1g-dev \
        libzip-dev \
        && docker-php-ext-install -j$(nproc) soap \
        && pecl install redis \
        && docker-php-ext-enable redis

WORKDIR /console

CMD [ "php", "bin/console" ]
