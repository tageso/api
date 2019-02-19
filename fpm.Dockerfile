FROM php:7.2-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

ADD ./ /app

RUN rm -r -f /app/storage/logs/*
RUN chmod uog+rwx /app/storage/logs