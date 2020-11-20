# About

This is an open source project based on Laravel 5.7. Feel free to fork and use! This repo is the code behind [Cartes.io](https://cartes.io).

## Using Cartes.io

You are free to use Cartes.io and/or it's API free of charge. No authentication is required.

## Install

After running composer and npm, run the following commands to create the permissions and roles:
- php artisan migrate
- php artisan permission:create-role admin web "manage incidents|edit incidents|create incidents|delete incidents|manage categories|edit categories|create categories|delete categories|manage user roles|manage roles|apply to report|manage maps"
- php artisan permission:create-role editor web "manage incidents|manage categories|manage maps"
- php artisan permission:create-role reporter web "edit incidents|create incidents|delete incidents"
