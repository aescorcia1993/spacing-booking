#!/bin/bash

# Navigate to app directory
cd /home/site/wwwroot

# Copy custom nginx configuration
echo "Configuring Nginx..."
cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-available/default

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 /home/site/wwwroot
chown -R www-data:www-data /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache

# Create necessary directories if they don't exist
mkdir -p /home/site/wwwroot/storage/framework/sessions
mkdir -p /home/site/wwwroot/storage/framework/views
mkdir -p /home/site/wwwroot/storage/framework/cache
mkdir -p /home/site/wwwroot/storage/logs
mkdir -p /home/site/wwwroot/storage/api-docs

# Set permissions for storage and cache
chmod -R 775 /home/site/wwwroot/storage
chmod -R 775 /home/site/wwwroot/bootstrap/cache

# Clear and cache Laravel configuration
echo "Optimizing Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configurations for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate Swagger documentation
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

# Restart Nginx
echo "Restarting Nginx..."
nginx -t && nginx -s reload

echo "Startup completed successfully!"
