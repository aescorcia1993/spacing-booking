<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'space_id',
        'booking_date',
        'start_time',
        'end_time',
        'attendees',
        'purpose',
        'status',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'attendees' => 'integer',
    ];

    protected $appends = [
        'start_datetime',
        'end_datetime',
        'duration_minutes',
        'computed_status',
    ];

    /**
     * Get the full start datetime by combining booking_date and start_time.
     */
    public function getStartDatetimeAttribute()
    {
        if (!$this->booking_date || !$this->start_time) {
            return null;
        }
        
        return Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time)
            ->format('Y-m-d H:i:s');
    }

    /**
     * Get the full end datetime by combining booking_date and end_time.
     */
    public function getEndDatetimeAttribute()
    {
        if (!$this->booking_date || !$this->end_time) {
            return null;
        }
        
        return Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->end_time)
            ->format('Y-m-d H:i:s');
    }

    /**
     * Calculate the duration in minutes.
     */
    public function getDurationMinutesAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        return $start->diffInMinutes($end);
    }

    /**
     * Get the computed status based on current date/time.
     * If the event has passed, return 'completed', otherwise return the stored status.
     */
    public function getComputedStatusAttribute()
    {
        // If already cancelled, keep it as cancelled
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        // Check if the booking has ended
        if ($this->end_datetime) {
            $endDateTime = Carbon::parse($this->end_datetime);
            if ($endDateTime->isPast()) {
                return 'completed';
            }
        }

        // Return the stored status (pending or confirmed)
        return $this->status;
    }

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the space that is booked.
     */
    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Check if there's an overlap with another booking for the same space.
     */
    public static function hasOverlap($spaceId, $bookingDate, $startTime, $endTime, $excludeBookingId = null)
    {
        $query = self::where('space_id', $spaceId)
            ->where('booking_date', $bookingDate)
            ->where('status', '!=', 'cancelled')
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

        return $query->exists();
    }

    /**
     * Get bookings for a specific date range.
     */
    public static function getForDateRange($spaceId, $startDate, $endDate)
    {
        return self::where('space_id', $spaceId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->with('user')
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();
    }
}
