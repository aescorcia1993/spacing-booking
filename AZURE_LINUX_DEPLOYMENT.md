# Configuración para Azure App Service (Linux)

## Archivos Creados para Solucionar 404 en Swagger

### 1. nginx.conf
Configuración personalizada de Nginx para servir Laravel correctamente desde `/home/site/wwwroot/public`.

### 2. startup.sh
Script de inicio que se ejecuta al desplegar:
- Configura Nginx
- Establece permisos
- Genera documentación de Swagger
- Optimiza Laravel

## Configuración Requerida en Azure Portal

### Application Settings (Variables de Entorno)

```bash
APP_NAME=SpacingBooking
APP_ENV=production
APP_DEBUG=false
APP_KEY=                    # Genera con: php artisan key:generate --show
APP_URL=https://be-spacing-booking.azurewebsites.net

# Base de datos
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

# Swagger
L5_SWAGGER_USE_ABSOLUTE_PATH=true
L5_SWAGGER_CONST_HOST=${APP_URL}/api

# Cache y Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### General Settings

1. **Stack:**
   - Runtime stack: PHP 8.3

2. **Startup Command:**
   ```bash
   bash /home/site/wwwroot/startup.sh
   ```

3. **Path Mappings:** (Opcional, ya configurado en nginx.conf)
   - Virtual path: `/`
   - Physical path: `/home/site/wwwroot/public`

## Pasos para Desplegar

### 1. Hacer commit y push

```bash
git add .
git commit -m "Add Azure Linux configuration for Laravel"
git push origin main
```

### 2. Configurar en Azure Portal

1. Ve a tu App Service: **be-spacing-booking**
2. Settings → **Configuration** → Application settings
3. Agrega todas las variables de entorno listadas arriba
4. Settings → **General settings** → Startup Command:
   ```
   bash /home/site/wwwroot/startup.sh
   ```
5. Click **Save** y espera el reinicio

### 3. Verificar el despliegue

Una vez desplegado, verifica:

- **API Base:** https://be-spacing-booking.azurewebsites.net/api
- **Swagger UI:** https://be-spacing-booking.azurewebsites.net/api/documentation
- **API Docs JSON:** https://be-spacing-booking.azurewebsites.net/docs/api-docs.json

## Troubleshooting

### Si sigues viendo 404:

1. **Verifica los logs:**
   ```bash
   # En Azure Portal → Monitoring → Log stream
   # O conecta por SSH:
   az webapp ssh --name be-spacing-booking --resource-group <tu-resource-group>
   ```

2. **Verifica que startup.sh se ejecutó:**
   ```bash
   # SSH a la aplicación
   ls -la /etc/nginx/sites-available/default
   cat /var/log/nginx/error.log
   ```

3. **Regenera Swagger manualmente:**
   ```bash
   # Desde SSH
   cd /home/site/wwwroot
   php artisan l5-swagger:generate
   ```

4. **Verifica permisos:**
   ```bash
   ls -la /home/site/wwwroot/storage
   ls -la /home/site/wwwroot/storage/api-docs
   ```

5. **Reinicia la aplicación:**
   - Azure Portal → Overview → Restart

### Comandos útiles para debugging (SSH)

```bash
# Ver logs de Nginx
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# Ver logs de Laravel
tail -f /home/site/wwwroot/storage/logs/laravel.log

# Verificar configuración de Nginx
nginx -t

# Verificar rutas de Laravel
cd /home/site/wwwroot
php artisan route:list

# Listar documentación de Swagger
php artisan l5-swagger:generate
ls -la storage/api-docs/
```

## Notas Importantes

- El directorio raíz debe ser `/home/site/wwwroot/public`
- Nginx escucha en el puerto 8080 en Azure App Service Linux
- El script `startup.sh` se ejecuta cada vez que se reinicia el contenedor
- La documentación de Swagger se regenera automáticamente en cada despliegue
