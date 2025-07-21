<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'task_description',
        'hours',
        'minutes',
        'total_minutes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours' => 'integer',
        'minutes' => 'integer',
        'total_minutes' => 'integer',
    ];

    /**
     * Get the user that owns the time log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total minutes from hours and minutes
     */
    public function calculateTotalMinutes(): int
    {
        return ($this->hours * 60) + $this->minutes;
    }

    /**
     * Get formatted time string
     */
    public function getFormattedTimeAttribute(): string
    {
        return sprintf('%02d:%02d', $this->hours, $this->minutes);
    }

    /**
     * Get total hours as decimal
     */
    public function getTotalHoursAttribute(): float
    {
        return round($this->total_minutes / 60, 2);
    }
}