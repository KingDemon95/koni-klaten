<?php

namespace App\Policies;

use App\Models\Atlet;
use App\Models\User;

class AtletPolicy
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
        return $this->viewAny($user);
    }

    public function view(User $user, Atlet $atlet): bool
    {
        return $this->owns($user, $atlet);
    }

    public function update(User $user, Atlet $atlet): bool
    {
        return $this->owns($user, $atlet);
    }

    public function delete(User $user, Atlet $atlet): bool
    {
        return $this->owns($user, $atlet);
    }

    private function isAdminCaborAktif(User $user): bool
    {
        return $user->hasRole('admin_cabor')
            && $user->cabang_olahraga_id !== null
            && $user->cabangOlahraga?->status === 'aktif';
    }

    private function owns(User $user, Atlet $atlet): bool
    {
        return $this->isAdminCaborAktif($user)
            && (int) $user->cabang_olahraga_id
                === (int) $atlet->cabang_olahraga_id;
    }
}
