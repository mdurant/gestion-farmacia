<?php

namespace App\Contracts\Repositories;

use App\Models\Drug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DrugRepositoryInterface
{
    public function findOrFail(int $id): Drug;

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /** @param array<string, mixed> $filters
     * @return Collection<int, Drug>
     */
    public function listForExport(array $filters = []): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): Drug;

    /** @param array<string, mixed> $data */
    public function update(Drug $drug, array $data): Drug;
}
