<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratActivity extends Model
{
    protected $fillable = [
        'surat_id',
        'actor_id',
        'action',
        'status_from',
        'status_to',
        'catatan',
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
