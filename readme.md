# About

This is an open source project based on Laravel 5.7. Feel free to fork and use! This repo is the code behind [Cartes.io](https://cartes.io).

The [API docs are available in our wiki](https://github.com/M-Media-Group/Cartes.io/wiki/API).

## Using Cartes.io

You are free to use Cartes.io and/or it's API free of charge. No authentication is required.

### Use cases

You can create maps for anything! You can explore the existing public maps on the site, or get inspired with a few ideas here:
- Do you have a couple of favorite restaurants? Create a map marking the spot so that you can always check back in and share your dosciveries with friends
- Friends/family from out of town coming to visit you? Create a map with all the helpful local destinations, like the best bakery or the nearest family doctor
- Going hiking? Create a map showing where you plan to be, and regularly check back and create new markers as you pass waypoints. Share the map with friends/family in case you get lost
- Does your twon have a few "little free libraries"? Create a map of where they are and share it with your community on Facebook or wherever
- Going out to protest? Create a public community-driven map so that everyone can mark dangerous incidents and be aware of what's going on when and where

## Install

After running composer and npm, run the following commands to create the permissions and roles:
- php artisan migrate
- php artisan permission:create-role admin web "manage incidents|edit incidents|create incidents|delete incidents|manage categories|edit categories|create categories|delete categories|manage user roles|manage roles|apply to report|manage maps"
- php artisan permission:create-role editor web "manage incidents|manage categories|manage maps"
- php artisan permission:create-role reporter web "edit incidents|create incidents|delete incidents"
