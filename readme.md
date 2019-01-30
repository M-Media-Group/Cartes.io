<p align="center"><img src="https://s3.eu-west-3.amazonaws.com/explorevillefranche/logo.svg"></p>

<p align="center">
	<a href="https://travis-ci.org/mwargan/ExploreVillefranche"><img src="https://travis-ci.org/mwargan/ExploreVillefranche.svg?branch=master" alt="Build Status"></a>
</p>

## Live sites
This template is live on:
- [Explore Villefranche](https://explorevillefranche.com)
- [Explore South of France](https://exploresouthoffrance.com)
- [Explore Saint Jean Cap Ferrat](https://exploresaintjeancapferrat.com)
- [Explore Beaulieu](https://explorebeaulieu.com)
- [Explore Eze Village](https://exploreezevillage.com)

## About Explore VIllefranche

This is an open source blog template based on Laravel 5.7. Feel free to fork and use!

## Install

After running composer and npm, run the following commands to create the permissions and roles:
- php artisan migrate
- php artisan permission:create-role admin web "manage posts|edit posts|create posts|delete posts|manage categories|edit categories|create categories|delete categories|manage user roles|manage roles|apply to write"
- php artisan permission:create-role editor web "manage posts|manage categories"
- php artisan permission:create-role writer web "edit posts|create posts|delete posts"

## Contributing

Thank you for considering contributing to the blog! Your contributions are warmly welcomed.

## Security Vulnerabilities

If you discover a security vulnerability within the blog, please open an issue.

## License

The blog is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
