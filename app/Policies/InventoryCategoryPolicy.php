<?php

namespace App\Policies;

use App\Models\InventoryCategory;
use App\Models\User;

class InventoryCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'admin_koni', 'ketua_koni', 'sekretaris_koni', 'staff_koni', 'admin_cabor',
        ]);
    }

    public function view(User $user, InventoryCategory $category): bool
    {
        if (!$this->viewAny($user)) {
            return false;
        }

        if ($user->hasRole('admin_cabor')) {
            return $category->cabang_olahraga_id === null
                || (int) $category->cabang_olahraga_id === (int) $user->cabang_olahraga_id;
        }

        if ($user->hasRole('staff_koni')) {
            return $category->cabang_olahraga_id === null;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin_koni', 'ketua_koni'])
            || ($user->hasRole('admin_cabor') && $user->cabang_olahraga_id !== null);
    }

    public function update(User $user, InventoryCategory $category): bool
    {
        return $user->hasAnyRole(['admin_koni', 'ketua_koni'])
            || ($user->hasRole('admin_cabor')
                && $user->cabang_olahraga_id !== null
                && (int) $category->cabang_olahraga_id === (int) $user->cabang_olahraga_id);
    }

    public function delete(User $user, InventoryCategory $category): bool
    {
        return $this->update($user, $category);
    }
}
