<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Publikasi extends Model
{
    public const JENIS_BERITA = 'berita';
    public const JENIS_FOTO = 'foto';
    public const JENIS_VIDEO = 'video';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $table = 'publikasis';

    protected $fillable = [
        'created_by',
        'updated_by',
        'published_by',
        'judul',
        'slug',
        'jenis',
        'kategori',
        'ringkasan',
        'konten',
        'media_path',
        'media_original_name',
        'thumbnail_path',
        'thumbnail_original_name',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public static function jenisLabels(): array
    {
        return [
            self::JENIS_BERITA => 'Berita',
            self::JENIS_FOTO => 'Foto',
            self::JENIS_VIDEO => 'Video',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Dipublikasikan',
        ];
    }
}
