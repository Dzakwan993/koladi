FROM php:8.2-fpm

WORKDIR /var/www

# Install system deps + node
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpq-dev postgresql-client \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev \
    nodejs npm \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /var/www

# Configure PHP upload limits
RUN echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/uploads.ini \
 && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini


# Permission
RUN chown -R www-data:www-data storage bootstrap/cache public \
&& chmod -R 775 storage bootstrap/cache public

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Frontend build
RUN npm ci && npm run build

EXPOSE 9000
CMD ["php-fpm"]

