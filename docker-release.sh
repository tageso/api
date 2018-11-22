#!/bin/bash
docker login -u "$DOCKER_USERNAME" -p "$DOCKER_PASSWORD"
#rm -r -f vendor
#composer install --no-dev
docker build -t tageso/api .
docker push tageso/api