<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardSetting extends Model
{
    protected $fillable = [
        'backdrop_path',
        'overlay_opacity',
        'background_position',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'overlay_opacity' => 'integer',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'overlay_opacity' => 55,
                'background_position' => 'center',
            ],
        );
    }
}
