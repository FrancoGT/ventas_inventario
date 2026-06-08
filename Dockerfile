FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install intl mysqli pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

# DocumentRoot => public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Carpetas que CI4 necesita
RUN mkdir -p writable/cache \
    && mkdir -p writable/logs \
    && mkdir -p writable/session \
    && mkdir -p writable/uploads

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 writable

EXPOSE 80

CMD ["apache2-foreground"]