<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\Resident;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_drugs_export_csv_respects_search_filter(): void
    {
        Drug::query()->create(['code' => 'A-1', 'name' => 'Losartán', 'min_stock' => 1, 'unit_cost' => 100, 'is_active' => true]);
        Drug::query()->create(['code' => 'B-1', 'name' => 'Paracetamol', 'min_stock' => 1, 'unit_cost' => 50, 'is_active' => true]);

        $user = $this->headNurse();

        $this->actingAs($user)
            ->get(route('inventory.drugs.export', ['format' => 'csv', 'search' => 'Losartán']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertSee('Losartán')
            ->assertDontSee('Paracetamol');
    }

    public function test_pharmacies_export_pdf_returns_attachment(): void
    {
        Pharmacy::query()->create(['code' => 'PH-1', 'name' => 'Central', 'type' => 'bodega_central', 'is_active' => true]);

        $user = $this->headNurse();

        $response = $this->actingAs($user)
            ->get(route('pharmacies.export', ['format' => 'pdf']));

        $response->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
    }

    public function test_residents_export_csv_requires_gate_and_returns_decrypted_data(): void
    {
        $user = $this->headNurse();
        $this->confirmGate($user);

        Resident::query()->create([
            'rut' => '12.345.678-9',
            'first_name' => 'Ana',
            'last_name' => 'Export',
            'room_number' => '301',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('residents.export', ['format' => 'csv', 'search' => 'Export']))
            ->assertOk()
            ->assertSee('Ana Export')
            ->assertSee('301');
    }

    public function test_users_index_does_not_filter_by_empty_role(): void
    {
        $admin = User::factory()->create(['is_active' => true, 'email' => 'admin@test.cl']);
        $admin->assignRole(UserRole::Admin->value);

        $tens = User::factory()->create(['is_active' => true, 'email' => 'tens@test.cl', 'name' => 'Usuario TENS']);
        $tens->assignRole(UserRole::NursingTechnician->value);

        $this->actingAs($admin)
            ->get(route('users.index', ['role' => '']))
            ->assertOk()
            ->assertSee('Usuario TENS');
    }

    private function headNurse(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::HeadNurse->value);

        return $user;
    }

    private function confirmGate(User $user): void
    {
        $this->actingAs($user)
            ->post(route('residents.gate.confirm'), [
                'password' => 'password',
                'disclaimer_accepted' => '1',
            ]);
    }
}
