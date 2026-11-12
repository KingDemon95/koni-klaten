<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IzinLatihan extends Model
{
    protected $table = 'izin_latihans';

    protected $fillable = [
        'user_id', 'cabang_olahraga_id', 'jenis_pemohon', 'nama_pemohon',
        'tujuan', 'keperluan', 'tanggal_mulai', 'tanggal_selesai',
        'file_path', 'file_original_name', 'status', 'catatan_admin', 'diproses_oleh', 'diproses_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'diproses_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(CabangOlahraga::class, 'cabang_olahraga_id');
    }
}
