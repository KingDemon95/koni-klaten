<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'cabang_olahraga_id',
    'is_active',
])]

#[Hidden([
    'password',
    'remember_token',
])]

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke cabang olahraga.
     * Digunakan terutama oleh Admin Cabor.
     */
    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(
            CabangOlahraga::class,
            'cabang_olahraga_id'
        );
    }

    /**
     * Relasi ke profil atlet.
     */
    public function atlet(): HasOne
    {
        return $this->hasOne(Atlet::class);
    }

    /**
     * Relasi ke profil pelatih.
     */
    public function pelatih(): HasOne
    {
        return $this->hasOne(Pelatih::class);
    }
}
