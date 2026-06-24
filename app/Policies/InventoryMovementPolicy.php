<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\InventoryMovement;
use App\Models\User;

class InventoryMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function view(User $user, InventoryMovement $inventoryMovement): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::InventoryMove->value);
    }

    public function registerWaste(User $user): bool
    {
        return $user->can(Permission::InventoryWaste->value);
    }
}
