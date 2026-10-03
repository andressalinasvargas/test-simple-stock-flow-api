FROM php:8.2-cli-alpine

RUN apk add --no-cache bash git curl libpng-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
