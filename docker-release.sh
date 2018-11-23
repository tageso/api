#!/bin/bash
git reset --hard master
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
rm -r -f vendor
composer install --no-dev
docker pull tageso/api
docker build -t tageso/api .
docker push tageso/api
rm -r -f vendor