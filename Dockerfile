FROM php:8.3-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    postgresql-client \
    postgresql-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    bash

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create .env from example (will be overridden by environment variables in Azure)
RUN if [ -f .env.example ]; then cp .env.example .env; else \
    echo "APP_NAME=SpacingBooking" > .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "DB_CONNECTION=mysql" >> .env && \
    echo "CACHE_DRIVER=file" >> .env && \
    echo "SESSION_DRIVER=file" >> .env; \
    fi

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Install laravel/pail
RUN composer require laravel/pail --dev --no-interaction

# Generate application key
RUN php artisan key:generate --force || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs storage/api-docs \
    && chmod -R 775 storage

# Copy configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Create startup script
RUN echo '#!/bin/sh' > /startup.sh && \
    echo 'set -e' >> /startup.sh && \
    echo 'echo "Starting Laravel application..."' >> /startup.sh && \
    echo 'cd /var/www/html' >> /startup.sh && \
    echo 'php artisan config:clear || true' >> /startup.sh && \
    echo 'php artisan route:clear || true' >> /startup.sh && \
    echo 'php artisan view:clear || true' >> /startup.sh && \
    echo 'php artisan l5-swagger:generate || echo "Swagger generation skipped"' >> /startup.sh && \
    echo 'php artisan config:cache || true' >> /startup.sh && \
    echo 'php artisan route:cache || true' >> /startup.sh && \
    echo 'echo "Starting services..."' >> /startup.sh && \
    echo 'exec /usr/bin/supervisord -c /etc/supervisord.conf' >> /startup.sh && \
    chmod +x /startup.sh

EXPOSE 8000

CMD ["/bin/sh", "/startup.sh"]
