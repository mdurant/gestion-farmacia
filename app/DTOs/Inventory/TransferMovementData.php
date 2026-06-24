<?php

namespace App\DTOs\Inventory;

use App\Enums\MovementType;

final readonly class TransferMovementData
{
    public function __construct(
        public int $batchId,
        public int $sourcePharmacyId,
        public int $destinationPharmacyId,
        public int $costCenterId,
        public int $userId,
        public int $quantity,
        public ?string $notes = null,
        public ?string $authorizationCode = null,
    ) {}

    public function movementType(): MovementType
    {
        return MovementType::Transfer;
    }
}
