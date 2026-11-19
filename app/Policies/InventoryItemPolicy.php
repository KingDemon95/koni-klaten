<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'admin_koni', 'ketua_koni', 'sekretaris_koni', 'staff_koni', 'admin_cabor',
        ]);
    }

    public function view(User $user, InventoryItem $item): bool
    {
        if (!$this->viewAny($user)) {
            return false;
        }

        if ($user->hasRole('admin_cabor')) {
            return $user->cabang_olahraga_id !== null
                && (int) $item->cabang_olahraga_id === (int) $user->cabang_olahraga_id;
        }

        if ($user->hasRole('staff_koni')) {
            return $item->cabang_olahraga_id === null;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin_koni', 'ketua_koni'])
            || ($user->hasRole('admin_cabor') && $user->cabang_olahraga_id !== null);
    }

    public function update(User $user, InventoryItem $item): bool
    {
        return $user->hasAnyRole(['admin_koni', 'ketua_koni'])
            || ($user->hasRole('admin_cabor')
                && $user->cabang_olahraga_id !== null
                && (int) $item->cabang_olahraga_id === (int) $user->cabang_olahraga_id);
    }

    public function delete(User $user, InventoryItem $item): bool
    {
        return $this->update($user, $item);
    }

    public function move(User $user, InventoryItem $item): bool
    {
        return $user->hasAnyRole(['admin_koni', 'ketua_koni'])
            || ($user->hasRole('admin_cabor')
                && $user->cabang_olahraga_id !== null
                && (int) $item->cabang_olahraga_id === (int) $user->cabang_olahraga_id)
            || ($user->hasRole('staff_koni') && $item->cabang_olahraga_id === null);
    }

    public function createLoan(User $user, InventoryItem $item): bool
    {
        return $item->status === 'aktif' && $this->move($user, $item);
    }

    public function returnLoan(User $user, InventoryItem $item): bool
    {
        return $this->move($user, $item);
    }
}
