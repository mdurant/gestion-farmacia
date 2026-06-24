<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\Resident;
use App\Models\SystemAlert;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_without_inventory_permission_cannot_view_index(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('dashboard.view');

        $this->actingAs($user)
            ->get(route('inventory.index'))
            ->assertForbidden();
    }

    public function test_head_nurse_can_view_inventory_index(): void
    {
        $this->actingAs($this->userWithRole(UserRole::HeadNurse))
            ->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Inventario de fármacos');
    }

    public function test_admin_can_register_entry_movement(): void
    {
        $user = $this->userWithRole(UserRole::Admin);
        [$drug, $pharmacy, $costCenter] = $this->fixtures();

        $this->actingAs($user)
            ->post(route('inventory.movements.entry.store'), [
                'drug_id' => $drug->id,
                'pharmacy_id' => $pharmacy->id,
                'cost_center_id' => $costCenter->id,
                'batch_number' => 'L-TEST-002',
                'expiration_date' => now()->addMonths(8)->toDateString(),
                'quantity' => 25,
                'unit_cost' => 150,
            ])
            ->assertRedirect(route('inventory.index'));

        $this->assertDatabaseHas('batches', [
            'drug_id' => $drug->id,
            'batch_number' => 'L-TEST-002',
            'quantity' => 25,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'drug_id' => $drug->id,
            'movement_type' => MovementType::Entry->value,
            'quantity' => 25,
        ]);
    }

    public function test_admin_can_register_transfer_between_pharmacies(): void
    {
        $user = $this->userWithRole(UserRole::Admin);
        [$drug, $origin, $destination, $costCenter, $batch] = $this->transferFixtures();

        $this->actingAs($user)
            ->post(route('inventory.movements.transfer.store'), [
                'batch_id' => $batch->id,
                'source_pharmacy_id' => $origin->id,
                'destination_pharmacy_id' => $destination->id,
                'cost_center_id' => $costCenter->id,
                'quantity' => 5,
            ])
            ->assertRedirect(route('inventory.movements.index'));

        $this->assertSame(15, $batch->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'movement_type' => MovementType::Transfer->value,
            'pharmacy_id' => $origin->id,
            'destination_pharmacy_id' => $destination->id,
            'quantity' => 5,
        ]);
    }

    public function test_head_nurse_can_register_administration_to_resident(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        [$drug, $pharmacy, $costCenter] = $this->fixtures();
        $batch = $this->createBatch($drug, $pharmacy, 10);
        $resident = Resident::query()->create([
            'first_name' => 'Ana',
            'last_name' => 'Pérez',
            'rut' => '11.222.333-4',
            'birth_date' => '1940-01-01',
            'cost_center_id' => $costCenter->id,
            'room_number' => '201',
        ]);

        $this->actingAs($user)
            ->post(route('inventory.movements.administration.store'), [
                'batch_id' => $batch->id,
                'pharmacy_id' => $pharmacy->id,
                'cost_center_id' => $costCenter->id,
                'resident_id' => $resident->id,
                'quantity' => 2,
                'prescription_id' => 'RX-001',
            ])
            ->assertRedirect(route('inventory.movements.index'));

        $this->assertSame(8, $batch->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'movement_type' => MovementType::ExitAdministration->value,
            'resident_id' => $resident->id,
            'prescription_id' => 'RX-001',
        ]);
    }

    public function test_admin_can_register_expiration_exit(): void
    {
        $user = $this->userWithRole(UserRole::Admin);
        [$drug, $pharmacy, $costCenter] = $this->fixtures();
        $batch = $this->createBatch($drug, $pharmacy, 12, now()->subDay());

        $this->actingAs($user)
            ->post(route('inventory.movements.expiration.store'), [
                'batch_id' => $batch->id,
                'pharmacy_id' => $pharmacy->id,
                'cost_center_id' => $costCenter->id,
                'quantity' => 12,
            ])
            ->assertRedirect(route('inventory.movements.index'));

        $this->assertSame(0, $batch->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'movement_type' => MovementType::ExitExpiration->value,
            'quantity' => 12,
        ]);
    }

    public function test_drug_kardex_page_lists_movements(): void
    {
        $user = $this->userWithRole(UserRole::Admin);
        [$drug, $pharmacy, $costCenter] = $this->fixtures();
        $this->createBatch($drug, $pharmacy, 5);

        $this->actingAs($user)
            ->get(route('inventory.drugs.show', $drug))
            ->assertOk()
            ->assertSee('Kardex')
            ->assertSee($drug->name);
    }

    public function test_admin_can_update_batch_metadata(): void
    {
        $user = $this->userWithRole(UserRole::Admin);
        [$drug, $pharmacy] = $this->fixtures();
        $batch = $this->createBatch($drug, $pharmacy, 8);

        $this->actingAs($user)
            ->put(route('inventory.batches.update', $batch), [
                'expiration_date' => now()->addMonths(10)->toDateString(),
                'unit_cost' => 250,
                'supplier_name' => 'Cenabast',
            ])
            ->assertRedirect(route('inventory.batches.show', $batch));

        $batch->refresh();
        $this->assertSame('Cenabast', $batch->supplier_name);
        $this->assertEquals(250, (float) $batch->unit_cost);
    }

    public function test_inventory_index_shows_active_alerts(): void
    {
        $user = $this->userWithRole(UserRole::Admin);
        [$drug, $pharmacy] = $this->fixtures();
        $batch = $this->createBatch($drug, $pharmacy, 1, now()->addDays(10));

        SystemAlert::query()->create([
            'type' => 'expiring_soon',
            'severity' => 'warning',
            'drug_id' => $drug->id,
            'batch_id' => $batch->id,
            'pharmacy_id' => $pharmacy->id,
            'title' => 'Vencimiento próximo',
            'message' => 'Lote por vencer en 10 días',
        ]);

        $this->actingAs($user)
            ->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Alertas activas')
            ->assertSee('Vencimiento próximo');
    }

    public function test_head_nurse_can_register_waste_from_web(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        [$drug, $pharmacy, $costCenter] = $this->fixtures();
        $batch = $this->createBatch($drug, $pharmacy, 10);

        $this->actingAs($user)
            ->post(route('inventory.movements.waste.store'), [
                'batch_id' => $batch->id,
                'pharmacy_id' => $pharmacy->id,
                'cost_center_id' => $costCenter->id,
                'quantity' => 2,
                'reason' => 'Ampolla rota',
            ])
            ->assertRedirect(route('inventory.movements.index'));

        $this->assertSame(8, $batch->fresh()->quantity);
    }

    public function test_tens_cannot_register_waste(): void
    {
        $user = $this->userWithRole(UserRole::NursingTechnician);
        [$drug, $pharmacy, $costCenter] = $this->fixtures();
        $batch = $this->createBatch($drug, $pharmacy, 10);

        $this->actingAs($user)
            ->get(route('inventory.movements.waste.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('inventory.movements.waste.store'), [
                'batch_id' => $batch->id,
                'pharmacy_id' => $pharmacy->id,
                'cost_center_id' => $costCenter->id,
                'quantity' => 1,
                'reason' => 'Test',
            ])
            ->assertForbidden();
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role->value);

        return $user;
    }

    /** @return array{0: Drug, 1: Pharmacy, 2: CostCenter} */
    private function fixtures(): array
    {
        $costCenter = CostCenter::query()->create(['code' => 'CC-T', 'name' => 'Test']);
        $pharmacy = Pharmacy::query()->create(['code' => 'PH-T', 'name' => 'Central', 'type' => 'bodega_central']);
        $drug = Drug::query()->create(['code' => 'DRG-T', 'name' => 'Test Drug', 'min_stock' => 5, 'unit_cost' => 100]);

        return [$drug, $pharmacy, $costCenter];
    }

    /** @return array{0: Drug, 1: Pharmacy, 2: Pharmacy, 3: CostCenter, 4: Batch} */
    private function transferFixtures(): array
    {
        [$drug, $origin, $costCenter] = $this->fixtures();
        $destination = Pharmacy::query()->create([
            'code' => 'PH-DST',
            'name' => 'Botiquín Destino',
            'type' => 'botiquin_piso',
        ]);
        $batch = $this->createBatch($drug, $origin, 20);

        return [$drug, $origin, $destination, $costCenter, $batch];
    }

    private function createBatch(Drug $drug, Pharmacy $pharmacy, int $quantity, ?\Illuminate\Support\Carbon $expiration = null): Batch
    {
        return Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $pharmacy->id,
            'batch_number' => 'L-'.fake()->unique()->numerify('####'),
            'expiration_date' => $expiration ?? now()->addMonths(4),
            'quantity' => $quantity,
            'unit_cost' => 100,
        ]);
    }
}
