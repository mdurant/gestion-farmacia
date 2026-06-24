<?php

namespace App\Services;

use App\Contracts\Repositories\BatchRepositoryInterface;
use App\Contracts\Repositories\InventoryMovementRepositoryInterface;
use App\Contracts\Services\MovementServiceInterface;
use App\DTOs\Inventory\AdministrationMovementData;
use App\DTOs\Inventory\EntryMovementData;
use App\DTOs\Inventory\ExpirationMovementData;
use App\DTOs\Inventory\TransferMovementData;
use App\DTOs\Inventory\WasteMovementData;
use App\Enums\MovementType;
use App\Events\HighValueWasteRecorded;
use App\Events\InventoryMovementRecorded;
use App\Models\Batch;
use App\Models\InventoryMovement;
use App\Models\SystemAlert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MovementService implements MovementServiceInterface
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly InventoryMovementRepositoryInterface $movementRepository,
        private readonly ControlledDrugAuthorizationService $controlledDrugAuthorizationService,
    ) {}

    public function processWasteExit(WasteMovementData $data): InventoryMovement
    {
        return $this->processExit(
            batchId: $data->batchId,
            pharmacyId: $data->pharmacyId,
            costCenterId: $data->costCenterId,
            userId: $data->userId,
            quantity: $data->quantity,
            movementType: $data->movementType(),
            reason: $data->reason,
            notes: $data->notes,
            authorizationCode: $data->authorizationCode,
            triggerHighValueAlert: true,
        );
    }

    public function processEntry(EntryMovementData $data): InventoryMovement
    {
        return DB::transaction(function () use ($data): InventoryMovement {
            $batch = $this->batchRepository->firstOrCreate(
                [
                    'drug_id' => $data->drugId,
                    'pharmacy_id' => $data->pharmacyId,
                    'batch_number' => $data->batchNumber,
                ],
                [
                    'expiration_date' => $data->expirationDate,
                    'quantity' => 0,
                    'unit_cost' => $data->unitCost,
                    'supplier_name' => $data->supplierName,
                    'supplier_document' => $data->supplierDocument,
                    'received_at' => now(),
                ],
            );

            $updatedBatch = $this->batchRepository->incrementQuantity($batch, $data->quantity);
            $totalValue = round($data->unitCost * $data->quantity, 2);

            $movement = $this->recordMovement([
                'movement_type' => $data->movementType()->value,
                'pharmacy_id' => $data->pharmacyId,
                'batch_id' => $updatedBatch->id,
                'drug_id' => $data->drugId,
                'cost_center_id' => $data->costCenterId,
                'user_id' => $data->userId,
                'quantity' => $data->quantity,
                'unit_cost' => $data->unitCost,
                'total_value' => $totalValue,
                'reason' => 'Entrada de inventario',
                'notes' => $data->notes,
            ]);

            $this->evaluateStockAlerts($updatedBatch);

            return $movement;
        });
    }

    public function processTransfer(TransferMovementData $data): InventoryMovement
    {
        return DB::transaction(function () use ($data): InventoryMovement {
            $sourceBatch = $this->batchRepository->findOrFail($data->batchId);
            $this->assertBatchBelongsToPharmacy($sourceBatch, $data->sourcePharmacyId);

            $user = User::query()->findOrFail($data->userId);
            $this->controlledDrugAuthorizationService->assertMovementAllowed(
                $sourceBatch,
                $user,
                $data->authorizationCode,
            );

            $updatedSource = $this->batchRepository->decrementQuantity($sourceBatch, $data->quantity);

            $destBatch = $this->batchRepository->firstOrCreate(
                [
                    'drug_id' => $updatedSource->drug_id,
                    'pharmacy_id' => $data->destinationPharmacyId,
                    'batch_number' => $updatedSource->batch_number,
                ],
                [
                    'expiration_date' => $updatedSource->expiration_date,
                    'quantity' => 0,
                    'unit_cost' => $updatedSource->unit_cost,
                    'supplier_name' => $updatedSource->supplier_name,
                    'received_at' => now(),
                ],
            );

            $this->batchRepository->incrementQuantity($destBatch, $data->quantity);

            $unitCost = (float) $updatedSource->unit_cost;
            $movement = $this->recordMovement([
                'movement_type' => $data->movementType()->value,
                'pharmacy_id' => $data->sourcePharmacyId,
                'destination_pharmacy_id' => $data->destinationPharmacyId,
                'batch_id' => $updatedSource->id,
                'drug_id' => $updatedSource->drug_id,
                'cost_center_id' => $data->costCenterId,
                'user_id' => $data->userId,
                'quantity' => $data->quantity,
                'unit_cost' => $unitCost,
                'total_value' => round($unitCost * $data->quantity, 2),
                'reason' => 'Traslado entre bodegas',
                'notes' => $data->notes,
            ]);

            $this->evaluateStockAlerts($updatedSource);

            return $movement;
        });
    }

    public function processAdministration(AdministrationMovementData $data): InventoryMovement
    {
        return $this->processExit(
            batchId: $data->batchId,
            pharmacyId: $data->pharmacyId,
            costCenterId: $data->costCenterId,
            userId: $data->userId,
            quantity: $data->quantity,
            movementType: $data->movementType(),
            reason: 'Administración a residente',
            notes: $data->notes,
            authorizationCode: $data->authorizationCode,
            residentId: $data->residentId,
            prescriptionId: $data->prescriptionId,
        );
    }

    public function processExpirationExit(ExpirationMovementData $data): InventoryMovement
    {
        return $this->processExit(
            batchId: $data->batchId,
            pharmacyId: $data->pharmacyId,
            costCenterId: $data->costCenterId,
            userId: $data->userId,
            quantity: $data->quantity,
            movementType: $data->movementType(),
            reason: 'Salida por vencimiento',
            notes: $data->notes,
            authorizationCode: $data->authorizationCode,
        );
    }

    private function processExit(
        int $batchId,
        int $pharmacyId,
        int $costCenterId,
        int $userId,
        int $quantity,
        MovementType $movementType,
        string $reason,
        ?string $notes = null,
        ?string $authorizationCode = null,
        ?int $residentId = null,
        ?string $prescriptionId = null,
        bool $triggerHighValueAlert = false,
    ): InventoryMovement {
        return DB::transaction(function () use (
            $batchId, $pharmacyId, $costCenterId, $userId, $quantity, $movementType,
            $reason, $notes, $authorizationCode, $residentId, $prescriptionId, $triggerHighValueAlert
        ): InventoryMovement {
            $batch = $this->batchRepository->findOrFail($batchId);
            $this->assertBatchBelongsToPharmacy($batch, $pharmacyId);

            $user = User::query()->findOrFail($userId);
            $this->controlledDrugAuthorizationService->assertMovementAllowed(
                $batch,
                $user,
                $authorizationCode,
            );

            $updatedBatch = $this->batchRepository->decrementQuantity($batch, $quantity);
            $unitCost = (float) $updatedBatch->unit_cost;
            $totalValue = round($unitCost * $quantity, 2);

            $movement = $this->recordMovement([
                'movement_type' => $movementType->value,
                'pharmacy_id' => $pharmacyId,
                'batch_id' => $updatedBatch->id,
                'drug_id' => $updatedBatch->drug_id,
                'cost_center_id' => $costCenterId,
                'resident_id' => $residentId,
                'user_id' => $userId,
                'prescription_id' => $prescriptionId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_value' => $totalValue,
                'reason' => $reason,
                'notes' => $notes,
            ]);

            $this->evaluateStockAlerts($updatedBatch);

            if ($triggerHighValueAlert && $movementType === MovementType::ExitWaste && $totalValue >= self::highValueWasteThreshold()) {
                HighValueWasteRecorded::dispatch($movement);
            }

            return $movement;
        });
    }

    /** @param array<string, mixed> $data */
    private function recordMovement(array $data): InventoryMovement
    {
        $data['movement_at'] = now();

        $movement = $this->movementRepository->create($data);
        $movement->load(['drug', 'pharmacy', 'destinationPharmacy', 'batch', 'costCenter', 'user', 'resident']);

        InventoryMovementRecorded::dispatch($movement);

        return $movement;
    }

    private function assertBatchBelongsToPharmacy(Batch $batch, int $pharmacyId): void
    {
        if ($batch->pharmacy_id !== $pharmacyId) {
            throw new RuntimeException('El lote no pertenece a la bodega indicada.');
        }
    }

    private function evaluateStockAlerts(Batch $batch): void
    {
        $batch->loadMissing('drug');
        $drug = $batch->drug;

        if ($drug === null) {
            return;
        }

        $totalStock = Batch::query()
            ->where('drug_id', $drug->id)
            ->where('pharmacy_id', $batch->pharmacy_id)
            ->sum('quantity');

        if ($totalStock <= $drug->min_stock) {
            $this->createAlertIfMissing('low_stock', [
                'severity' => 'error',
                'drug_id' => $drug->id,
                'batch_id' => $batch->id,
                'pharmacy_id' => $batch->pharmacy_id,
                'title' => 'Stock crítico',
                'message' => "El fármaco {$drug->name} está bajo el mínimo ({$totalStock}/{$drug->min_stock}).",
                'metadata' => [
                    'current_stock' => $totalStock,
                    'min_stock' => $drug->min_stock,
                ],
            ]);
        }

        if ($batch->quantity > 0 && $batch->isExpiringWithinDays(30)) {
            $this->createAlertIfMissing('expiring_soon', [
                'severity' => 'warning',
                'drug_id' => $drug->id,
                'batch_id' => $batch->id,
                'pharmacy_id' => $batch->pharmacy_id,
                'title' => 'Vencimiento próximo',
                'message' => "El lote {$batch->batch_number} de {$drug->name} vence el {$batch->expiration_date->format('d/m/Y')}.",
                'metadata' => [
                    'expiration_date' => $batch->expiration_date->toDateString(),
                    'days_remaining' => now()->diffInDays($batch->expiration_date, false),
                ],
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    private function createAlertIfMissing(string $type, array $data): void
    {
        $exists = SystemAlert::query()
            ->where('type', $type)
            ->where('drug_id', $data['drug_id'])
            ->where('batch_id', $data['batch_id'] ?? null)
            ->where('pharmacy_id', $data['pharmacy_id'])
            ->whereNull('read_at')
            ->exists();

        if ($exists) {
            return;
        }

        SystemAlert::query()->create([
            'type' => $type,
            ...$data,
        ]);
    }

    public static function highValueWasteThreshold(): int
    {
        return (int) config('acalis.inventory.high_value_waste_threshold', 50000);
    }
}
