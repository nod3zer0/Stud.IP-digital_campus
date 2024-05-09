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
        locales \
    && rm -rf /var/lib/apt/lists/*

# Install php extensions
RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install pdo gettext curl gd mbstring zip pdo pdo_mysql mysqli intl json

FROM node:16 as nodejs

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


#reverse proxy fix
ARG ProxyUrl
RUN if [[ -z "$ProxyUrl" ]] ; then printf '%s\n%s\n' "ServerName $ProxyUrl" "$(cat /etc/apache2/apache2.conf)" >/etc/apache2/apache2.conf ; fi
RUN if [[ -z "$ProxyUrl" ]] ;  then printf '%s\n%s\n' "UseCanonicalName On" "$(cat /etc/apache2/apache2.conf)" >/etc/apache2/apache2.conf ; fi


COPY --from=build /studip /var/www/studip

WORKDIR /var/www/studip

# Add config template
COPY ./docker/studip/config_local.php ./config/config_local.inc.php

# Copy config
RUN cp ./config/config.inc.php.dist ./config/config.inc.php

# Add custom entrypoint
COPY ./docker/studip/docker-entrypoint.sh /usr/local/bin/
RUN chmod u+x /usr/local/bin/docker-entrypoint.sh
# plugins hot fix
RUN chmod -R 755 /var/www/studip/public/plugins_packages
RUN chown -R www-data /var/www/studip/public/plugins_packages
#locales hot fix
RUN sed -i '/en_GB.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen
ENV LANG en_GB.UTF-8
ENV LANGUAGE en_GB:en
ENV LC_ALL en_GB.UTF-8

# SimpleSamlPHP

#copy SimpleSamlPHP
COPY ./docker/studip/simplesamlphp /var/simplesamlphp
#copy apache site config
COPY ./docker/studip/000-default.conf /etc/apache2/sites-available/000-default.conf
#create cache directory
RUN mkdir -p /var/cache/simplesamlphp/core
#make permissions
RUN chown -R www-data /var/simplesamlphp
RUN chown -R www-data /var/cache/simplesamlphp

# Set start parameters
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
