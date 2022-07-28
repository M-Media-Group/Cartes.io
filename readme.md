# About

This is an open source project based on Laravel 5.7. Feel free to fork and use! This repo is the code behind [Cartes.io](https://cartes.io).

The [API docs are available in our wiki](https://github.com/M-Media-Group/Cartes.io/wiki/API).

Want to just test out the site? [There's a demo map](https://cartes.io/maps/048eebe4-8dac-46e2-a947-50b6b8062fec#2/43.7/7.3), or you can create your own map.

## Using Cartes.io

You are free to use [Cartes.io](https://cartes.io) and/or it's [API](https://github.com/M-Media-Group/Cartes.io/wiki/API) free of charge. No authentication is required.

[Please consider donating/sponsoring](https://github.com/sponsors/M-Media-Group) the project!

### Use cases

You can create maps for anything! You can [explore the existing public maps](https://cartes.io) on the site, or get inspired with a few ideas here:
- Do you have a couple of favorite restaurants? Create a map marking the spot so that you can always check back in and share your discoveries with friends
- Friends/family from out of town coming to visit you? Create a map with all the helpful local destinations, like the best bakery or the nearest family doctor
- Going hiking? Create a map showing where you plan to be, and regularly create new markers as you pass important checkpoints. Share the map with friends/family in case you get lost
- Does your town have a few "little free libraries"? Create a map of where they are and share it with your community on Facebook or wherever
- Going out to protest? Create a public community-driven map so that everyone can mark dangerous markers and be aware of what's going on when and where

### Notable maps created

Some awesome maps have already been created by the community and shared publicly.

Note, to see markers on some of these maps, you need to go into "Map display options", and then check the "Show all markers" checkbox.

- [Ukraine crisis](https://cartes.io/maps/139c1aa3-228e-4a8d-9d77-289dc6c70d8e#2/43.7/7.3): Live tracking from multiple sources of the unfolding events in Ukraine
- [Coronavirus Drive-Thru testing points](https://cartes.io/maps/a61bce50-20be-4b31-a7ee-cfaa31325813#2/43.7/7.3): This map shows identified Coronavirus drive-thru testing points around the world.
- [Twin Cities riots](https://cartes.io/maps/651107a9-1d22-46a8-8254-111f7ac74a2b#2/43.7/7.3): This map evolved from the Minnesota protests and was used by locals to mark dangerous markers as they happend. 135 events were reported.
- [Places to get help in Beirut](https://cartes.io/maps/a7967e04-38e6-4328-a0b4-e5d2c3282687#13/33.8889/35.5291): Following the 2020 explosion in Beirut, this map was created to raise awareness within the local community on where they can get help.
- [Little free libraries on the French Riviera](https://cartes.io/maps/4b8e280f-0682-42a7-be43-f3d2ea729f7b#12/43.7111/7.2970): This map shows the locations of public little free libraries - a box where you can leave and borrow books out in the open.
- [The hardest airports to fly in to](https://cartes.io/maps/9dec23f1-5fa9-4841-a1c2-5086968ba8f1): Here's a non exhaustive map of some of the hardest places to land a plane around the world!

## Interact with in Python
See the Cartes.io package at https://pypi.org/project/py-cartes-io/ - `pip install py-cartes-io`, which allows you to interact with the Cartes.io API in a fluent Pythonic way.

## Interact with in JS
See the Cartes.io NPM package at https://www.npmjs.com/package/@m-media/npm-cartes-io - `npm install @m-media/npm-cartes-io`, which allows you to interact with the Cartes.io API in a fluent way in JS.

## Development installation

After running composer and npm, run the following commands to create the permissions and roles:
- php artisan migrate
- php artisan permission:create-role admin web "manage markers|edit markers|create markers|delete markers|manage categories|edit categories|create categories|delete categories|manage user roles|manage roles|apply to report|manage maps|create markers in bulk|mark spam"
- php artisan permission:create-role editor web "manage markers|manage categories|manage maps"
- php artisan permission:create-role "power reporter" web "create markers in bulk"
- php artisan permission:create-role reporter web "edit markers|create markers|delete markers|mark spam"

## Roadmap

The general plan is that this repo will be simplified to containing just the core app and its API, while the frontend will be developed in a seperate repo, [here](https://github.com/M-Media-Group/Cartes.io-Web-App). If you're a frontend developer, please feel free to contribute on that repo to the development of the web-app.