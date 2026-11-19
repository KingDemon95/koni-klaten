<?php

namespace App\Support\Audit;

use App\Models\AuditLog;
use App\Models\CabangOlahraga;
use App\Models\InventoryLoan;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * Simpan perubahan model tanpa memasukkan kredensial atau data kontak ke log.
     */
    public function recordModel(Model $subject, string $operation): void
    {
        if ($subject instanceof AuditLog) {
            return;
        }

        $actor = Auth::user();

        if (! $actor instanceof User) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        if ($operation === 'created') {
            $newValues = $subject->getAttributes();
        } elseif (in_array($operation, ['updated', 'restored'], true)) {
            foreach ($subject->getChanges() as $key => $value) {
                if (in_array($key, ['created_at', 'updated_at', 'deleted_at', 'id'], true)) {
                    continue;
                }

                $oldValues[$key] = $subject->getRawOriginal($key);
                $newValues[$key] = $subject->getAttributes()[$key] ?? $value;
            }
        } elseif (in_array($operation, ['deleted', 'forceDeleted'], true)) {
            $oldValues = $subject->getAttributes();
            $operation = 'deleted';
        }

        $safeOld = $this->safeValues($oldValues);
        $safeNew = $this->safeValues($newValues);

        $event = $this->classifyEvent($operation, $oldValues, $newValues);

        if ($operation === 'updated' && $safeOld === [] && $safeNew === []) {
            if (array_key_exists('password', $newValues)) {
                $event = 'password_changed';
            } elseif ($newValues === []) {
                return;
            }
        }

        $this->store($actor, $event, $subject, $safeOld, $safeNew);
    }

    /** Catat aktivitas khusus yang tidak memicu event model, misalnya perubahan role. */
    public function recordCustom(
        string $event,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        ?User $actor = null,
    ): void {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            return;
        }

        $this->store(
            $actor,
            $event,
            $subject,
            $this->safeValues($oldValues),
            $this->safeValues($newValues),
        );
    }

    private function store(
        User $actor,
        string $event,
        ?Model $subject,
        array $oldValues,
        array $newValues,
    ): void {
        $request = request();
        $roles = $actor->getRoleNames()->take(4)->implode(', ');

        AuditLog::query()->create([
            'actor_id' => $actor->getKey(),
            'actor_name' => Str::limit((string) $actor->name, 255, ''),
            'actor_role' => Str::limit($roles, 120, ''),
            'event' => Str::limit($event, 40, ''),
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $subject ? $this->subjectLabel($subject) : 'Sesi pengguna',
            'cabang_olahraga_id' => $subject ? $this->branchId($subject) : null,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'method' => Str::limit((string) $request->method(), 10, ''),
            'route_name' => Str::limit((string) ($request->route()?->getName() ?? ''), 160, '') ?: null,
            'created_at' => now(),
        ]);
    }

    private function classifyEvent(string $operation, array $oldValues, array $newValues): string
    {
        if ($operation !== 'updated') {
            return $operation;
        }

        $decision = $newValues['keputusan_admin'] ?? null;

        if (in_array($decision, ['diterima', 'disetujui'], true)) {
            return 'approved';
        }

        if ($decision === 'ditolak') {
            return 'rejected';
        }

        if ($decision === 'revisi') {
            return 'revision_requested';
        }

        $status = $newValues['status'] ?? null;

        if (in_array($status, ['terverifikasi', 'diterima', 'disetujui', 'approved'], true)) {
            return 'approved';
        }

        if (in_array($status, ['ditolak', 'rejected'], true)) {
            return 'rejected';
        }

        if (in_array($status, ['published', 'dipublikasikan'], true)) {
            return 'published';
        }

        if (in_array($status, ['perlu_revisi', 'revisi', 'perlu_revisi_staff'], true)) {
            return 'revision_requested';
        }

        if (in_array($status, ['diajukan', 'menunggu_verifikasi'], true)) {
            return 'submitted';
        }

        if ($status === 'diteruskan_admin') {
            return 'forwarded';
        }

        if (array_key_exists('status', $newValues) && ($oldValues['status'] ?? null) !== $status) {
            return 'status_changed';
        }

        return 'updated';
    }

    private function safeValues(array $values): array
    {
        $safe = [];

        foreach ($values as $key => $value) {
            $key = (string) $key;

            if (
                in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at'], true)
                || preg_match('/password|token|secret|recovery|(^|_)email$|alamat|telepon|phone|kontak|tanggal_lahir|tempat_lahir|jenis_kelamin|peminjam|tujuan|keperluan|(^|_)path$|_original_name$|two_factor/i', $key)
            ) {
                continue;
            }

            if (is_string($value)) {
                $value = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email disamarkan]', $value) ?? $value;
                $value = preg_replace('/(?<!\d)(?:\+?62|0)[\d\s().-]{8,15}(?!\d)/', '[nomor kontak disamarkan]', $value) ?? $value;
                $safe[$key] = Str::limit(trim($value), 500, '…');
            } elseif (is_scalar($value) || $value === null) {
                $safe[$key] = $value;
            } elseif (is_array($value)) {
                $safe[$key] = array_slice($value, 0, 20);
            }
        }

        return $safe;
    }

    private function subjectLabel(Model $subject): string
    {
        $attributes = $subject->getAttributes();

        foreach ([
            'nama_lengkap',
            'nama_barang',
            'nama_kejuaraan',
            'nama',
            'judul',
            'perihal',
            'kode_barang',
            'name',
        ] as $key) {
            if (isset($attributes[$key]) && trim((string) $attributes[$key]) !== '') {
                return Str::limit((string) $attributes[$key], 255, '');
            }
        }

        return class_basename($subject).' #'.$subject->getKey();
    }

    private function branchId(Model $subject): ?int
    {
        if ($subject instanceof CabangOlahraga) {
            return (int) $subject->getKey();
        }

        $branchId = $subject->getAttribute('cabang_olahraga_id');

        if ($branchId) {
            return (int) $branchId;
        }

        if ($subject instanceof InventoryMovement || $subject instanceof InventoryLoan) {
            return $subject->item()->value('cabang_olahraga_id');
        }

        return null;
    }
}
