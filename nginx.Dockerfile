FROM nginx:latest

ADD ./docker/nginx.conf /etc/nginx/conf.d/default.conf
ADD ./ /app