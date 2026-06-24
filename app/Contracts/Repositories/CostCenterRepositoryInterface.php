<?php

namespace App\Contracts\Repositories;

use App\Models\CostCenter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CostCenterRepositoryInterface
{
    public function findOrFail(int $id): CostCenter;

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /** @param array<string, mixed> $data */
    public function create(array $data): CostCenter;

    /** @param array<string, mixed> $data */
    public function update(CostCenter $costCenter, array $data): CostCenter;

    /** @return \Illuminate\Support\Collection<int, CostCenter> */
    public function activeOptions(): \Illuminate\Support\Collection;
}
