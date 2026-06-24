<?php

namespace App\DTOs\Inventory;

use App\Enums\MovementType;

final readonly class AdministrationMovementData
{
    public function __construct(
        public int $batchId,
        public int $pharmacyId,
        public int $costCenterId,
        public int $residentId,
        public int $userId,
        public int $quantity,
        public ?string $prescriptionId = null,
        public ?string $notes = null,
        public ?string $authorizationCode = null,
    ) {}

    public function movementType(): MovementType
    {
        return MovementType::ExitAdministration;
    }
}
