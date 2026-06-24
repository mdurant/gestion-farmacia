<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_list_users(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_tens_cannot_access_user_management(): void
    {
        $tens = User::factory()->create(['is_active' => true]);
        $tens->assignRole(UserRole::NursingTechnician->value);

        $this->actingAs($tens)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'first_name' => 'Pedro',
                'last_name' => 'Soto',
                'rut' => '12.345.678-5',
                'email' => 'pedro.soto@acalis-pharma.cl',
                'role' => UserRole::HeadNurse->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'pedro.soto@acalis-pharma.cl',
            'role' => UserRole::HeadNurse->value,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_view_user_detail_and_audit(): void
    {
        $admin = $this->adminUser();
        $target = User::factory()->create(['is_active' => true]);
        $target->assignRole(UserRole::NursingTechnician->value);

        $this->actingAs($admin)
            ->get(route('users.show', $target))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('audit.index'))
            ->assertOk();
    }

    public function test_user_creation_is_audited(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'first_name' => 'Ana',
                'last_name' => 'Díaz',
                'rut' => '16.234.567-8',
                'email' => 'ana.diaz@acalis-pharma.cl',
                'role' => UserRole::NursingTechnician->value,
            ]);

        $this->assertTrue(
            AuditLog::query()->where('table_name', 'users')->where('action', 'creacion')->exists()
        );
    }

    public function test_admin_can_deactivate_user(): void
    {
        $admin = $this->adminUser();
        $target = User::factory()->create(['is_active' => true]);
        $target->assignRole(UserRole::NursingTechnician->value);

        $this->actingAs($admin)
            ->patch(route('users.toggle-active', $target))
            ->assertRedirect();

        $this->assertFalse($target->fresh()->is_active);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(UserRole::Admin->value);

        return $admin;
    }
}
