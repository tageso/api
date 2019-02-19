FROM nginx:latest

ADD ./docker/nginx-kube.conf /etc/nginx/conf.d/default.conf
ADD ./ /app