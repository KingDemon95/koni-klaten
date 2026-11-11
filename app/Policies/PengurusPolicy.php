<?php

namespace App\Policies;

use App\Models\Pengurus;
use App\Models\User;

class PengurusPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin_koni') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdminCaborAktif($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminCaborAktif($user);
    }

    public function view(User $user, Pengurus $pengurus): bool
    {
        return $this->isOwner($user, $pengurus);
    }

    public function update(User $user, Pengurus $pengurus): bool
    {
        return $this->isOwner($user, $pengurus);
    }

    public function delete(User $user, Pengurus $pengurus): bool
    {
        return $this->isOwner($user, $pengurus);
    }

    private function isAdminCaborAktif(User $user): bool
    {
        return $user->hasRole('admin_cabor')
            && $user->cabang_olahraga_id !== null
            && $user->cabangOlahraga?->status === 'aktif';
    }

    private function isOwner(User $user, Pengurus $pengurus): bool
    {
        return $this->isAdminCaborAktif($user)
            && $pengurus->cabang_olahraga_id !== null
            && (int) $user->cabang_olahraga_id
                === (int) $pengurus->cabang_olahraga_id;
    }
}
