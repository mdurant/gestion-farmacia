<?php

namespace App\Repositories;

use App\Contracts\Repositories\InventoryMovementRepositoryInterface;
use App\Models\InventoryMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InventoryMovementRepository implements InventoryMovementRepositoryInterface
{
    /** @param array<string, mixed> $data */
    public function create(array $data): InventoryMovement
    {
        return InventoryMovement::query()->create($data);
    }

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return InventoryMovement::query()
            ->with(['drug', 'pharmacy', 'destinationPharmacy', 'batch', 'costCenter', 'user', 'resident'])
            ->when($filters['movement_type'] ?? null, fn ($q, $type) => $q->where('movement_type', $type))
            ->when($filters['pharmacy_id'] ?? null, function ($q, $id): void {
                $q->where(function ($inner) use ($id): void {
                    $inner->where('pharmacy_id', $id)
                        ->orWhere('destination_pharmacy_id', $id);
                });
            })
            ->when($filters['drug_id'] ?? null, fn ($q, $id) => $q->where('drug_id', $id))
            ->when($filters['batch_id'] ?? null, fn ($q, $id) => $q->where('batch_id', $id))
            ->when($filters['resident_id'] ?? null, fn ($q, $id) => $q->where('resident_id', $id))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('movement_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('movement_at', '<=', $to))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->whereHas('drug', fn ($dq) => $dq
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%"));
            })
            ->orderByDesc('movement_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
