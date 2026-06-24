<?php

namespace App\Contracts\Repositories;

use App\Models\InventoryMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryMovementRepositoryInterface
{
    /** @param array<string, mixed> $data */
    public function create(array $data): InventoryMovement;

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
}
