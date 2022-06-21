# Setup php, apache and stud.ip
FROM php:7.4-cli

# Install system requirements
RUN apt update && apt install -y  --no-install-recommends \
    default-mysql-client default-libmysqlclient-dev libcurl4-openssl-dev zlib1g-dev libpng-dev libjpeg-dev libonig-dev libzip-dev libicu-dev \
    lsb-release \
    zip \
    tar \
    && rm -rf /var/lib/apt/lists/*

# Install php extensions
RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install pdo gettext curl gd mbstring zip pdo pdo_mysql mysqli intl json

# Install npm using nvm
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash -
RUN apt update && apt install -y --no-install-recommends nodejs \
        curl apt-transport-https ca-certificates gnupg unzip git \
    && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
