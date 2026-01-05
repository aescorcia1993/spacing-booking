<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Space Booking API',
        'version' => '1.0',
        'status' => 'running',
        'description' => 'Sistema de gestión de reservas de espacios para eventos'
    ]);
});

// =============================================
// RUTAS PÚBLICAS DE AUTENTICACIÓN
// =============================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// =============================================
// RUTAS PÚBLICAS DE ESPACIOS
// =============================================
// Listado de espacios disponibles (con filtros)
Route::get('/spaces', [SpaceController::class, 'index']);
// Ver detalle de un espacio
Route::get('/spaces/{id}', [SpaceController::class, 'show']);
// Obtener tipos de espacios disponibles
Route::get('/spaces-types', [SpaceController::class, 'types']);
// Verificar disponibilidad de un espacio
Route::post('/spaces/{id}/check-availability', [SpaceController::class, 'checkAvailability']);
// Obtener reservas de un espacio en un rango de fechas
Route::get('/spaces/{spaceId}/bookings', [BookingController::class, 'getSpaceBookings']);

// =============================================
// RUTAS PROTEGIDAS CON AUTENTICACIÓN
// =============================================
Route::middleware('auth:sanctum')->group(function () {
    // Perfil de usuario
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // =============================================
    // RESERVAS (BOOKINGS) - Usuario autenticado
    // =============================================
    // Listar reservas del usuario actual
    Route::get('/bookings', [BookingController::class, 'index']);
    // Obtener todas las reservas (para vista de calendario)
    Route::get('/bookings/all', [BookingController::class, 'getAllBookings']);
    // Ver detalle de una reserva
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    // Crear nueva reserva
    Route::post('/bookings', [BookingController::class, 'store']);
    // Actualizar reserva
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    // Cancelar reserva (soft cancel)
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    // Eliminar reserva (hard delete)
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

    // =============================================
    // ADMINISTRACIÓN DE ESPACIOS - Solo Admin
    // =============================================
    Route::middleware('admin')->group(function () {
        // Listar todos los espacios (incluyendo inactivos)
        Route::get('/admin/spaces', [SpaceController::class, 'adminIndex']);
        // Crear nuevo espacio
        Route::post('/admin/spaces', [SpaceController::class, 'store']);
        // Actualizar espacio
        Route::put('/admin/spaces/{id}', [SpaceController::class, 'update']);
        // Eliminar espacio
        Route::delete('/admin/spaces/{id}', [SpaceController::class, 'destroy']);
    });
});
