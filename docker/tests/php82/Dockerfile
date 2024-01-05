# Setup php, apache and stud.ip
FROM php:8.2-cli

# Install system requirements
RUN apt update && apt install -y  --no-install-recommends \
    default-mysql-client default-libmysqlclient-dev libcurl4-openssl-dev zlib1g-dev libpng-dev libjpeg-dev libonig-dev libzip-dev libicu-dev \
    lsb-release \
    zip \
    tar \
    && rm -rf /var/lib/apt/lists/*

# Install php extensions
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/
RUN docker-php-ext-install -j$(nproc) pdo gettext curl gd mbstring zip pdo pdo_mysql mysqli intl

# Install npm using nvm
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt update && apt install -y --no-install-recommends nodejs \
        curl apt-transport-https ca-certificates gnupg unzip git \
    && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
