FROM php:7.2-cli-alpine

RUN docker-php-ext-install pdo pdo_mysql

RUN apk update \
    && apk upgrade \
    && apk add zlib-dev \
    && docker-php-ext-configure zip --with-zlib-dir=/usr \
    && docker-php-ext-install zip

ADD ./ /app

CMD ["php", "/app/artisan", "queue:work", "-vvv"]