<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes;

    public const CONDITIONS = [
        'baik' => 'Baik',
        'rusak_ringan' => 'Rusak ringan',
        'rusak_berat' => 'Rusak berat',
        'tidak_layak' => 'Tidak layak pakai',
    ];

    protected $table = 'inventory_items';

    protected $fillable = [
        'kode_barang',
        'inventory_category_id',
        'cabang_olahraga_id',
        'nama_barang',
        'deskripsi',
        'jumlah',
        'satuan',
        'kondisi',
        'lokasi',
        'tanggal_perolehan',
        'harga_perolehan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_perolehan' => 'date',
            'harga_perolehan' => 'decimal:2',
            'jumlah' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(CabangOlahraga::class, 'cabang_olahraga_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_item_id')->latest('tanggal_mutasi');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class, 'inventory_item_id')->latest('tanggal_pinjam');
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(InventoryLoan::class, 'inventory_item_id')->where('status', 'dipinjam');
    }

    public function availableQuantity(): int
    {
        $loaned = $this->relationLoaded('activeLoans')
            ? $this->activeLoans->sum('jumlah')
            : $this->activeLoans()->sum('jumlah');

        return max(0, (int) $this->jumlah - (int) $loaned);
    }
}
