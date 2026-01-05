<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Space Booking API",
 *     version="1.0.0",
 *     description="API REST para sistema de reserva de espacios para eventos. Permite gestionar espacios (salas de reuniones, auditorios, etc.), realizar reservas y gestionar usuarios con autenticación JWT mediante Laravel Sanctum.",
 *     @OA\Contact(
 *         email="admin@spacebooking.com",
 *         name="API Support"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="API Server Local"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Ingresa el token JWT generado al hacer login (sin 'Bearer' al inicio)"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints para registro, login y gestión de autenticación"
 * )
 * 
 * @OA\Tag(
 *     name="Spaces",
 *     description="Endpoints públicos para consultar espacios disponibles"
 * )
 * 
 * @OA\Tag(
 *     name="Bookings",
 *     description="Endpoints para gestionar reservas (requiere autenticación)"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Spaces",
 *     description="Endpoints para administración de espacios (requiere rol admin)"
 * )
 */
abstract class Controller
{
    //
}
