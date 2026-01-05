<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'capacity',
        'photos',
        'available_hours',
        'is_active',
    ];

    protected $casts = [
        'photos' => 'array',
        'available_hours' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the bookings for this space.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if space is available for a given time range.
     */
    public function isAvailable($startTime, $endTime, $excludeBookingId = null)
    {
        $query = $this->bookings()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }
}
