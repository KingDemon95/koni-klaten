<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prestasi extends Model
{
    protected $table = 'prestasis';

    protected $fillable = [
        'atlet_id', 'pelatih_id', 'cabang_olahraga_id', 'verified_by',
        'nama_kejuaraan', 'tingkat', 'kategori', 'hasil', 'jenis_medali',
        'penyelenggara', 'tanggal_prestasi', 'keterangan',
        'bukti_path', 'bukti_original_name', 'status',
        'catatan_verifikasi', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_prestasi' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function atlet(): BelongsTo
    {
        return $this->belongsTo(Atlet::class);
    }

    public function pelatih(): BelongsTo
    {
        return $this->belongsTo(Pelatih::class);
    }

    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(CabangOlahraga::class, 'cabang_olahraga_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
