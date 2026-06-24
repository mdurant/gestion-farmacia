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

class ResidentsModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_tens_can_view_residents_but_not_create(): void
    {
        $resident = $this->createResident();

        $user = $this->userWithRole(UserRole::NursingTechnician);
        $this->confirmResidentGate($user);

        $this->actingAs($user)
            ->get(route('residents.index'))
            ->assertOk()
            ->assertSee($resident->full_name);

        $this->actingAs($user)
            ->get(route('residents.create'))
            ->assertForbidden();
    }

    public function test_head_nurse_can_create_resident_with_encrypted_data(): void
    {
        $costCenter = CostCenter::query()->create(['code' => 'CC-R', 'name' => 'Piso 3']);
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);

        $this->actingAs($user)
            ->post(route('residents.store'), [
                'rut' => '10.111.222-3',
                'first_name' => 'Rosa',
                'last_name' => 'Vega',
                'birth_date' => '1942-05-10',
                'cost_center_id' => $costCenter->id,
                'room_number' => '305B',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $resident = Resident::query()->first();
        $this->assertNotNull($resident);
        $this->assertSame('Rosa Vega', $resident->full_name);
        $this->assertNotSame('Rosa', $resident->getRawOriginal('first_name'));
    }

    public function test_resident_show_displays_administration_history_with_traceability(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);
        $resident = $this->createResident();
        $pharmacy = Pharmacy::query()->create(['code' => 'PH-R', 'name' => 'Central', 'type' => 'bodega_central']);
        $costCenter = CostCenter::query()->create(['code' => 'CC-R', 'name' => 'Piso 1']);
        $drug = Drug::query()->create(['code' => 'DRG-R', 'name' => 'Test Drug', 'min_stock' => 1]);
        $batch = Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $pharmacy->id,
            'batch_number' => 'L-RX',
            'expiration_date' => now()->addMonths(3),
            'quantity' => 5,
            'unit_cost' => 100,
        ]);

        InventoryMovement::query()->create([
            'movement_type' => MovementType::ExitAdministration,
            'drug_id' => $drug->id,
            'batch_id' => $batch->id,
            'pharmacy_id' => $pharmacy->id,
            'cost_center_id' => $costCenter->id,
            'resident_id' => $resident->id,
            'user_id' => $user->id,
            'prescription_id' => 'RX-TRACE-001',
            'quantity' => 1,
            'unit_cost' => 100,
            'total_value' => 100,
            'reason' => 'Administración a residente',
            'movement_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('residents.show', $resident))
            ->assertOk()
            ->assertSee('Historial de administraciones')
            ->assertSee('RX-TRACE-001')
            ->assertSee('Trazabilidad');
    }

    public function test_head_nurse_can_update_resident(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);
        $resident = $this->createResident();

        $this->actingAs($user)
            ->put(route('residents.update', $resident), [
                'rut' => $resident->rut,
                'first_name' => 'María',
                'last_name' => 'Actualizada',
                'room_number' => '102C',
                'is_active' => 1,
            ])
            ->assertRedirect(route('residents.show', $resident));

        $this->assertSame('María Actualizada', $resident->fresh()->full_name);
    }

    public function test_administration_form_preselects_resident_from_query(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);
        $resident = $this->createResident();

        $this->actingAs($user)
            ->get(route('inventory.movements.administration.create', ['resident_id' => $resident->id]))
            ->assertOk()
            ->assertSee($resident->full_name);
    }

    public function test_administration_requires_prescription_id(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);
        $resident = $this->createResident();

        $this->actingAs($user)
            ->post(route('inventory.movements.administration.store'), [
                'batch_id' => 1,
                'pharmacy_id' => 1,
                'cost_center_id' => 1,
                'resident_id' => $resident->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('prescription_id');
    }

    public function test_residents_search_finds_decrypted_name_when_status_filter_is_all(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);
        $resident = $this->createResident();

        $this->actingAs($user)
            ->get(route('residents.index', [
                'search' => 'González',
                'cost_center_id' => '',
                'is_active' => '',
            ]))
            ->assertOk()
            ->assertSee($resident->full_name)
            ->assertDontSee('No hay residentes registrados');
    }

    public function test_residents_search_finds_by_rut_without_formatting(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);
        $resident = $this->createResident();

        $this->actingAs($user)
            ->get(route('residents.index', ['search' => '123456789']))
            ->assertOk()
            ->assertSee($resident->full_name);
    }

    public function test_residents_search_finds_by_room_number(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);
        $resident = $this->createResident();

        $this->actingAs($user)
            ->get(route('residents.index', ['search' => '101A']))
            ->assertOk()
            ->assertSee($resident->full_name);
    }

    public function test_residents_search_with_active_status_filter(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);
        $active = $this->createResident();
        Resident::query()->create([
            'rut' => '9.876.543-2',
            'first_name' => 'Pedro',
            'last_name' => 'Inactivo',
            'room_number' => '202B',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('residents.index', [
                'search' => 'González',
                'is_active' => '1',
            ]))
            ->assertOk()
            ->assertSee($active->full_name)
            ->assertDontSee('Pedro Inactivo');
    }

    public function test_residents_search_with_inactive_status_filter(): void
    {
        $user = $this->userWithRole(UserRole::HeadNurse);
        $this->confirmResidentGate($user);
        $this->createResident();
        Resident::query()->create([
            'rut' => '9.876.543-2',
            'first_name' => 'Pedro',
            'last_name' => 'Inactivo',
            'room_number' => '202B',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('residents.index', [
                'search' => 'Inactivo',
                'is_active' => '0',
            ]))
            ->assertOk()
            ->assertSee('Pedro Inactivo')
            ->assertDontSee('María González');
    }

    private function userWithRole(UserRole $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role->value);

        return $user;
    }

    private function confirmResidentGate(User $user): void
    {
        $this->actingAs($user)
            ->post(route('residents.gate.confirm'), [
                'password' => 'password',
                'disclaimer_accepted' => '1',
            ]);
    }

    private function createResident(): Resident
    {
        return Resident::query()->create([
            'rut' => '12.345.678-9',
            'first_name' => 'María',
            'last_name' => 'González',
            'birth_date' => '1945-03-15',
            'room_number' => '101A',
            'is_active' => true,
        ]);
    }
}
