<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengurus extends Model
{
    protected $table = 'pengurus';

    protected $fillable = [
        'cabang_olahraga_id',
        'nama_lengkap',
        'jabatan',
        'bidang',
        'jenis_kelamin',
        'periode_mulai',
        'periode_selesai',
        'no_telepon',
        'email',
        'foto_path',
        'urutan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'integer',
            'periode_selesai' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(
            CabangOlahraga::class,
            'cabang_olahraga_id'
        );
    }
}