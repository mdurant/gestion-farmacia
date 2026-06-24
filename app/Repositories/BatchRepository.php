<?php

namespace App\Repositories;

use App\Contracts\Repositories\BatchRepositoryInterface;
use App\Models\Batch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BatchRepository implements BatchRepositoryInterface
{
    public function findOrFail(int $id): Batch
    {
        return Batch::query()->with(['drug', 'pharmacy'])->findOrFail($id);
    }

    public function decrementQuantity(Batch $batch, int $quantity): Batch
    {
        return DB::transaction(function () use ($batch, $quantity): Batch {
            $locked = Batch::query()->lockForUpdate()->findOrFail($batch->id);

            if ($locked->availableQuantity() < $quantity) {
                throw new RuntimeException('Stock insuficiente en el lote seleccionado.');
            }

            $locked->decrement('quantity', $quantity);

            return $locked->fresh(['drug', 'pharmacy']);
        });
    }

    public function incrementQuantity(Batch $batch, int $quantity): Batch
    {
        $batch->increment('quantity', $quantity);

        return $batch->fresh(['drug', 'pharmacy']);
    }

    /** @param array<string, mixed> $filters */
    public function paginateForInventory(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Batch::query()
            ->with(['drug', 'pharmacy.costCenter'])
            ->whereHas('drug')
            ->when($filters['pharmacy_id'] ?? null, fn ($q, $id) => $q->where('pharmacy_id', $id))
            ->when($filters['drug_id'] ?? null, fn ($q, $id) => $q->where('drug_id', $id))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('drug', fn ($dq) => $dq
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->when(($filters['status'] ?? null) === 'low_stock', function ($query): void {
                $query->whereHas('drug', function ($dq): void {
                    $dq->whereRaw(
                        '(select coalesce(sum(b.quantity), 0) from batches b where b.drug_id = drugs.id and b.deleted_at is null) <= drugs.min_stock'
                    );
                });
            })
            ->when(($filters['status'] ?? null) === 'expiring', fn ($q) => $q
                ->whereDate('expiration_date', '<=', now()->addDays(30))
                ->where('quantity', '>', 0))
            ->when(($filters['status'] ?? null) === 'in_stock', fn ($q) => $q->where('quantity', '>', 0))
            ->orderBy('expiration_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @param array<string, mixed> $attributes */
    public function firstOrCreate(array $attributes, array $values = []): Batch
    {
        return Batch::query()->firstOrCreate($attributes, $values);
    }

    /** @return Collection<int, Batch> */
    public function availableForPharmacy(int $pharmacyId, ?int $drugId = null): Collection
    {
        return Batch::query()
            ->with('drug')
            ->where('pharmacy_id', $pharmacyId)
            ->when($drugId, fn ($q, $id) => $q->where('drug_id', $id))
            ->where('quantity', '>', 0)
            ->orderBy('expiration_date')
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function update(Batch $batch, array $data): Batch
    {
        $batch->update($data);

        return $batch->fresh(['drug', 'pharmacy']);
    }

    public function delete(Batch $batch): void
    {
        if ($batch->quantity > 0) {
            throw new RuntimeException('No se puede dar de baja un lote con stock disponible.');
        }

        if ($batch->movements()->exists()) {
            throw new RuntimeException('No se puede eliminar un lote con movimientos registrados.');
        }

        $batch->delete();
    }
}
