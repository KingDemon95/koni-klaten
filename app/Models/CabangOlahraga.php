<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CabangOlahraga extends Model
{
    protected $table = 'cabang_olahraga';

    protected $fillable = [
        'nama',
        'singkatan',
        'deskripsi',
        'logo_path',
        'nama_ketua',
        'email',
        'telepon',
        'alamat',
        'status',
    ];

    /**
     * Relasi ke data atlet.
     */
    public function atlets(): HasMany
    {
        return $this->hasMany(
            Atlet::class,
            'cabang_olahraga_id'
        );
    }

    /**
     * Relasi ke data pelatih.
     */
    public function pelatihs(): HasMany
    {
        return $this->hasMany(
            Pelatih::class,
            'cabang_olahraga_id'
        );
    }

    /**
     * Relasi ke data pengurus.
     */
    public function pengurus(): HasMany
    {
        return $this->hasMany(
            Pengurus::class,
            'cabang_olahraga_id'
        );
    }

    /** Relasi agenda dan kejuaraan milik cabang olahraga. */
    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class, 'cabang_olahraga_id');
    }

    public function prestasis(): HasMany
    {
        return $this->hasMany(Prestasi::class, 'cabang_olahraga_id');
    }

    public function izinLatihans(): HasMany
    {
        return $this->hasMany(IzinLatihan::class, 'cabang_olahraga_id');
    }
}