<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilKoni extends Model
{
    protected $table = 'profil_koni';

    protected $fillable = [
        'nama_organisasi',
        'nama_singkat',
        'tagline',
        'deskripsi',
        'sejarah',
        'visi',
        'misi',
        'alamat',
        'telepon',
        'email',
        'website',
        'instagram',
        'facebook',
        'youtube',
        'logo_path',
        'updated_by',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([
            'nama_organisasi' => 'Komite Olahraga Nasional Indonesia Kabupaten Klaten',
            'nama_singkat' => 'KONI Klaten',
            'tagline' => 'Bersama membangun prestasi olahraga Kabupaten Klaten.',
        ]);
    }
}
