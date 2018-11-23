#!/bin/bash
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
#docker login -u "$DOCKER_USERNAME" -p "$DOCKER_PASSWORD"
composer install --no-dev
docker build -t tageso/api .
docker push tageso/api