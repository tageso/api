#!/bin/bash
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" -p --password-stdin
#rm -r -f vendor
#composer install --no-dev
docker build -t tageso/api .
docker push tageso/api