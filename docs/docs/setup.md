# Setup
_Comming Soon_


# Docker-Compose
To Setup the API with Docker-Compose:
```
version: '3.2'
services:
  todoapi:
    image: tageso/api:latest
    networks:
      tageso:
        aliases:
          - todoapi
    environment:
      APP_ENV: local
      APP_DEBUG: 1
      APP_TIMEZONE: UTC
      DB_CONNECTION: mysql
      DB_HOST: mysql
      DB_PORT: 3306
      DB_DATABASE: tageso
      DB_USERNAME: dev
      DB_PASSWORD: abcdefgh
      CACHE_DRIVER: file
      QUEUE_CONNECTION: database
      SMTP_HOST: mail.example.com
      SMTP_USER: info@tageso.de
      SMTP_PASS: PASSWORD
      SMTP_FROM: info@tageso.de
      SMTP_FROM_NAME: Tageso
      SMTP_SECURE: tls
      SMTP_PORT: 587
      SEND_MAILS: 0
      FRONTEND_URL: http://localhost:8080/
      S3_Endpoint: https://s3.tageso.de
      S3_Key: USER
      S3_Secret: PASS
      S3_Bucket: dev
  mysql:
    image: mysql:5.6
    restart: always
    volumes:
      - ./mysql-produktiv:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: abcdefgh
      MYSQL_DATABASE: tageso
      MYSQL_USER: dev
      MYSQL_PASSWORD: abcdefgh
    networks:
      tageso:
        aliases:
          - mysql
    networks:
      tageso:
        aliases:
          - phpmyadmin
networks:
  tageso:

```