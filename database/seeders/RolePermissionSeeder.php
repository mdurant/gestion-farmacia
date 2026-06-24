<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value);
        }

        $rolePermissions = [
            UserRole::Admin->value => Permission::values(),
            UserRole::MedicalDirector->value => [
                Permission::DashboardView->value,
                Permission::InventoryView->value,
                Permission::InventoryMove->value,
                Permission::InventoryWaste->value,
                Permission::PharmaciesManage->value,
                Permission::ResidentsView->value,
                Permission::ResidentsManage->value,
                Permission::ReportsInternal->value,
                Permission::ReportsExecutive->value,
                Permission::ControlledDrugAuthorize->value,
                Permission::SupportAccess->value,
            ],
            UserRole::HeadNurse->value => [
                Permission::DashboardView->value,
                Permission::InventoryView->value,
                Permission::InventoryMove->value,
                Permission::InventoryWaste->value,
                Permission::ResidentsView->value,
                Permission::ResidentsManage->value,
                Permission::ReportsInternal->value,
                Permission::SupportAccess->value,
            ],
            UserRole::NursingTechnician->value => [
                Permission::DashboardView->value,
                Permission::InventoryView->value,
                Permission::InventoryMove->value,
                Permission::ResidentsView->value,
                Permission::SupportAccess->value,
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName);
            $role->syncPermissions($permissions);
        }
    }
}
