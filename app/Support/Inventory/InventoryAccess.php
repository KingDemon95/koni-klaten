<?php

namespace App\Support\Inventory;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class InventoryAccess
{
    public function visibleItemsQuery(User $user): Builder
    {
        if ($user->hasAnyRole(['admin_koni', 'ketua_koni', 'sekretaris_koni'])) {
            return InventoryItem::query();
        }

        if ($user->hasRole('staff_koni')) {
            return InventoryItem::query()->whereNull('cabang_olahraga_id');
        }

        if ($user->hasRole('admin_cabor') && $user->cabang_olahraga_id) {
            return InventoryItem::query()->where('cabang_olahraga_id', $user->cabang_olahraga_id);
        }

        return InventoryItem::query()->whereRaw('1 = 0');
    }

    public function visibleCategoriesQuery(User $user): Builder
    {
        if ($user->hasAnyRole(['admin_koni', 'ketua_koni', 'sekretaris_koni'])) {
            return InventoryCategory::query();
        }

        if ($user->hasRole('staff_koni')) {
            return InventoryCategory::query()->whereNull('cabang_olahraga_id');
        }

        if ($user->hasRole('admin_cabor') && $user->cabang_olahraga_id) {
            return InventoryCategory::query()->where(function (Builder $query) use ($user): void {
                $query->whereNull('cabang_olahraga_id')
                    ->orWhere('cabang_olahraga_id', $user->cabang_olahraga_id);
            });
        }

        return InventoryCategory::query()->whereRaw('1 = 0');
    }

    public function categoryMatchesScope(InventoryCategory $category, ?int $caborId): bool
    {
        if ($caborId === null) {
            return $category->cabang_olahraga_id === null;
        }

        return $category->cabang_olahraga_id === null
            || (int) $category->cabang_olahraga_id === $caborId;
    }

    public function routePrefix(User $user): string
    {
        return match (true) {
            $user->hasAnyRole(['admin_koni', 'ketua_koni']) => 'admin',
            $user->hasRole('admin_cabor') => 'cabor',
            $user->hasRole('staff_koni') => 'staff',
            $user->hasRole('sekretaris_koni') => 'sekretaris',
            default => 'admin',
        };
    }
}
