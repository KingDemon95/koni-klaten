<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLoan extends Model
{
    protected $table = 'inventory_loans';

    protected $fillable = [
        'inventory_item_id',
        'peminjam',
        'kontak_peminjam',
        'unit_peminjam',
        'jumlah',
        'tanggal_pinjam',
        'batas_pengembalian',
        'tanggal_dikembalikan',
        'status',
        'kondisi_pengembalian',
        'catatan',
        'catatan_pengembalian',
        'created_by',
        'returned_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'batas_pengembalian' => 'date',
            'tanggal_dikembalikan' => 'datetime',
            'jumlah' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
