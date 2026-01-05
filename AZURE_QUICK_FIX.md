# CONFIGURACIÓN CRÍTICA PASO A PASO

## 🔴 PASO 1: Path Mapping (EL MÁS IMPORTANTE)

Esto es lo que soluciona el 404. Laravel necesita servir desde `/public`.

1. Azure Portal → be-spacing-booking
2. Settings → **Configuration**
3. Tab: **Path mappings**
4. Click: **+ New path mapping** o **New virtual application**
5. Ingresa:
   ```
   Virtual Path: /
   Physical Path: site/wwwroot/public
   Type: Application (marcado)
   ```
6. Click **OK**
7. Click **Save** (arriba)

## 🔴 PASO 2: Startup Command

1. Mismo menú **Configuration**
2. Tab: **General settings**
3. Scroll hasta **Startup Command**
4. Ingresa: `bash /home/site/wwwroot/startup.sh`
5. Click **Save**

## 🔴 PASO 3: Variables de Entorno

1. Mismo menú **Configuration**
2. Tab: **Application settings**
3. Click **+ New application setting** para cada una:

**Mínimo requerido:**
```
APP_URL = https://be-spacing-booking.azurewebsites.net
L5_SWAGGER_CONST_HOST = https://be-spacing-booking.azurewebsites.net/api
APP_KEY = [generar con: php artisan key:generate --show]
```

**Completas (recomendado):**
```
APP_NAME = SpacingBooking
APP_ENV = production
APP_DEBUG = false
APP_URL = https://be-spacing-booking.azurewebsites.net

DB_CONNECTION = mysql
DB_HOST = [tu-servidor-mysql]
DB_PORT = 3306
DB_DATABASE = [tu-base-datos]
DB_USERNAME = [tu-usuario]
DB_PASSWORD = [tu-password]

L5_SWAGGER_USE_ABSOLUTE_PATH = true
L5_SWAGGER_CONST_HOST = https://be-spacing-booking.azurewebsites.net/api

CACHE_DRIVER = file
SESSION_DRIVER = file
```

## 🔴 PASO 4: Reiniciar

1. Azure Portal → Overview
2. Click **Restart**
3. Espera 2-3 minutos

## ✅ Verificar

Abre en tu navegador:
```
https://be-spacing-booking.azurewebsites.net/api/documentation
```

## ❌ Si AÚN da 404

### Opción A: SSH y Debug
```bash
# Conectar por SSH (Azure Portal → Development Tools → SSH)
cd /home/site/wwwroot

# Ver rutas disponibles
php artisan route:list | grep doc

# Regenerar Swagger
php artisan l5-swagger:generate

# Ver permisos
ls -la storage/api-docs/
```

### Opción B: Ver Logs
Azure Portal → Monitoring → Log stream

Busca errores de:
- PHP Fatal errors
- Permission denied
- File not found

### Opción C: Verificar Path Mapping
```bash
# En SSH
pwd
# Debe mostrar: /home/site/wwwroot

ls -la public/index.php
# Debe existir

# Ver que Nginx está sirviendo desde public
cat /etc/nginx/sites-enabled/default | grep root
# Debe contener: /home/site/wwwroot/public
```

## 🎯 Archivos del Proyecto

```
spacing-booking/
├── startup.sh              ← Script que se ejecuta al iniciar
├── public/
│   ├── .htaccess          ← Reglas de rewrite
│   └── index.php          ← Punto de entrada Laravel
└── storage/
    └── api-docs/
        └── api-docs.json  ← Generado por l5-swagger
```

## 📞 Si Nada Funciona

1. Verifica que el GitHub Actions deployment fue exitoso
2. Asegúrate de tener una base de datos MySQL configurada
3. Revisa que el APP_KEY esté generado
4. Intenta acceder primero a: https://be-spacing-booking.azurewebsites.net/api
5. Si la API base funciona pero Swagger no, el problema es la ruta de Swagger
