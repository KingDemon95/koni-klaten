<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'actor_id',
        'actor_name',
        'actor_role',
        'event',
        'subject_type',
        'subject_id',
        'subject_label',
        'cabang_olahraga_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'method',
        'route_name',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Riwayat aktivitas bersifat permanen dan tidak dapat diubah.');
        });

        static::deleting(function (): never {
            throw new LogicException('Riwayat aktivitas bersifat permanen dan tidak dapat dihapus.');
        });
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function cabangOlahraga(): BelongsTo
    {
        return $this->belongsTo(CabangOlahraga::class, 'cabang_olahraga_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    public static function eventLabels(): array
    {
        return [
            'created' => 'Membuat data',
            'updated' => 'Memperbarui data',
            'deleted' => 'Menghapus data',
            'restored' => 'Memulihkan data',
            'approved' => 'Menyetujui',
            'rejected' => 'Menolak',
            'published' => 'Mempublikasikan',
            'revision_requested' => 'Meminta revisi',
            'submitted' => 'Mengajukan data',
            'forwarded' => 'Meneruskan proses',
            'status_changed' => 'Mengubah status',
            'role_assigned' => 'Memberikan hak akses',
            'role_changed' => 'Mengubah hak akses',
            'password_changed' => 'Mengubah kata sandi',
            'login' => 'Login ke sistem',
            'logout' => 'Logout dari sistem',
        ];
    }

    public function getEventLabelAttribute(): string
    {
        return self::eventLabels()[$this->event] ?? ucfirst(str_replace('_', ' ', $this->event));
    }

    public function getModuleLabelAttribute(): string
    {
        if (in_array($this->event, ['login', 'logout'], true)) {
            return 'Autentikasi';
        }

        $labels = [
            CabangOlahraga::class => 'Cabang olahraga',
            Atlet::class => 'Atlet',
            Pelatih::class => 'Pelatih',
            Pengurus::class => 'Struktur organisasi',
            Prestasi::class => 'Prestasi',
            Agenda::class => 'Agenda',
            Surat::class => 'Persuratan',
            TemplateSurat::class => 'Template surat',
            Publikasi::class => 'Publikasi',
            IzinLatihan::class => 'Izin latihan',
            ProfilKoni::class => 'Profil KONI',
            InventoryCategory::class => 'Kategori inventaris',
            InventoryItem::class => 'Inventaris',
            InventoryMovement::class => 'Mutasi inventaris',
            InventoryLoan::class => 'Peminjaman inventaris',
            User::class => 'Akun pengguna',
        ];

        return $labels[$this->subject_type] ?? 'Sistem';
    }
}
