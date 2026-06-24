<?php

namespace App\Repositories;

use App\Contracts\Repositories\DrugRepositoryInterface;
use App\Models\Drug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DrugRepository implements DrugRepositoryInterface
{
    public function findOrFail(int $id): Drug
    {
        return Drug::query()->findOrFail($id);
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
        return Drug::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null, fn ($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->when($filters['controlled'] ?? false, fn ($q) => $q->where(function ($q): void {
                $q->where('is_controlled', true)->orWhere('is_narcotic', true);
            }))
            ->orderBy('name');
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Drug
    {
        return Drug::query()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Drug $drug, array $data): Drug
    {
        $drug->update($data);

        return $drug->fresh();
    }
}
