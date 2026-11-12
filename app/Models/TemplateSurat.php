<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateSurat extends Model
{
    protected $table = 'template_surats';

    protected $fillable = [
        'judul',
        'deskripsi',
        'file_path',
        'file_original_name',
        'uploaded_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
