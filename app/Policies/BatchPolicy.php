<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function view(User $user, Batch $batch): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function update(User $user, Batch $batch): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }

    public function delete(User $user, Batch $batch): bool
    {
        return $user->can(Permission::PharmaciesManage->value);
    }
}
