<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpaceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/spaces",
     *     summary="Listar espacios disponibles",
     *     tags={"Spaces"},
     *     @OA\Parameter(name="type", in="query", description="Filtrar por tipo de espacio", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="capacity", in="query", description="Capacidad mínima", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date", in="query", description="Fecha (YYYY-MM-DD)", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", description="Número de página", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de espacios paginada",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Sala Principal"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="type", type="string", example="meeting_room"),
     *                 @OA\Property(property="capacity", type="integer", example=10),
     *                 @OA\Property(property="photos", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="available_hours", type="object"),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total", type="integer", example=5)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Space::where('is_active', true);

        // Filtrar por búsqueda de nombre, descripción o tipo
        if ($request->has('search') && $request->search) {
            $search = strtolower($request->search); // Convertir a minúsculas
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(type) LIKE ?', ["%{$search}%"]);
            });
        }

        // Filtrar por tipo (solo si no hay búsqueda general)
        if ($request->has('type') && $request->type && !$request->has('search')) {
            $query->where('type', $request->type);
        }

        // Filtrar por capacidad mínima
        if ($request->has('capacity') && $request->capacity) {
            $query->where('capacity', '>=', $request->capacity);
        }

        // Filtrar por disponibilidad en fecha específica
        if ($request->has('date')) {
            // Esto se puede expandir para verificar disponibilidad real
            // Por ahora solo retorna todos los espacios activos
        }

        $spaces = $query->paginate(10);

        return response()->json($spaces);
    }

    /**
     * Display a listing of all spaces (admin only).
     */
    public function adminIndex(Request $request)
    {
        $query = Space::query();

        // Ordenamiento dinámico
        $sortField = $request->get('sort_field', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validar campos permitidos para ordenar
        $allowedSortFields = ['id', 'name', 'type', 'capacity', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        // Validar orden
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        // Obtener el número de registros por página (por defecto 10)
        $perPage = $request->get('per_page', 10);
        $perPage = min(max((int)$perPage, 1), 100); // Entre 1 y 100

        $spaces = $query->orderBy($sortField, $sortOrder)->paginate($perPage);
        return response()->json($spaces);
    }

    /**
     * Display the specified space.
     */
    public function show($id)
    {
        $space = Space::with(['bookings' => function ($query) {
            $query->where('status', '!=', 'cancelled')
                ->where('end_time', '>=', now())
                ->orderBy('start_time');
        }])->findOrFail($id);

        return response()->json($space);
    }

    /**
     * @OA\Post(
     *     path="/admin/spaces",
     *     summary="Crear nuevo espacio (Admin)",
     *     tags={"Admin - Spaces"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","description","type","capacity"},
     *             @OA\Property(property="name", type="string", example="Sala de Reuniones A"),
     *             @OA\Property(property="description", type="string", example="Sala equipada con proyector y pizarra"),
     *             @OA\Property(property="type", type="string", example="meeting_room"),
     *             @OA\Property(property="capacity", type="integer", example=10),
     *             @OA\Property(property="photos", type="array", @OA\Items(type="string", example="https://example.com/photo.jpg")),
     *             @OA\Property(property="available_hours", type="object",
     *                 @OA\Property(property="start", type="string", example="08:00"),
     *                 @OA\Property(property="end", type="string", example="18:00")
     *             ),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Espacio creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Espacio creado exitosamente"),
     *             @OA\Property(property="space", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado (requiere rol admin)"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'photos' => 'nullable|array',
            'photos.*' => 'url',
            'available_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $space = Space::create($request->all());

        return response()->json([
            'message' => 'Espacio creado exitosamente',
            'space' => $space
        ], 201);
    }

    /**
     * Update the specified space (admin only).
     */
    public function update(Request $request, $id)
    {
        $space = Space::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|string|max:100',
            'capacity' => 'sometimes|required|integer|min:1',
            'photos' => 'nullable|array',
            'photos.*' => 'url',
            'available_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $space->update($request->all());

        return response()->json([
            'message' => 'Espacio actualizado exitosamente',
            'space' => $space
        ]);
    }

    /**
     * Remove the specified space (admin only).
     */
    public function destroy($id)
    {
        $space = Space::findOrFail($id);

        // Verificar si tiene reservas activas
        $activeBookings = $space->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('end_time', '>=', now())
            ->count();

        if ($activeBookings > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el espacio porque tiene reservas activas'
            ], 400);
        }

        $space->delete();

        return response()->json([
            'message' => 'Espacio eliminado exitosamente'
        ]);
    }

    /**
     * Get available types of spaces.
     */
    public function types()
    {
        $types = Space::select('type')
            ->distinct()
            ->pluck('type');

        return response()->json($types);
    }

    /**
     * Check availability for a space in a date range.
     */
    public function checkAvailability($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $space = Space::findOrFail($id);
        $isAvailable = $space->isAvailable(
            $request->start_time,
            $request->end_time
        );

        return response()->json([
            'available' => $isAvailable,
            'space_id' => $id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);
    }
}
