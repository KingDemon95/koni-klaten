<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\WorkflowNotification;

class WorkflowNotifier
{
    public static function toUser(
        ?User $user,
        string $title,
        string $message,
        string $url,
        string $category = 'info',
        ?int $exceptUserId = null,
    ): void {
        if (! $user || ! $user->is_active || $user->id === $exceptUserId) {
            return;
        }

        $user->notify(new WorkflowNotification($title, $message, $url, $category));
    }

    public static function toRole(
        string $role,
        string $title,
        string $message,
        string $url,
        string $category = 'info',
        ?int $caborId = null,
        ?int $exceptUserId = null,
    ): void {
        $guard = config('auth.defaults.guard', 'web');

        // Gunakan relasi roles secara langsung. Scope ->role($role) milik
        // Spatie melempar RoleDoesNotExist jika role belum tersedia.
        // Untuk alur notifikasi, role tanpa penerima harus berarti "tidak ada
        // penerima", bukan menggagalkan proses bisnis utama.
        $users = User::query()
            ->where('is_active', true)
            ->whereHas('roles', static function ($query) use ($role, $guard): void {
                $query->where('name', $role)
                    ->where('guard_name', $guard);
            })
            ->when($caborId !== null, fn ($query) => $query->where('cabang_olahraga_id', $caborId))
            ->when($exceptUserId !== null, fn ($query) => $query->where('id', '!=', $exceptUserId))
            ->get();

        foreach ($users as $user) {
            self::toUser($user, $title, $message, $url, $category, $exceptUserId);
        }
    }

    public static function toCaborAdmins(
        int $caborId,
        string $title,
        string $message,
        string $url,
        string $category,
    ): void {
        self::toRole('admin_cabor', $title, $message, $url, $category, $caborId);
    }
}
