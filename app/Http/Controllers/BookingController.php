<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/bookings",
     *     summary="Listar reservas del usuario autenticado",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="status", in="query", description="Filtrar por estado (confirmed, pending, cancelled, completed)", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="from_date", in="query", description="Fecha desde", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="to_date", in="query", description="Fecha hasta", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de reservas del usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request)
    {
        $query = Booking::where('user_id', $request->user()->id)
            ->with('space');

        // Filtrar por tipo de reserva (upcoming, active, past)
        if ($request->has('type') && $request->type) {
            $now = now();
            
            switch ($request->type) {
                case 'upcoming':
                    // Reservas futuras que aún no han comenzado
                    $query->where(function($q) use ($now) {
                        $q->where('status', 'confirmed')
                          ->orWhere('status', 'pending');
                    })
                    ->whereRaw("CONCAT(booking_date, ' ', start_time) > ?", [$now]);
                    break;
                    
                case 'active':
                    // Reservas que están ocurriendo ahora
                    $query->where(function($q) use ($now) {
                        $q->where('status', 'confirmed')
                          ->orWhere('status', 'pending');
                    })
                    ->whereRaw("CONCAT(booking_date, ' ', start_time) <= ?", [$now])
                    ->whereRaw("CONCAT(booking_date, ' ', end_time) >= ?", [$now]);
                    break;
                    
                case 'past':
                    // Reservas completadas, canceladas o que ya terminaron
                    $query->where(function($q) use ($now) {
                        $q->where('status', 'completed')
                          ->orWhere('status', 'cancelled')
                          ->orWhereRaw("CONCAT(booking_date, ' ', end_time) < ?", [$now]);
                    });
                    break;
            }
        }

        // Filtrar por estado
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filtrar por fecha
        if ($request->has('from_date')) {
            $query->where('booking_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('booking_date', '<=', $request->to_date);
        }

        // Ordenar y paginar
        $perPage = $request->get('per_page', 10);
        $bookings = $query->orderBy('booking_date', 'desc')
                         ->orderBy('start_time', 'desc')
                         ->paginate($perPage);

        return response()->json([
            'data' => $bookings,
            'total' => $bookings->count(),
            'current_page' => 1,
            'per_page' => $bookings->count(),
            'last_page' => 1,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/bookings/{id}",
     *     summary="Ver detalle de una reserva",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalle de la reserva",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Reserva no encontrada")
     * )
     */
    public function show(Request $request, $id)
    {
        $booking = Booking::with('space')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json($booking);
    }

    /**
     * @OA\Post(
     *     path="/bookings",
     *     summary="Crear nueva reserva",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"space_id","event_name","booking_date","start_time","end_time"},
     *             @OA\Property(property="space_id", type="integer", example=1),
     *             @OA\Property(property="event_name", type="string", example="Reunión de equipo"),
     *             @OA\Property(property="booking_date", type="string", format="date", example="2026-01-20"),
     *             @OA\Property(property="start_time", type="string", format="time", example="09:00:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="11:00:00"),
     *             @OA\Property(property="notes", type="string", example="Traer laptop")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reserva creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="booking", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Espacio no disponible"),
     *     @OA\Response(response=409, description="El espacio ya está reservado para ese horario"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'space_id' => 'required|exists:spaces,id',
            'event_name' => 'required|string|max:255',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el espacio exista y esté activo
        $space = Space::where('id', $request->space_id)
            ->where('is_active', true)
            ->first();

        if (!$space) {
            return response()->json([
                'message' => 'El espacio no está disponible'
            ], 400);
        }

        // Validación adicional: Verificar que la reserva no sea demasiado larga (máximo 8 horas = 480 minutos)
        $startDateTime = Carbon::parse($request->booking_date . ' ' . $request->start_time);
        $endDateTime = Carbon::parse($request->booking_date . ' ' . $request->end_time);
        $durationMinutes = $endDateTime->diffInMinutes($startDateTime);

        if ($durationMinutes > 480) {
            return response()->json([
                'message' => 'La duración de la reserva no puede exceder 8 horas'
            ], 400);
        }

        // Verificar solapamiento de reservas
        if (Booking::hasOverlap($request->space_id, $request->booking_date, $request->start_time, $request->end_time)) {
            return response()->json([
                'message' => 'El espacio ya está reservado para el horario seleccionado'
            ], 409);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'space_id' => $request->space_id,
            'event_name' => $request->event_name,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'notes' => $request->notes,
            'status' => 'confirmed',
        ]);

        $booking->load('space');

        return response()->json([
            'message' => 'Reserva creada exitosamente',
            'booking' => $booking
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/bookings/{id}",
     *     summary="Actualizar una reserva",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="space_id", type="integer", example=1),
     *             @OA\Property(property="event_name", type="string", example="Reunión actualizada"),
     *             @OA\Property(property="booking_date", type="string", format="date", example="2026-01-20"),
     *             @OA\Property(property="start_time", type="string", format="time", example="10:00:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="12:00:00"),
     *             @OA\Property(property="notes", type="string", example="Notas actualizadas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="booking", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="No se pueden modificar reservas pasadas o canceladas"),
     *     @OA\Response(response=404, description="Reserva no encontrada"),
     *     @OA\Response(response=409, description="Conflicto de horario")
     * )
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // No permitir modificar reservas pasadas o canceladas
        $endDateTime = Carbon::parse($booking->end_datetime);
        if ($endDateTime->isPast()) {
            return response()->json([
                'message' => 'No se pueden modificar reservas pasadas'
            ], 400);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'message' => 'No se pueden modificar reservas canceladas'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'space_id' => 'sometimes|required|exists:spaces,id',
            'event_name' => 'sometimes|required|string|max:255',
            'booking_date' => 'sometimes|required|date|after_or_equal:today',
            'start_time' => 'sometimes|required|date_format:H:i:s',
            'end_time' => 'sometimes|required|date_format:H:i:s|after:start_time',
            'notes' => 'nullable|string',
            'attendees' => 'sometimes|integer|min:1',
            'purpose' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Si se está cambiando el espacio, verificar que esté activo
        if ($request->has('space_id') && $request->space_id != $booking->space_id) {
            $space = Space::where('id', $request->space_id)
                ->where('is_active', true)
                ->first();

            if (!$space) {
                return response()->json([
                    'message' => 'El espacio seleccionado no está disponible'
                ], 400);
            }

            // Verificar capacidad si se proporciona attendees
            if ($request->has('attendees') && $request->attendees > $space->capacity) {
                return response()->json([
                    'message' => "El espacio solo tiene capacidad para {$space->capacity} personas"
                ], 400);
            }
        }

        // Si se están modificando fechas u horarios, verificar solapamiento
        $spaceId = $request->space_id ?? $booking->space_id;
        $bookingDate = $request->booking_date ?? $booking->booking_date;
        $startTime = $request->start_time ?? $booking->start_time;
        $endTime = $request->end_time ?? $booking->end_time;

        // Validar que la nueva fecha no sea del pasado
        $newStartDateTime = Carbon::parse($bookingDate . ' ' . $startTime);
        if ($newStartDateTime->isPast()) {
            return response()->json([
                'message' => 'No se puede crear una reserva con fecha y hora del pasado'
            ], 400);
        }

        // Validar duración mínima (30 minutos) y máxima (8 horas = 480 minutos)
        $newEndDateTime = Carbon::parse($bookingDate . ' ' . $endTime);
        $durationMinutes = $newStartDateTime->diffInMinutes($newEndDateTime);

        if ($durationMinutes < 30) {
            return response()->json([
                'message' => 'La duración mínima de la reserva es de 30 minutos'
            ], 400);
        }

        if ($durationMinutes > 480) {
            return response()->json([
                'message' => 'La duración de la reserva no puede exceder 8 horas'
            ], 400);
        }

        // Verificar solapamiento con otras reservas
        if (Booking::hasOverlap($spaceId, $bookingDate, $startTime, $endTime, $booking->id)) {
            return response()->json([
                'message' => 'El espacio ya está reservado para el horario seleccionado'
            ], 409);
        }

        $booking->update($request->all());
        $booking->load('space');

        return response()->json([
            'message' => 'Reserva actualizada exitosamente',
            'booking' => $booking
        ]);
    }

    /**
     * @OA\Post(
     *     path="/bookings/{id}/cancel",
     *     summary="Cancelar una reserva",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva cancelada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="booking", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="La reserva ya está cancelada o es pasada"),
     *     @OA\Response(response=404, description="Reserva no encontrada")
     * )
     */
    public function cancel(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($booking->status === 'cancelled') {
            return response()->json([
                'message' => 'La reserva ya está cancelada'
            ], 400);
        }

        // No permitir cancelar reservas que ya pasaron
        $endDateTime = Carbon::parse($booking->end_datetime);
        if ($endDateTime->isPast()) {
            return response()->json([
                'message' => 'No se pueden cancelar reservas pasadas'
            ], 400);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Reserva cancelada exitosamente',
            'booking' => $booking
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/bookings/{id}",
     *     summary="Eliminar permanentemente una reserva",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Reserva no encontrada")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $booking->delete();

        return response()->json([
            'message' => 'Reserva eliminada exitosamente'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/spaces/{spaceId}/bookings",
     *     summary="Obtener reservas de un espacio en un rango de fechas",
     *     tags={"Spaces"},
     *     @OA\Parameter(name="spaceId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="start_date", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="end_date", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de reservas del espacio",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function getSpaceBookings($spaceId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $bookings = Booking::getForDateRange(
            $spaceId,
            $request->start_date,
            $request->end_date
        );

        return response()->json($bookings);
    }

    /**
     * @OA\Get(
     *     path="/bookings/all",
     *     summary="Obtener todas las reservas (para vista de calendario)",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="start_date", in="query", description="Fecha desde", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="end_date", in="query", description="Fecha hasta", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de todas las reservas activas",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function getAllBookings(Request $request)
    {
        $query = Booking::with(['space', 'user'])
            ->where('status', '!=', 'cancelled');

        // Filtrar por rango de fechas si se proporciona
        if ($request->has('start_date')) {
            $query->where('booking_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('booking_date', '<=', $request->end_date);
        }

        $bookings = $query->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json($bookings);
    }
}
