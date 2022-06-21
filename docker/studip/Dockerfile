# Setup php, apache and stud.ip
FROM php:7.4-apache as base

# Install system requirements
RUN apt update && apt install -y --no-install-recommends \
        default-mysql-client \
        default-libmysqlclient-dev \
        libcurl4-openssl-dev zlib1g-dev \
        libpng-dev \
        libjpeg-dev \
        libonig-dev \
        libzip-dev \
        libicu-dev \
        vim \
    && rm -rf /var/lib/apt/lists/*

# Install php extensions
RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install pdo gettext curl gd mbstring zip pdo pdo_mysql mysqli intl json

FROM node:12 as nodejs

# Install node modules
COPY . /studip
WORKDIR /studip
RUN make webpack-prod

FROM base as build

# Install composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy studip
COPY --from=nodejs /studip /studip

# Execute make to install composer dependencies and build assets
WORKDIR /studip
RUN make composer

FROM base

# Reconfigure apache
ENV APACHE_DOCUMENT_ROOT /var/www/studip/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=build /studip /var/www/studip

WORKDIR /var/www/studip

# Add config template
COPY ./docker/studip/config_local.php ./config/config_local.inc.php

# Add custom entrypoint
COPY ./docker/studip/docker-entrypoint.sh /usr/local/bin/
RUN chmod u+x /usr/local/bin/docker-entrypoint.sh

# Set start parameters
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]