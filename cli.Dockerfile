FROM php:7.2-cli-alpine

RUN docker-php-ext-install pdo pdo_mysql

RUN apk update \
    && apk upgrade \
    && apk add zlib-dev \
    && docker-php-ext-configure zip --with-zlib-dir=/usr \
    && docker-php-ext-install zip

ADD ./ /app

RUN rm -r -f /app/storage/logs/*
RUN chmod uog+rwx /app/storage/logs

CMD ["php", "/app/artisan", "queue:work", "-vvv"]