<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratMessage extends Model
{
    public const TYPE_DISPOSISI = 'disposisi';
    public const TYPE_BALASAN = 'balasan';

    protected $fillable = [
        'surat_id',
        'sender_id',
        'sender_role',
        'message_type',
        'body',
        'attachment_path',
        'attachment_original_name',
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function senderRoleLabel(): string
    {
        return match ($this->sender_role) {
            'staff_koni' => 'Staff KONI',
            'admin_cabor' => 'Admin Cabor',
            'ketua_koni' => 'Ketua KONI',
            'sekretaris_koni' => 'Sekretaris KONI',
            'admin_koni' => 'Admin KONI',
            default => 'Pengguna',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->message_type) {
            self::TYPE_DISPOSISI => 'Disposisi Staff',
            self::TYPE_BALASAN => 'Balasan Cabor',
            default => 'Pesan',
        };
    }
}
