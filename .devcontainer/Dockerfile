# [Choice] PHP version (use -bullseye variants on local arm64/Apple Silicon): 8, 8.1, 8.0, 7, 7.4, 7.3, 8-bullseye, 8.1-bullseye, 8.0-bullseye, 7-bullseye, 7.4-bullseye, 7.3-bullseye, 8-buster, 8.1-buster, 8.0-buster, 7-buster, 7.4-buster
ARG VARIANT=8.1-bullseye
FROM mcr.microsoft.com/vscode/devcontainers/php:0-${VARIANT}

# Install MariaDB client
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client  \
    # Get required packages for php extensions GD
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    # Clean up
    && apt-get clean -y && rm -rf /var/lib/apt/lists/* \
    && apt-get autoremove -y && apt-get autoclean -y

# Install php-mysql driver and GD
RUN docker-php-ext-install mysqli pdo pdo_mysql exif pcntl

# Enable php extensions like GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    # Also enable Sodium
    && docker-php-ext-enable sodium
    # Also enable pcntl which is used for Unit Test time measurements
    && docker-php-ext-enable pcntl

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>
