# Solución 404 en Swagger - Azure App Service Linux

## ⚠️ Problema Identificado

Azure App Service Linux sirve archivos desde `/home/site/wwwroot` pero Laravel necesita que el servidor apunte a `/home/site/wwwroot/public`.

## ✅ Solución Completa

### 1. Configuración CRÍTICA en Azure Portal

**Ve a Azure Portal → Tu App Service → Configuration → Path mappings → Add New Virtual Application**

```
Virtual Path: /
Physical Path: site/wwwroot/public
Application: ☑️ (marcado)
```

**Luego en General Settings:**

```
Stack: PHP 8.3
Startup Command: bash /home/site/wwwroot/startup.sh
```

### 2. Variables de Entorno Requeridas

En **Configuration → Application settings**, agrega:

```bash
APP_NAME=SpacingBooking
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:TU_CLAVE_AQUI    # Genera con: php artisan key:generate --show
APP_URL=https://be-spacing-booking.azurewebsites.net

# Base de datos MySQL
DB_CONNECTION=mysql
DB_HOST=tu-servidor.mysql.database.azure.com
DB_PORT=3306
DB_DATABASE=spacing_booking
DB_USERNAME=tu-usuario
DB_PASSWORD=tu-contraseña

# Swagger - IMPORTANTE
L5_SWAGGER_USE_ABSOLUTE_PATH=true
L5_SWAGGER_CONST_HOST=https://be-spacing-booking.azurewebsites.net/api

# Cache y Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 3. Desplegar los Cambios

```bash
# Hacer commit de los archivos actualizados
git add .
git commit -m "Fix Azure deployment configuration"
git push origin main
```

### 4. Después del Despliegue

1. **Reinicia la aplicación** en Azure Portal
2. **Espera 2-3 minutos** para que todo se inicialice
3. **Accede a:**
   - API Base: https://be-spacing-booking.azurewebsites.net/api
   - Swagger: https://be-spacing-booking.azurewebsites.net/api/documentation

## 🔍 Verificación y Debugging

### Opción 1: SSH al contenedor

```bash
# Conectar por SSH
az webapp ssh --name be-spacing-booking --resource-group <tu-resource-group>

# O desde Azure Portal → Development Tools → SSH

# Una vez conectado:
cd /home/site/wwwroot

# Verificar que el startup.sh se ejecutó
ls -la storage/api-docs/

# Ver rutas de Laravel
php artisan route:list | grep documentation

# Regenerar Swagger manualmente si es necesario
php artisan l5-swagger:generate

# Ver logs
tail -f /var/log/nginx/error.log
tail -f storage/logs/laravel.log
```

### Opción 2: Ver logs en tiempo real

En Azure Portal:
- **Monitoring → Log stream**
- Selecciona "Application logs"

### Verificar que Swagger está funcionando localmente

```bash
# En tu máquina local
cd "c:\Users\hp\OneDrive\Escritorio\ENTRENAMIENTO DE PROGRAMACION 2025\SPACING-BOOKING\spacing-booking"

# Instalar dependencias
composer install

# Generar documentación
php artisan l5-swagger:generate

# Servir la aplicación
php artisan serve

# Acceder a: http://localhost:8000/api/documentation
```

## 🚨 Causas Comunes del 404

1. **Path mapping NO configurado** → El más común
   - Solución: Configurar Virtual Path a `/` y Physical Path a `site/wwwroot/public`

2. **Startup command NO configurado**
   - Solución: Agregar `bash /home/site/wwwroot/startup.sh` en General Settings

3. **Variables de entorno faltantes**
   - Solución: Verificar que todas las variables estén configuradas

4. **Permisos en storage/**
   - Solución: El startup.sh los configura con `chmod -R 777`

5. **Cache de Laravel corrupta**
   - Solución: SSH y ejecutar `php artisan config:clear && php artisan cache:clear`

## 📋 Checklist de Configuración

- [ ] Path mapping configurado: `/` → `site/wwwroot/public`
- [ ] Startup command: `bash /home/site/wwwroot/startup.sh`
- [ ] Variable `APP_URL` configurada correctamente
- [ ] Variable `L5_SWAGGER_CONST_HOST` configurada
- [ ] Todas las variables de BD configuradas
- [ ] Aplicación reiniciada después de cambios
- [ ] Logs verificados sin errores

## 🎯 URLs Esperadas

Una vez configurado correctamente:

| Endpoint | URL |
|----------|-----|
| API Base | https://be-spacing-booking.azurewebsites.net/api |
| Swagger UI | https://be-spacing-booking.azurewebsites.net/api/documentation |
| API Docs JSON | https://be-spacing-booking.azurewebsites.net/docs/api-docs.json |
| Health Check | https://be-spacing-booking.azurewebsites.net/api |
