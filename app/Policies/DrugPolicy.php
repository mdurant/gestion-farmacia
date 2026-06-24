<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Drug;
use App\Models\User;

class DrugPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function view(User $user, Drug $drug): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }

    public function update(User $user, Drug $drug): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }

    public function delete(User $user, Drug $drug): bool
    {
        return $user->can(Permission::UsersManage->value);
    }

    public function authorizeControlled(User $user, Drug $drug): bool
    {
        return $user->can(Permission::ControlledDrugAuthorize->value)
            || ! ($drug->is_controlled || $drug->is_narcotic);
    }
}
