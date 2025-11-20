FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql mbstring zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN mkdir -p var && chown -R www-data:www-data var


EXPOSE 9000
CMD ["php-fpm"]
