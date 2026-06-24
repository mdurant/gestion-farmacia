<?php

namespace App\Repositories;

use App\Contracts\Repositories\CostCenterRepositoryInterface;
use App\Models\CostCenter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CostCenterRepository implements CostCenterRepositoryInterface
{
    public function findOrFail(int $id): CostCenter
    {
        return CostCenter::query()->findOrFail($id);
    }

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CostCenter::query()
            ->withCount('pharmacies')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('floor', 'like', "%{$search}%")
                        ->orWhere('pavilion', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): CostCenter
    {
        return CostCenter::query()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(CostCenter $costCenter, array $data): CostCenter
    {
        $costCenter->update($data);

        return $costCenter->fresh();
    }

    /** @return Collection<int, CostCenter> */
    public function activeOptions(): Collection
    {
        return CostCenter::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
