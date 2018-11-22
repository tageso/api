#!/bin/bash
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
composer install --no-dev
docker build -t tageso/api .
docker push tageso/api