# 🏢 SpaceBooking API - Backend

Sistema de gestión de reservas de espacios corporativos desarrollado con Laravel 11, PostgreSQL y desplegado en Azure.

## 📋 Tabla de Contenidos
- [Descripción](#descripción)
- [Tecnologías](#tecnologías)
- [Infraestructura Azure](#infraestructura-azure)
- [Instalación](#instalación)
- [Endpoints API](#endpoints-api)
- [Flujo de Reservas](#flujo-de-reservas)
- [Autenticación](#autenticación)
- [Credenciales de Prueba](#credenciales-de-prueba)

## 🎯 Descripción

SpaceBooking es una API REST completa para la gestión de reservas de espacios corporativos (salas de conferencias, auditorios, espacios de trabajo). Incluye sistema de roles (usuario/administrador), aprobación de reservas, gestión de disponibilidad y notificaciones.

## 🛠 Tecnologías

- **Framework:** Laravel 11.x
- **Base de Datos:** PostgreSQL 14
- **Autenticación:** Laravel Sanctum (Token-based)
- **Documentación:** Swagger/OpenAPI (L5-Swagger)
- **Cache:** Redis
- **Storage:** Azure Blob Storage
- **Cloud:** Azure App Service + Azure Database for PostgreSQL

## ☁️ Infraestructura Azure

### Servicios Desplegados

1. **Azure App Service**
   - Plan: B1 (Basic)
   - Runtime: PHP 8.2
   - Sistema Operativo: Linux
   - Región: East US
   - Auto-scaling: Habilitado

2. **Azure Database for PostgreSQL**
   - Versión: 14
   - Tier: Flexible Server
   - SKU: Burstable B1ms
   - Storage: 32 GB
   - Backup: 7 días de retención
   - Conexión: SSL habilitado

3. **Azure Redis Cache**
   - Tier: Basic C0
   - Puerto: 6380 (SSL)
   - Persistencia: Habilitada

4. **Azure Blob Storage**
   - Tier: Standard (LRS)
   - Contenedores: `uploads`, `backups`
   - Acceso: Private con SAS tokens

### Configuración de Red
- Virtual Network (VNet) para comunicación privada
- Network Security Groups (NSG) configurados
- CORS habilitado para el frontend

## 🚀 Instalación

### Prerrequisitos
```bash
- PHP >= 8.2
- Composer
- PostgreSQL 14+
- Node.js (para compilar assets)
```

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd spacing-booking
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos en `.env`**
```env
DB_CONNECTION=pgsql
DB_HOST=your-azure-postgres.postgres.database.azure.com
DB_PORT=5432
DB_DATABASE=spacebooking
DB_USERNAME=your-username
DB_PASSWORD=your-password
DB_SSLMODE=require
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate --seed
```

6. **Generar documentación Swagger**
```bash
php artisan l5-swagger:generate
```

7. **Iniciar servidor de desarrollo**
```bash
php artisan serve
```

La API estará disponible en `http://localhost:8000`

## 📡 Endpoints API

### Autenticación
```
POST      /api/register              Registro de nuevos usuarios
POST      /api/login                 Login y obtención de token
POST      /api/logout                Cerrar sesión (requiere token)
GET       /api/user                  Obtener usuario autenticado
```

### Espacios Públicos
```
GET       /api/spaces                Listar todos los espacios
GET       /api/spaces/{id}           Obtener detalles de un espacio
GET       /api/spaces-types          Obtener tipos de espacios disponibles
POST      /api/spaces/{id}/check-availability  Verificar disponibilidad
GET       /api/spaces/{spaceId}/bookings       Obtener reservas de un espacio
```

### Reservas (Requiere Autenticación)
```
GET       /api/bookings              Obtener mis reservas (con filtros: upcoming/active/past)
POST      /api/bookings              Crear nueva reserva
GET       /api/bookings/all          Listar todas las reservas (admin)
GET       /api/bookings/{id}         Obtener detalles de una reserva
PUT       /api/bookings/{id}         Actualizar una reserva
DELETE    /api/bookings/{id}         Eliminar una reserva
POST      /api/bookings/{id}/cancel  Cancelar una reserva
```

### Administración de Espacios (Solo Admin)
```
GET       /api/admin/spaces          Listar espacios (vista admin)
POST      /api/admin/spaces          Crear nuevo espacio
PUT       /api/admin/spaces/{id}     Actualizar espacio
DELETE    /api/admin/spaces/{id}     Eliminar espacio
```

### Documentación
```
GET       /api/documentation         Swagger UI
GET       /docs                      Documentación interactiva
GET       /docs/asset/{asset}        Assets de Swagger
```

### Otros
```
GET       /                          Welcome page
GET       /api                       API info
GET       /up                        Health check
GET       /storage/{path}            Archivos públicos
GET       /sanctum/csrf-cookie       CSRF token
GET       /api/oauth2-callback       OAuth2 callback (Swagger)
```

## 🔄 Flujo de Reservas

### 1. Flujo de Usuario Regular

#### Crear Reserva
```
1. Usuario se autentica → Obtiene token Sanctum
2. Busca espacios disponibles → GET /api/spaces
3. Verifica disponibilidad → POST /api/spaces/{id}/check-availability
   Payload: { booking_date, start_time, end_time }
4. Crea reserva → POST /api/bookings
   Payload: {
     space_id, booking_date, start_time, end_time,
     event_name, purpose, attendees, notes
   }
5. Sistema valida:
   - Disponibilidad del espacio
   - Horario válido (30 min - 8 horas)
   - No solapamiento con otras reservas
6. Si el espacio requiere aprobación:
   - Estado: "pending"
   - Notificación al administrador
7. Si no requiere aprobación:
   - Estado: "confirmed"
   - Notificación de confirmación al usuario
```

#### Gestionar Reservas
```
- Ver mis reservas → GET /api/bookings?type={upcoming|active|past}&page=1&per_page=10
  * upcoming: Reservas futuras confirmadas/pendientes
  * active: Reservas en curso (entre start_time y end_time)
  * past: Reservas completadas, canceladas o pasadas

- Actualizar reserva → PUT /api/bookings/{id}
  * Solo si está en estado "pending" o "confirmed"
  * Re-valida disponibilidad

- Cancelar reserva → POST /api/bookings/{id}/cancel
  * Cambia estado a "cancelled"
  * Libera el espacio
  * Notificación de cancelación
```

### 2. Flujo de Administrador

#### Gestión de Espacios
```
1. Admin crea/edita espacios → POST/PUT /api/admin/spaces
   - Define si requiere aprobación (requires_approval)
   - Establece capacidad y tipo
   - Sube imagen del espacio

2. Ver todas las reservas → GET /api/bookings/all
   - Dashboard completo de reservas
   - Filtros avanzados

3. Aprobar/Rechazar reservas pendientes
   - Actualiza estado de "pending" a "confirmed"/"cancelled"
   - Envía notificación al usuario
```

### 3. Estados de Reserva

```php
- pending:    Esperando aprobación del administrador
- confirmed:  Aprobada y activa
- active:     En curso (entre start_time y end_time)
- completed:  Finalizada (después de end_time)
- cancelled:  Cancelada por usuario o admin
```

### 4. Validaciones Automáticas

**Al crear/actualizar reserva:**
- ✅ Espacio existe y está activo
- ✅ Fecha no está en el pasado
- ✅ Hora de inicio < Hora de fin
- ✅ Duración mínima: 30 minutos
- ✅ Duración máxima: 8 horas
- ✅ No hay solapamiento de horarios
- ✅ Capacidad del espacio >= número de asistentes

**Respuesta de disponibilidad:**
```json
{
  "available": true,
  "conflicts": [],
  "message": "El espacio está disponible"
}
```

## 🔐 Autenticación

### Sanctum Token-Based Auth

1. **Login**
```bash
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

# Respuesta:
{
  "token": "1|xxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Usuario",
    "email": "user@example.com",
    "is_admin": false
  }
}
```

2. **Usar el token en peticiones**
```bash
GET /api/bookings
Authorization: Bearer 1|xxxxxxxxxxx
```

3. **Logout**
```bash
POST /api/logout
Authorization: Bearer 1|xxxxxxxxxxx
```

## 👤 Credenciales de Prueba

### Administrador
```
Email:    admin@spacebooking.com
Password: admin123
Rol:      Administrador
Permisos: Gestión completa de espacios y reservas
```

### Usuario Regular
```
Email:    maria.garcia@example.com
Password: password
Rol:      Usuario
Permisos: Crear y gestionar sus propias reservas
```

## 📊 Base de Datos

### Tablas Principales

**users**
- id, name, email, password, is_admin, timestamps

**spaces**
- id, name, description, type, capacity, image_url, is_active, requires_approval, timestamps

**bookings**
- id, user_id, space_id, booking_date, start_time, end_time, event_name, purpose, attendees, notes, status, timestamps

### Tipos de Espacios
- `conference_room` - Sala de Conferencias
- `auditorium` - Auditorio
- `meeting_room` - Sala de Reuniones
- `workspace` - Espacio de Trabajo
- `event_hall` - Salón de Eventos
- `corporate_theater` - Teatro Corporativo
- `innovation_lab` - Laboratorio de Innovación

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Con coverage
php artisan test --coverage
```

## 📝 Logs

Los logs se almacenan en:
- `storage/logs/laravel.log` (Desarrollo)
- Azure Application Insights (Producción)

## 🔒 Seguridad

- ✅ CORS configurado para frontend específico
- ✅ CSRF protection habilitado
- ✅ SQL Injection protegido (Eloquent ORM)
- ✅ Rate limiting en endpoints públicos
- ✅ Sanitización de inputs
- ✅ Passwords hasheados con bcrypt
- ✅ SSL/TLS en producción
- ✅ Tokens con expiración

## 📦 Deployment en Azure

```bash
# Compilar assets
npm run build

# Optimizar autoloader
composer install --optimize-autoloader --no-dev

# Cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Deploy con Azure CLI
az webapp deployment source config-local-git
git push azure main
```

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -m 'Add: nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Copyright (c) 2026 SpaceBooking. All Rights Reserved.

Este proyecto es **propietario y confidencial**. Todos los derechos reservados.

## 👥 Equipo

Desarrollado por el equipo de SpaceBooking

---

**Documentación API:** [https://your-domain.azurewebsites.net/api/documentation](https://your-domain.azurewebsites.net/api/documentation)
**Frontend:** [https://your-frontend.azurewebsites.net](https://your-frontend.azurewebsites.net)
