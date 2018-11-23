#!/bin/bash
git reset --hard master
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
rm -r -f vendor
composer install --no-dev

cd docs
sudo pip install --upgrade pip
sudo pip install mkdocs
sudo pip install mkdocs-material
mkdocs build

cd ..
cp -r docs/site/* public/


docker pull tageso/api
docker build -t tageso/api .
docker push tageso/api
rm -r -f vendor