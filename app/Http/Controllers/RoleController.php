<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $roles = Role::query()
            ->with('permissions')
            ->whereIn('name', UserRole::values())
            ->get()
            ->sortBy(fn (Role $role) => array_search($role->name, UserRole::values(), true));

        $permissionsByGroup = collect(Permission::cases())
            ->groupBy(fn (Permission $permission) => $permission->group())
            ->sortBy(fn ($_, string $group) => array_search($group, Permission::groupOrder(), true));

        return view('roles.index', [
            'roles' => $roles,
            'permissionsByGroup' => $permissionsByGroup,
            'userCounts' => User::query()
                ->selectRaw('role, count(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),
        ]);
    }
}
