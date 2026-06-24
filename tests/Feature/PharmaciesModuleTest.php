<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Enums\PharmacyType;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmaciesModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_view_pharmacies_index(): void
    {
        [$costCenter, $pharmacy] = $this->fixtures();

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get(route('pharmacies.index'))
            ->assertOk()
            ->assertSee('Bodegas y ubicaciones')
            ->assertSee($pharmacy->name);
    }

    public function test_head_nurse_can_view_but_not_create_pharmacy(): void
    {
        $this->fixtures();

        $user = $this->userWithRole(UserRole::HeadNurse);

        $this->actingAs($user)
            ->get(route('pharmacies.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('pharmacies.create'))
            ->assertForbidden();
    }

    public function test_admin_can_create_pharmacy_linked_to_cost_center(): void
    {
        [$costCenter] = $this->fixtures();

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->post(route('pharmacies.store'), [
                'code' => 'BP-999',
                'name' => 'Botiquín Prueba',
                'type' => PharmacyType::FloorKit->value,
                'cost_center_id' => $costCenter->id,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pharmacies', [
            'code' => 'BP-999',
            'cost_center_id' => $costCenter->id,
        ]);
    }

    public function test_admin_can_create_cost_center(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->post(route('pharmacies.cost-centers.store'), [
                'code' => 'PISO-9',
                'name' => 'Piso 9',
                'floor' => '9',
                'pavilion' => 'Z',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cost_centers', [
            'code' => 'PISO-9',
            'name' => 'Piso 9',
        ]);
    }

    public function test_cannot_delete_cost_center_with_pharmacies(): void
    {
        [$costCenter] = $this->fixtures();

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->delete(route('pharmacies.cost-centers.destroy', $costCenter))
            ->assertRedirect()
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('cost_centers', ['id' => $costCenter->id]);
    }

    public function test_transfers_history_lists_movements(): void
    {
        [$costCenter, $origin, $destination, $drug, $batch] = $this->transferFixtures();

        InventoryMovement::query()->create([
            'drug_id' => $drug->id,
            'batch_id' => $batch->id,
            'pharmacy_id' => $origin->id,
            'destination_pharmacy_id' => $destination->id,
            'cost_center_id' => $costCenter->id,
            'user_id' => User::factory()->create()->id,
            'movement_type' => MovementType::Transfer,
            'quantity' => 5,
            'unit_cost' => 100,
            'total_value' => 500,
            'movement_at' => now(),
        ]);

        $this->actingAs($this->userWithRole(UserRole::Admin))
            ->get(route('pharmacies.transfers'))
            ->assertOk()
            ->assertSee('Historial de traslados')
            ->assertSee($origin->name)
            ->assertSee($destination->name);
    }

    public function test_tens_can_view_cost_center_detail(): void
    {
        [$costCenter] = $this->fixtures();

        $this->actingAs($this->userWithRole(UserRole::NursingTechnician))
            ->get(route('pharmacies.cost-centers.show', $costCenter))
            ->assertOk()
            ->assertSee($costCenter->name);
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role->value);

        return $user;
    }

    /** @return array{0: CostCenter, 1: Pharmacy} */
    private function fixtures(): array
    {
        $costCenter = CostCenter::query()->create([
            'code' => 'CC-PH',
            'name' => 'Piso Test',
            'floor' => '1',
        ]);

        $pharmacy = Pharmacy::query()->create([
            'code' => 'PH-TEST',
            'name' => 'Bodega Test',
            'type' => PharmacyType::Central,
            'cost_center_id' => $costCenter->id,
        ]);

        return [$costCenter, $pharmacy];
    }

    /** @return array{0: CostCenter, 1: Pharmacy, 2: Pharmacy, 3: Drug, 4: Batch} */
    private function transferFixtures(): array
    {
        [$costCenter, $origin] = $this->fixtures();

        $destination = Pharmacy::query()->create([
            'code' => 'PH-DST',
            'name' => 'Botiquín Destino',
            'type' => PharmacyType::FloorKit,
            'cost_center_id' => $costCenter->id,
        ]);

        $drug = Drug::query()->create([
            'code' => 'DRG-TR',
            'name' => 'Fármaco Traslado',
            'min_stock' => 1,
            'unit_cost' => 100,
        ]);

        $batch = Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $origin->id,
            'batch_number' => 'LOT-TR',
            'expiration_date' => now()->addMonths(6),
            'quantity' => 20,
            'unit_cost' => 100,
        ]);

        return [$costCenter, $origin, $destination, $drug, $batch];
    }
}
