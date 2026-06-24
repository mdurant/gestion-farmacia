<?php

namespace App\Contracts\Repositories;

use App\Models\Resident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ResidentRepositoryInterface
{
    public function findOrFail(int $id): Resident;

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /** @param array<string, mixed> $filters
     * @return Collection<int, Resident>
     */
    public function listForExport(array $filters = []): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): Resident;

    /** @param array<string, mixed> $data */
    public function update(Resident $resident, array $data): Resident;

    public function delete(Resident $resident): void;
}
