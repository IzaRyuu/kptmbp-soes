FROM php:8.2-cli

# Install system dependencies & PostgreSQL driver
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port
EXPOSE 8080

# Start Laravel Server on port 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]