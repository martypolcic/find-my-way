FROM php:8.4-apache

ARG WWWGROUP=1000

WORKDIR /app

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install \
    zip \
    pdo_mysql

COPY --chown=www-data:www-data --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

#COPY --chown=www-data:www-data . .

# Copy composer files
#WCOPY composer.json composer.lock ./

#RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-scripts

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN groupadd --force -g $WWWGROUP www-data

EXPOSE 80/tcp