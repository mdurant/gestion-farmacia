<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\BatchRepositoryInterface;
use App\Contracts\Repositories\DrugRepositoryInterface;
use App\Contracts\Repositories\InventoryMovementRepositoryInterface;
use App\Models\Batch;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\SystemAlert;
use App\Support\RequestFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly DrugRepositoryInterface $drugRepository,
        private readonly InventoryMovementRepositoryInterface $movementRepository,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Drug::class);

        $filters = [
            'search' => RequestFilters::optionalString($request, 'search'),
            'pharmacy_id' => RequestFilters::optionalInteger($request, 'pharmacy_id'),
            'status' => RequestFilters::optionalString($request, 'status'),
        ];

        return view('inventory.index', [
            'batches' => $this->batchRepository->paginateForInventory($filters),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(),
            'stats' => $this->inventoryStats(),
            'filters' => $filters,
            'alerts' => SystemAlert::query()
                ->with(['drug', 'batch', 'pharmacy'])
                ->whereNull('read_at')
                ->whereIn('type', ['low_stock', 'expiring_soon'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function movements(Request $request): View
    {
        $this->authorize('viewAny', \App\Models\InventoryMovement::class);

        $filters = [
            'search' => RequestFilters::optionalString($request, 'search'),
            'movement_type' => RequestFilters::optionalString($request, 'movement_type'),
            'pharmacy_id' => RequestFilters::optionalInteger($request, 'pharmacy_id'),
            'from' => RequestFilters::optionalString($request, 'from'),
            'to' => RequestFilters::optionalString($request, 'to'),
        ];

        return view('inventory.movements.index', [
            'movements' => $this->movementRepository->paginate($filters),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(),
            'movementTypes' => \App\Enums\MovementType::cases(),
            'filters' => $filters,
        ]);
    }

    public function drugs(Request $request): View
    {
        $this->authorize('viewAny', Drug::class);

        return view('inventory.drugs.index', [
            'drugs' => $this->drugRepository->paginate([
                'search' => $request->string('search')->toString() ?: null,
                'controlled' => $request->boolean('controlled'),
            ]),
        ]);
    }

    /** @return array<string, int> */
    private function inventoryStats(): array
    {
        return [
            'drugs' => Drug::query()->where('is_active', true)->count(),
            'batches' => Batch::query()->where('quantity', '>', 0)->count(),
            'low_stock' => Drug::query()
                ->where('is_active', true)
                ->get()
                ->filter(function (Drug $drug): bool {
                    $total = Batch::query()->where('drug_id', $drug->id)->sum('quantity');

                    return $total <= $drug->min_stock;
                })
                ->count(),
            'expiring' => Batch::query()
                ->where('quantity', '>', 0)
                ->whereDate('expiration_date', '<=', now()->addDays(30))
                ->count(),
        ];
    }
}
