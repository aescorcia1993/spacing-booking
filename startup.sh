#!/bin/bash

echo "=== Laravel Startup Script ==="
echo "Working directory: $(pwd)"

# Navigate to app directory
cd /home/site/wwwroot

# Create necessary directories if they don't exist
echo "Creating required directories..."
mkdir -p storage/framework/{sessions,views,cache,testing}
mkdir -p storage/logs
mkdir -p storage/api-docs
mkdir -p bootstrap/cache

# Set permissions for storage and cache
echo "Setting permissions..."
chmod -R 777 storage bootstrap/cache

# Clear all caches
echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate Swagger documentation
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

# List generated files for debugging
echo "Checking api-docs directory:"
ls -la storage/api-docs/

# Cache configurations for production (only if not in debug mode)
if [ "$APP_DEBUG" != "true" ]; then
    echo "Caching configurations for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "=== Startup completed successfully ==="
echo "API documentation should be available at: ${APP_URL}/api/documentation"
