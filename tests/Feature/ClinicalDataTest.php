<?php

namespace Tests\Feature;

use App\Enums\ResidentAccessAction;
use App\Enums\UserRole;
use App\Models\Drug;
use App\Models\HealthInsurance;
use App\Models\Resident;
use App\Models\ResidentAccessLog;
use App\Models\ResidentTreatment;
use App\Models\User;
use Database\Seeders\ClinicalCatalogSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TreatmentDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_clinical_catalog_seeder_creates_presentations_and_insurances(): void
    {
        $this->seed(ClinicalCatalogSeeder::class);

        $this->assertDatabaseHas('drug_presentations', ['name' => 'Comprimido']);
        $this->assertDatabaseHas('drug_presentations', ['name' => 'Puff']);
        $this->assertDatabaseHas('drug_presentations', ['name' => 'Aplicación']);
        $this->assertDatabaseHas('health_insurances', ['name' => 'Fonasa']);
        $this->assertDatabaseHas('health_insurances', ['name' => 'Isapre']);
        $this->assertDatabaseHas('health_insurances', ['name' => 'Otro']);
    }

    public function test_treatment_database_seeder_loads_excel_json_with_encryption(): void
    {
        $this->seed(ClinicalCatalogSeeder::class);
        $this->seed(TreatmentDatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(20, Resident::query()->count());
        $this->assertGreaterThanOrEqual(100, Drug::query()->count());
        $this->assertGreaterThanOrEqual(300, ResidentTreatment::query()->count());

        $resident = Resident::query()->whereNotNull('rut')->first();
        $this->assertNotNull($resident);
        $this->assertNotSame($resident->first_name, $resident->getRawOriginal('first_name'));
    }

    public function test_viewing_resident_creates_access_log_with_browser(): void
    {
        $user = $this->headNurse();
        $resident = $this->createResident();
        $this->confirmGate($user);

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Mozilla/5.0 Chrome/120.0.0.0')
            ->get(route('residents.show', $resident))
            ->assertOk();

        $log = ResidentAccessLog::query()->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame(ResidentAccessAction::View, $log->action);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('Google Chrome', $log->browser);
        $this->assertNotNull($log->accessed_at);
    }

    public function test_update_resident_logs_old_and_new_values(): void
    {
        $user = $this->headNurse();
        $resident = $this->createResident();
        $this->confirmGate($user);

        $this->actingAs($user)
            ->put(route('residents.update', $resident), [
                'rut' => $resident->rut,
                'first_name' => 'Actualizado',
                'last_name' => 'Residente',
                'is_active' => 1,
            ]);

        $log = ResidentAccessLog::query()
            ->where('action', ResidentAccessAction::Update)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertIsArray($log->old_values);
        $this->assertIsArray($log->new_values);
    }

    public function test_soft_delete_logs_baja_action(): void
    {
        $user = $this->headNurse();
        $resident = $this->createResident();
        $this->confirmGate($user);

        $this->actingAs($user)
            ->delete(route('residents.destroy', $resident));

        $this->assertSoftDeleted($resident);
        $this->assertTrue(
            ResidentAccessLog::query()
                ->where('action', ResidentAccessAction::Delete)
                ->where('resident_id', $resident->id)
                ->exists()
        );
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

    private function createResident(): Resident
    {
        $insurance = HealthInsurance::query()->firstOrCreate(
            ['code' => 'FONASA'],
            ['name' => 'Fonasa', 'is_active' => true],
        );

        return Resident::query()->create([
            'rut' => '12.345.678-9',
            'first_name' => 'María',
            'last_name' => 'González',
            'health_insurance_id' => $insurance->id,
            'is_active' => true,
        ]);
    }
}
