
# TaGeSo API

This is the secound Version of the Tageso API build with Lumen and MYSQL as Backend. It changed from mongoDB to mysql because its easyer to maintain for us. Even the system is bigger than planed at the beginning so a log of features just hacked in the system this should change by this api.

This is still work in process.

# Setup

1) Create a .env based on the .env.example
2) Run artisan migrate to setup the database
3) login with the user "admin" and password "admin"

## Setup for tageso.de developers while migration

1) Create a .env based on the .env.example
2) Run import:fromLiveAPI to get the live Data from the current mongodb backend

# Basic Rules

* The API schould run as continuse deployment, for this there need to be tested quellqode, lumen add a lot of features 
* It need to be possible to add new features. Therefor the http-controller should just save the incomming request data and trigger a event
* Thinks that take some time should be in jobs
* Never delete or overwrite any data, that make a log of trouble in the last version.
