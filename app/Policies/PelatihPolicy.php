<?php

namespace App\Policies;

use App\Models\Pelatih;
use App\Models\User;

class PelatihPolicy
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

    public function view(User $user, Pelatih $pelatih): bool
    {
        return $this->owns($user, $pelatih);
    }

    public function update(User $user, Pelatih $pelatih): bool
    {
        return $this->owns($user, $pelatih);
    }

    public function delete(User $user, Pelatih $pelatih): bool
    {
        return $this->owns($user, $pelatih);
    }

    private function isAdminCaborAktif(User $user): bool
    {
        return $user->hasRole('admin_cabor')
            && $user->cabang_olahraga_id !== null
            && $user->cabangOlahraga?->status === 'aktif';
    }

    private function owns(User $user, Pelatih $pelatih): bool
    {
        return $this->isAdminCaborAktif($user)
            && (int) $user->cabang_olahraga_id
                === (int) $pelatih->cabang_olahraga_id;
    }
}
