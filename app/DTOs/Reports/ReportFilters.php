<?php

namespace App\DTOs\Reports;

readonly class ReportFilters
{
    public function __construct(
        public ?string $from = null,
        public ?string $to = null,
        public ?int $pharmacyId = null,
        public ?int $costCenterId = null,
        public ?int $drugId = null,
        public ?int $residentId = null,
        public ?string $movementType = null,
        public ?int $userId = null,
    ) {}

    /** @return array<string, mixed> */
    public function toMovementFilters(?string $movementType = null): array
    {
        return array_filter([
            'from' => $this->from,
            'to' => $this->to,
            'pharmacy_id' => $this->pharmacyId,
            'cost_center_id' => $this->costCenterId,
            'drug_id' => $this->drugId,
            'resident_id' => $this->residentId,
            'movement_type' => $movementType ?? $this->movementType,
            'user_id' => $this->userId,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
