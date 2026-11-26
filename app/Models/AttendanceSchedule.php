<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSchedule extends Model
{
    protected $fillable = [
        'schedule_type',
        'cabang_olahraga_id',
        'created_by',
        'title',
        'effective_from',
        'effective_until',
        'version',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'version' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(CabangOlahraga::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(AttendanceScheduleSlot::class)
            ->orderBy('day_of_week')
            ->orderBy('sort_order');
    }
}
