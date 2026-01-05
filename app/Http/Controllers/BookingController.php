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
     * Display a listing of the authenticated user's bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::where('user_id', $request->user()->id)
            ->with('space');

        // Filtrar por estado
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filtrar por fecha
        if ($request->has('from_date')) {
            $query->where('start_time', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('end_time', '<=', $request->to_date);
        }

        $bookings = $query->orderBy('start_time', 'desc')->paginate(10);

        return response()->json($bookings);
    }

    /**
     * Display the specified booking.
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
     * Store a newly created booking.
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
     * Update the specified booking.
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // No permitir modificar reservas pasadas o canceladas
        if ($booking->end_time < now()) {
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Si se están modificando fechas u horarios, verificar solapamiento
        $spaceId = $request->space_id ?? $booking->space_id;
        $bookingDate = $request->booking_date ?? $booking->booking_date;
        $startTime = $request->start_time ?? $booking->start_time;
        $endTime = $request->end_time ?? $booking->end_time;

        if (Booking::hasOverlap($spaceId, $bookingDate, $startTime, $endTime, $booking->id)) {
            return response()->json([
                'message' => 'El espacio ya está reservado para el horario seleccionado'
            ], 409);
        }

        // Validar duración máxima (8 horas = 480 minutos)
        if ($request->has('start_time') && $request->has('end_time')) {
            $startDateTime = Carbon::parse($bookingDate . ' ' . $startTime);
            $endDateTime = Carbon::parse($bookingDate . ' ' . $endTime);
            $durationMinutes = $endDateTime->diffInMinutes($startDateTime);
            if ($durationMinutes > 480) {
                return response()->json([
                    'message' => 'La duración de la reserva no puede exceder 8 horas'
                ], 400);
            }
        }

        $booking->update($request->all());
        $booking->load('space');

        return response()->json([
            'message' => 'Reserva actualizada exitosamente',
            'booking' => $booking
        ]);
    }

    /**
     * Cancel the specified booking.
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
        if ($booking->end_time < now()) {
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
     * Remove the specified booking (hard delete).
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
     * Get bookings for a specific space and date range.
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
     * Get all bookings with optional date range filter (for calendar view).
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
