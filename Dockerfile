FROM alpine:3.8

#Based on https://github.com/TrafeX/docker-php-nginx

RUN apk add php7 php7-fpm php7-json php7-pdo php7-iconv php7-pdo_mysql php7-zip php7-mbstring php7-xml php7-dom php-xmlwriter php-simplexml nginx supervisor

COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY docker/fpm-pool.conf /etc/php7/php-fpm.d/zzz_custom.conf
COPY docker/php.ini /etc/php7/conf.d/zzz_custom.ini


# Configure supervisord
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN mkdir -p /var/www/html

ADD ./ /var/www/html

EXPOSE 80 443
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]