<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_tens_can_view_pharmacies_but_not_manage(): void
    {
        $user = $this->userWithRole(UserRole::NursingTechnician);

        $this->actingAs($user)
            ->get(route('pharmacies.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('pharmacies.create'))
            ->assertForbidden();
    }

    public function test_admin_can_access_pharmacies_module(): void
    {
        $user = $this->userWithRole(UserRole::Admin);

        $this->actingAs($user)
            ->get(route('pharmacies.index'))
            ->assertOk();
    }

    public function test_tens_cannot_register_waste_via_api(): void
    {
        $user = $this->userWithRole(UserRole::NursingTechnician);
        [$batch, $pharmacy, $costCenter] = $this->createInventoryFixtures();

        $this->actingAs($user)
            ->postJson(route('inventory.waste.store'), [
                'batch_id' => $batch->id,
                'pharmacy_id' => $pharmacy->id,
                'cost_center_id' => $costCenter->id,
                'quantity' => 1,
                'reason' => 'Prueba',
            ])
            ->assertForbidden();
    }

    public function test_head_nurse_can_register_waste_via_api(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        [$batch, $pharmacy, $costCenter] = $this->createInventoryFixtures();

        $this->actingAs($user)
            ->postJson(route('inventory.waste.store'), [
                'batch_id' => $batch->id,
                'pharmacy_id' => $pharmacy->id,
                'cost_center_id' => $costCenter->id,
                'quantity' => 1,
                'reason' => 'Frasco roto',
            ])
            ->assertCreated();
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role->value);

        return $user;
    }

    /** @return array{0: Batch, 1: Pharmacy, 2: CostCenter} */
    private function createInventoryFixtures(): array
    {
        $costCenter = CostCenter::query()->create(['code' => 'CC-1', 'name' => 'Piso 1']);
        $pharmacy = Pharmacy::query()->create(['code' => 'PH-1', 'name' => 'Central', 'type' => 'bodega_central']);
        $drug = Drug::query()->create(['code' => 'DRG-1', 'name' => 'Test', 'min_stock' => 1]);
        $batch = Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $pharmacy->id,
            'batch_number' => 'LOT-1',
            'expiration_date' => now()->addMonths(6),
            'quantity' => 50,
            'unit_cost' => 100,
        ]);

        return [$batch, $pharmacy, $costCenter];
    }
}
