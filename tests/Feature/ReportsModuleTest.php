<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Resident;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_head_nurse_can_view_reports_hub_and_kardex(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Reportes internos');

        $this->actingAs($user)
            ->get(route('reports.kardex'))
            ->assertOk()
            ->assertSee('Kardex de movimientos');
    }

    public function test_head_nurse_cannot_view_executive_valuation(): void
    {
        $this->actingAs($this->userWithRole(UserRole::HeadNurse))
            ->get(route('reports.valuation'))
            ->assertForbidden();
    }

    public function test_medical_director_can_view_executive_reports(): void
    {
        $this->actingAs($this->userWithRole(UserRole::MedicalDirector))
            ->get(route('reports.valuation'))
            ->assertOk()
            ->assertSee('Valorización de inventario');
    }

    public function test_kardex_csv_export_returns_csv(): void
    {
        $this->seedMovementData();

        $this->actingAs($this->userWithRole(UserRole::HeadNurse))
            ->get(route('reports.export', ['report' => 'kardex', 'format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_resident_consumption_report_groups_administrations(): void
    {
        [$user, $resident, $drug, $pharmacy, $costCenter, $batch] = $this->seedMovementData();

        InventoryMovement::query()->create([
            'movement_type' => MovementType::ExitAdministration,
            'drug_id' => $drug->id,
            'batch_id' => $batch->id,
            'pharmacy_id' => $pharmacy->id,
            'cost_center_id' => $costCenter->id,
            'resident_id' => $resident->id,
            'user_id' => $user->id,
            'prescription_id' => 'RX-REP-001',
            'quantity' => 2,
            'unit_cost' => 100,
            'total_value' => 200,
            'reason' => 'Administración a residente',
            'movement_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('reports.resident-consumption'))
            ->assertOk()
            ->assertSee($resident->full_name);
    }

    public function test_purchase_projection_shows_critical_drug(): void
    {
        $director = $this->userWithRole(UserRole::MedicalDirector);
        $drug = Drug::query()->create([
            'code' => 'DRG-CRIT',
            'name' => 'Fármaco Crítico',
            'min_stock' => 20,
            'max_stock' => 50,
            'unit_cost' => 500,
        ]);

        $pharmacy = Pharmacy::query()->create(['code' => 'PH-R', 'name' => 'Central', 'type' => 'bodega_central']);
        Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $pharmacy->id,
            'batch_number' => 'L-LOW',
            'expiration_date' => now()->addMonths(4),
            'quantity' => 5,
            'unit_cost' => 500,
        ]);

        $this->actingAs($director)
            ->get(route('reports.purchase-projection'))
            ->assertOk()
            ->assertSee('Fármaco Crítico')
            ->assertSee('Crítico');
    }

    /** @return array{0: User, 1: Resident, 2: Drug, 3: Pharmacy, 4: CostCenter, 5: Batch} */
    private function seedMovementData(): array
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $costCenter = CostCenter::query()->create(['code' => 'CC-R', 'name' => 'Piso 1']);
        $pharmacy = Pharmacy::query()->create(['code' => 'PH-R', 'name' => 'Central', 'type' => 'bodega_central']);
        $drug = Drug::query()->create(['code' => 'DRG-R', 'name' => 'Paracetamol', 'min_stock' => 10, 'unit_cost' => 100]);
        $batch = Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $pharmacy->id,
            'batch_number' => 'L-R',
            'expiration_date' => now()->addMonths(6),
            'quantity' => 50,
            'unit_cost' => 100,
        ]);
        $resident = Resident::query()->create([
            'rut' => '11.111.111-1',
            'first_name' => 'Ana',
            'last_name' => 'Test',
        ]);

        InventoryMovement::query()->create([
            'movement_type' => MovementType::Entry,
            'drug_id' => $drug->id,
            'batch_id' => $batch->id,
            'pharmacy_id' => $pharmacy->id,
            'cost_center_id' => $costCenter->id,
            'user_id' => $user->id,
            'quantity' => 50,
            'unit_cost' => 100,
            'total_value' => 5000,
            'reason' => 'Entrada',
            'movement_at' => now(),
        ]);

        return [$user, $resident, $drug, $pharmacy, $costCenter, $batch];
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role->value);

        return $user;
    }
}
