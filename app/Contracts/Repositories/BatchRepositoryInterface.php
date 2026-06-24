<?php

namespace App\Contracts\Repositories;

use App\Models\Batch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BatchRepositoryInterface
{
    public function findOrFail(int $id): Batch;

    public function decrementQuantity(Batch $batch, int $quantity): Batch;

    public function incrementQuantity(Batch $batch, int $quantity): Batch;

    /** @param array<string, mixed> $filters */
    public function paginateForInventory(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /** @param array<string, mixed> $attributes */
    public function firstOrCreate(array $attributes, array $values = []): Batch;

    /** @return Collection<int, Batch> */
    public function availableForPharmacy(int $pharmacyId, ?int $drugId = null): Collection;

    /** @param array<string, mixed> $data */
    public function update(Batch $batch, array $data): Batch;

    public function delete(Batch $batch): void;
}
