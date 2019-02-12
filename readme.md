
# TaGeSo API

![Build Status](https://travis-ci.org/tageso/api.svg?branch=master)

This is the secound Version of the Tageso API build with Lumen and MYSQL as Backend. It changed from mongoDB to mysql because its easyer to maintain for us. Even the system is bigger than planed at the beginning so a log of features just hacked in the system this should change by this api.

This is still work in process.

# Run Dev-Envirment

## Requirmentes

You need the following tools

* composer
* docker
* docker-compose

Copy the .env.example to .env and change it

```
composer install
docker-compose up
```

You can go into the docker-container by calling

```
docker-compose exec php /bin/sh
```

# Setup

1) Create a .env based on the .env.example
2) Run artisan migrate to setup the database
3) login with the user "admin" and password "admin"

## Setup for tageso.de developers while migration

1) Create a .env based on the .env.example
2) Run import:fromLiveAPI to get the live Data from the current mongodb backend