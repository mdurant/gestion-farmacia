<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\CostCenter;
use App\Models\User;

class CostCenterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function view(User $user, CostCenter $costCenter): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }

    public function update(User $user, CostCenter $costCenter): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }

    public function delete(User $user, CostCenter $costCenter): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }
}
