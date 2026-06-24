<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Exceptions\ControlledDrugAuthorizationRequiredException;
use App\Models\Batch;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\ControlledDrugAuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlledDrugAuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_controlled_drug_requires_authorization_for_tens(): void
    {
        $service = app(ControlledDrugAuthorizationService::class);
        $batch = $this->controlledBatch();
        $tens = User::factory()->create(['is_active' => true]);

        $this->expectException(ControlledDrugAuthorizationRequiredException::class);

        $service->assertMovementAllowed($batch, $tens);
    }

    public function test_director_can_move_controlled_drug_without_code(): void
    {
        $service = app(ControlledDrugAuthorizationService::class);
        $batch = $this->controlledBatch();
        $director = User::factory()->create(['is_active' => true]);
        $director->assignRole(UserRole::MedicalDirector->value);

        $service->assertMovementAllowed($batch, $director);

        $this->assertTrue(true);
    }

    public function test_valid_authorization_code_allows_tens(): void
    {
        $service = app(ControlledDrugAuthorizationService::class);
        $batch = $this->controlledBatch();
        $tens = User::factory()->create(['is_active' => true]);

        $service->assertMovementAllowed($batch, $tens, 'FAR-12345678');

        $this->assertTrue(true);
    }

    private function controlledBatch(): Batch
    {
        $pharmacy = Pharmacy::query()->create(['code' => 'PH-C', 'name' => 'Central', 'type' => 'bodega_central']);
        $drug = Drug::query()->create([
            'code' => 'FAR-CTRL',
            'name' => 'Morfina',
            'is_controlled' => true,
            'is_narcotic' => true,
        ]);

        return Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $pharmacy->id,
            'batch_number' => 'LOT-C',
            'expiration_date' => now()->addYear(),
            'quantity' => 10,
            'unit_cost' => 5000,
        ]);
    }
}
