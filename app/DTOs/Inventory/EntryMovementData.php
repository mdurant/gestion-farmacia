<?php

namespace App\DTOs\Inventory;

use App\Enums\MovementType;

final readonly class EntryMovementData
{
    public function __construct(
        public int $drugId,
        public int $pharmacyId,
        public int $costCenterId,
        public int $userId,
        public string $batchNumber,
        public string $expirationDate,
        public int $quantity,
        public float $unitCost,
        public ?string $supplierName = null,
        public ?string $supplierDocument = null,
        public ?string $notes = null,
    ) {}

    public function movementType(): MovementType
    {
        return MovementType::Entry;
    }
}
