# About

This is an open source project based on Laravel 5.7. Feel free to fork and use! This repo is the code behind [Cartes.io](https://cartes.io).

## Install

After running composer and npm, run the following commands to create the permissions and roles:
- php artisan migrate
- php artisan permission:create-role admin web "manage incidents|edit incidents|create incidents|delete incidents|manage categories|edit categories|create categories|delete categories|manage user roles|manage roles|apply to report|manage maps"
- php artisan permission:create-role editor web "manage incidents|manage categories|manage maps"
- php artisan permission:create-role reporter web "edit incidents|create incidents|delete incidents"

## Contributing

Thank you for considering contributing to the blog! Your contributions are warmly welcomed.

## Security Vulnerabilities

If you discover a security vulnerability within the blog, please open an issue.

## License

The blog is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
