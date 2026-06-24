<?php

namespace App\Contracts\Repositories;

use App\Models\Pharmacy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PharmacyRepositoryInterface
{
    public function findOrFail(int $id): Pharmacy;

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /** @param array<string, mixed> $filters
     * @return Collection<int, Pharmacy>
     */
    public function listForExport(array $filters = []): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): Pharmacy;

    /** @param array<string, mixed> $data */
    public function update(Pharmacy $pharmacy, array $data): Pharmacy;

    public function delete(Pharmacy $pharmacy): void;
}
