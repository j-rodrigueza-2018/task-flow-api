FROM php:8.5-fpm

# Instalamos las dependencias del SO, incluyendo uuid-dev
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    uuid-dev \
    && docker-php-ext-install zip pdo pdo_pgsql \
    && pecl install uuid \
    && docker-php-ext-enable uuid

# Inyectar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html