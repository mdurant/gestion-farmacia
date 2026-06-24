<?php

namespace Tests\Unit;

use App\Contracts\Services\MovementServiceInterface;
use App\DTOs\Inventory\WasteMovementData;
use App\Enums\MovementType;
use App\Events\HighValueWasteRecorded;
use App\Events\InventoryMovementRecorded;
use App\Models\Batch;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MovementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_waste_exit_decrements_stock_and_dispatches_events(): void
    {
        Event::fake([InventoryMovementRecorded::class, HighValueWasteRecorded::class]);

        $user = User::factory()->create();
        $costCenter = CostCenter::query()->create([
            'code' => 'CC-TEST',
            'name' => 'Test Center',
        ]);
        $pharmacy = Pharmacy::query()->create([
            'code' => 'PH-TEST',
            'name' => 'Test Pharmacy',
            'type' => 'bodega_central',
        ]);
        $drug = Drug::query()->create([
            'code' => 'DRG-TEST',
            'name' => 'Test Drug',
            'min_stock' => 10,
            'unit_cost' => 1000,
        ]);
        $batch = Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $pharmacy->id,
            'batch_number' => 'LOT-001',
            'expiration_date' => now()->addMonths(3),
            'quantity' => 100,
            'unit_cost' => 1000,
        ]);

        $service = app(MovementServiceInterface::class);

        $movement = $service->processWasteExit(new WasteMovementData(
            batchId: $batch->id,
            pharmacyId: $pharmacy->id,
            costCenterId: $costCenter->id,
            userId: $user->id,
            quantity: 5,
            reason: 'Frasco roto durante manipulación',
        ));

        $this->assertSame(MovementType::ExitWaste, $movement->movement_type);
        $this->assertSame(5, $movement->quantity);
        $this->assertSame(95, $batch->fresh()->quantity);

        Event::assertDispatched(InventoryMovementRecorded::class);
        Event::assertNotDispatched(HighValueWasteRecorded::class);
    }

    public function test_high_value_waste_triggers_management_alert_event(): void
    {
        Event::fake([InventoryMovementRecorded::class, HighValueWasteRecorded::class]);

        $user = User::factory()->create();
        $costCenter = CostCenter::query()->create(['code' => 'CC-HV', 'name' => 'High Value']);
        $pharmacy = Pharmacy::query()->create([
            'code' => 'PH-HV',
            'name' => 'HV Pharmacy',
            'type' => 'bodega_central',
        ]);
        $drug = Drug::query()->create([
            'code' => 'DRG-HV',
            'name' => 'Expensive Drug',
            'min_stock' => 1,
            'unit_cost' => 20000,
        ]);
        $batch = Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $pharmacy->id,
            'batch_number' => 'LOT-HV',
            'expiration_date' => now()->addYear(),
            'quantity' => 50,
            'unit_cost' => 20000,
        ]);

        $service = app(MovementServiceInterface::class);

        $service->processWasteExit(new WasteMovementData(
            batchId: $batch->id,
            pharmacyId: $pharmacy->id,
            costCenterId: $costCenter->id,
            userId: $user->id,
            quantity: 3,
            reason: 'Merma por cadena de frío interrumpida',
        ));

        Event::assertDispatched(HighValueWasteRecorded::class);
    }
}
