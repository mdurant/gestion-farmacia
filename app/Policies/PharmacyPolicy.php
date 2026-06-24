<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Pharmacy;
use App\Models\User;

class PharmacyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function view(User $user, Pharmacy $pharmacy): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }

    public function update(User $user, Pharmacy $pharmacy): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }

    public function delete(User $user, Pharmacy $pharmacy): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }
}
