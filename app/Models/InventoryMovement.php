<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movements';

    protected $fillable = [
        'inventory_item_id',
        'lokasi_asal',
        'lokasi_tujuan',
        'jumlah',
        'tanggal_mutasi',
        'catatan',
        'moved_by',
    ];

    protected function casts(): array
    {
        return ['tanggal_mutasi' => 'datetime'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
