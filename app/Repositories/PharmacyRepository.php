<?php

namespace App\Repositories;

use App\Contracts\Repositories\PharmacyRepositoryInterface;
use App\Models\Pharmacy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;

class PharmacyRepository implements PharmacyRepositoryInterface
{
    public function findOrFail(int $id): Pharmacy
    {
        return Pharmacy::query()->with('costCenter')->findOrFail($id);
    }

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @param array<string, mixed> $filters */
    public function listForExport(array $filters = []): \Illuminate\Support\Collection
    {
        return $this->filteredQuery($filters)->get();
    }

    /** @param array<string, mixed> $filters */
    private function filteredQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        return Pharmacy::query()
            ->with('costCenter')
            ->withCount(['batches as batches_in_stock_count' => fn ($q) => $q->where('quantity', '>', 0)])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['cost_center_id'] ?? null, fn ($q, $id) => $q->where('cost_center_id', $id))
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null, fn ($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name');
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Pharmacy
    {
        return Pharmacy::query()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Pharmacy $pharmacy, array $data): Pharmacy
    {
        $pharmacy->update($data);

        return $pharmacy->fresh(['costCenter']);
    }

    public function delete(Pharmacy $pharmacy): void
    {
        $hasStock = $pharmacy->batches()->where('quantity', '>', 0)->exists();

        if ($hasStock) {
            throw new RuntimeException('No se puede dar de baja una bodega con stock activo.');
        }

        $pharmacy->delete();
    }
}
