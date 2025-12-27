# 1️⃣ Base image: PHP 8.2 CLI
FROM php:8.2-cli

# 2️⃣ Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 3️⃣ Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4️⃣ Set working directory
WORKDIR /var/www

# 5️⃣ Copy composer files dulu (biar cache aman)
COPY composer.json composer.lock ./

# 6️⃣ Auto install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 7️⃣ Copy seluruh source code
COPY . .

# 8️⃣ Permission (opsional tapi aman)
RUN chown -R www-data:www-data /var/www

# 9️⃣ Expose port Laravel
EXPOSE 8000

# 🔟 Jalankan Laravel dev server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
