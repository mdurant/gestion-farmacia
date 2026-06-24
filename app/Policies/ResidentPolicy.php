<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Resident;
use App\Models\User;

class ResidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ResidentsView->value);
    }

    public function view(User $user, Resident $resident): bool
    {
        return $user->can(Permission::ResidentsView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::ResidentsManage->value);
    }

    public function update(User $user, Resident $resident): bool
    {
        return $user->can(Permission::ResidentsManage->value);
    }

    public function delete(User $user, Resident $resident): bool
    {
        return $user->can(Permission::ResidentsManage->value);
    }
}
