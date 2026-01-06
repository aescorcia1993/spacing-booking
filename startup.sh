#!/bin/bash
set -e

echo "========================================="
echo "Laravel Azure Startup Script"
echo "========================================="

# Determine working directory (Azure uses /home/site/wwwroot, Docker uses /var/www/html)
if [ -d "/home/site/wwwroot" ]; then
    WORKDIR="/home/site/wwwroot"
else
    WORKDIR="/var/www/html"
fi

echo "Working directory: $WORKDIR"
cd $WORKDIR

# Install Composer if not exists
if [ ! -f "composer.phar" ]; then
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
fi

# Install dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    php composer.phar install --no-dev --optimize-autoloader --no-interaction
else
    echo "Vendor directory exists, skipping composer install"
fi

# Create .env if doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cp .env.example .env 2>/dev/null || cat > .env << 'EOF'
APP_NAME=SpacingBooking
APP_ENV=production
APP_DEBUG=false
APP_URL=${APP_URL}
LOG_CHANNEL=stack
LOG_LEVEL=error
DB_CONNECTION=mysql
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
L5_SWAGGER_USE_ABSOLUTE_PATH=true
L5_SWAGGER_CONST_HOST=${APP_URL}/api
EOF
fi

# Generate key if not exists
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Create directories
echo "Creating directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs storage/api-docs bootstrap/cache

# Set permissions
echo "Setting permissions..."
chmod -R 777 storage bootstrap/cache

# Clear caches
echo "Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Cache for production (BEFORE Swagger generation)
echo "Caching configuration..."
php artisan config:cache 2>/dev/null || true

# Generate Swagger (AFTER config cache)
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate --force 2>&1 || echo "Warning: Swagger generation failed"

# Cache routes (AFTER Swagger generation)
echo "Caching routes..."
php artisan route:cache 2>/dev/null || true

echo "========================================="
echo "Startup completed!"
echo "Swagger: ${APP_URL}/api/documentation"
echo "========================================="
