<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surat extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_REVISI = 'revisi';
    public const STATUS_DITERUSKAN = 'diteruskan_admin';
    public const STATUS_PERLU_REVISI_STAFF = 'perlu_revisi_staff';
    public const STATUS_MENUNGGU_PENYAMPAIAN = 'menunggu_penyampaian';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'cabang_olahraga_id',
        'created_by',
        'staff_id',
        'admin_koni_id',
        'perihal',
        'jenis_surat',
        'nomor_surat',
        'tanggal_surat',
        'ringkasan',
        'file_path',
        'file_original_name',
        'staff_attachment_path',
        'staff_attachment_original_name',
        'admin_response_path',
        'admin_response_original_name',
        'status',
        'keputusan_admin',
        'catatan_staff',
        'catatan_admin',
        'diajukan_at',
        'diteruskan_at',
        'diputuskan_at',
        'disampaikan_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'diajukan_at' => 'datetime',
            'diteruskan_at' => 'datetime',
            'diputuskan_at' => 'datetime',
            'disampaikan_at' => 'datetime',
        ];
    }

    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(CabangOlahraga::class, 'cabang_olahraga_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function adminKoni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_koni_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(SuratActivity::class)->with('actor')->latest();
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_DIAJUKAN => 'Menunggu pemeriksaan Staff',
            self::STATUS_REVISI => 'Perlu revisi Admin Cabor',
            self::STATUS_DITERUSKAN => 'Diteruskan ke Admin KONI',
            self::STATUS_PERLU_REVISI_STAFF => 'Keputusan revisi menunggu Staff',
            self::STATUS_MENUNGGU_PENYAMPAIAN => 'Menunggu penyampaian hasil',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }
}
