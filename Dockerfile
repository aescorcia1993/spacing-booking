FROM php:8.3-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    bash

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create .env from example
RUN cp .env.example .env || echo "APP_NAME=SpacingBooking" > .env

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Generate application key
RUN php artisan key:generate --force || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs storage/api-docs \
    && chmod -R 775 storage

# Generate Swagger
RUN php artisan l5-swagger:generate || true

# Copy configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Create startup script
RUN echo '#!/bin/sh' > /startup.sh && \
    echo 'php artisan config:cache' >> /startup.sh && \
    echo 'php artisan route:cache' >> /startup.sh && \
    echo '/usr/bin/supervisord -c /etc/supervisord.conf' >> /startup.sh && \
    chmod +x /startup.sh

EXPOSE 8080

CMD ["/bin/sh", "/startup.sh"]
