<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class ReportPolicy
{
    public function viewInternal(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ReportsInternal->value);
    }

    public function viewExecutive(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ReportsExecutive->value);
    }
}
