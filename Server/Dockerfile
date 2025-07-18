FROM php:8.2-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Donner les droits à Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
