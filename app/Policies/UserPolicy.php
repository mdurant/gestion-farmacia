<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::UsersManage->value);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(Permission::UsersManage->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::UsersManage->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(Permission::UsersManage->value);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can(Permission::UsersManage->value) && $user->id !== $model->id;
    }

    public function restore(User $user): bool
    {
        return $user->can(Permission::UsersManage->value);
    }

    public function toggleActive(User $user, User $model): bool
    {
        return $user->can(Permission::UsersManage->value)
            && $user->id !== $model->id
            && ! $model->isPendingActivation();
    }

    public function resendActivation(User $user, User $model): bool
    {
        return $user->can(Permission::UsersManage->value) && $model->isPendingActivation();
    }
}
