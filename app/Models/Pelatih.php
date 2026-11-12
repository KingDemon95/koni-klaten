<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelatih extends Model
{
    protected $table = 'pelatihs';

    protected $fillable = [
        'cabang_olahraga_id',
        'user_id',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_telepon',
        'email',
        'pendidikan_terakhir',
        'spesialisasi',
        'sertifikasi',
        'pengalaman_tahun',
        'foto_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'pengalaman_tahun' => 'integer',
        ];
    }

    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(
            CabangOlahraga::class,
            'cabang_olahraga_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prestasis(): HasMany
    {
        return $this->hasMany(Prestasi::class);
    }

    public function izinLatihans(): HasMany
    {
        return $this->hasMany(IzinLatihan::class, 'user_id', 'user_id');
    }
}
