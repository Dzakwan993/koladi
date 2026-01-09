FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git unzip curl \
    libpq-dev postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# copy app
COPY . /var/www

# copy entrypoints (PATH FIX)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/entrypoint.local.sh /usr/local/bin/entrypoint.local.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    /usr/local/bin/entrypoint.local.sh

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
