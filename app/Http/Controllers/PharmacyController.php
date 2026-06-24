<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CostCenterRepositoryInterface;
use App\Contracts\Repositories\InventoryMovementRepositoryInterface;
use App\Contracts\Repositories\PharmacyRepositoryInterface;
use App\Enums\MovementType;
use App\Enums\PharmacyType;
use App\Http\Requests\Pharmacies\StorePharmacyRequest;
use App\Http\Requests\Pharmacies\UpdatePharmacyRequest;
use App\Models\Pharmacy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\RequestFilters;
use Illuminate\View\View;
use RuntimeException;

class PharmacyController extends Controller
{
    public function __construct(
        private readonly PharmacyRepositoryInterface $pharmacyRepository,
        private readonly CostCenterRepositoryInterface $costCenterRepository,
        private readonly InventoryMovementRepositoryInterface $movementRepository,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Pharmacy::class);

        $filters = [
            'search' => RequestFilters::optionalString($request, 'search'),
            'type' => RequestFilters::optionalString($request, 'type'),
            'cost_center_id' => RequestFilters::optionalInteger($request, 'cost_center_id'),
            'is_active' => RequestFilters::optionalBoolean($request, 'is_active'),
        ];

        return view('pharmacies.index', [
            'pharmacies' => $this->pharmacyRepository->paginate($filters),
            'costCenters' => $this->costCenterRepository->activeOptions(),
            'types' => PharmacyType::cases(),
            'filters' => $filters,
            'stats' => [
                'total' => Pharmacy::query()->count(),
                'active' => Pharmacy::query()->where('is_active', true)->count(),
                'with_stock' => Pharmacy::query()->whereHas('batches', fn ($q) => $q->where('quantity', '>', 0))->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Pharmacy::class);

        return view('pharmacies.create', [
            'types' => PharmacyType::cases(),
            'costCenters' => $this->costCenterRepository->activeOptions(),
        ]);
    }

    public function store(StorePharmacyRequest $request): RedirectResponse
    {
        $pharmacy = $this->pharmacyRepository->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('pharmacies.show', $pharmacy)
            ->with('status', 'Bodega registrada correctamente.');
    }

    public function show(Pharmacy $pharmacy): View
    {
        $this->authorize('view', $pharmacy);

        $pharmacy->load(['costCenter', 'batches.drug']);

        $totalStock = $pharmacy->batches->sum('quantity');
        $recentTransfers = $this->movementRepository->paginate([
            'movement_type' => MovementType::Transfer->value,
            'pharmacy_id' => $pharmacy->id,
        ], 10);

        return view('pharmacies.show', [
            'pharmacy' => $pharmacy,
            'totalStock' => $totalStock,
            'recentTransfers' => $recentTransfers,
        ]);
    }

    public function edit(Pharmacy $pharmacy): View
    {
        $this->authorize('update', $pharmacy);

        return view('pharmacies.edit', [
            'pharmacy' => $pharmacy,
            'types' => PharmacyType::cases(),
            'costCenters' => $this->costCenterRepository->activeOptions(),
        ]);
    }

    public function update(UpdatePharmacyRequest $request, Pharmacy $pharmacy): RedirectResponse
    {
        $this->pharmacyRepository->update($pharmacy, [
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('pharmacies.show', $pharmacy)
            ->with('status', 'Bodega actualizada correctamente.');
    }

    public function destroy(Pharmacy $pharmacy): RedirectResponse
    {
        $this->authorize('delete', $pharmacy);

        try {
            $this->pharmacyRepository->delete($pharmacy);
        } catch (RuntimeException $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()
            ->route('pharmacies.index')
            ->with('status', 'Bodega dada de baja correctamente.');
    }

    public function transfers(Request $request): View
    {
        $this->authorize('viewAny', Pharmacy::class);

        $filters = [
            'movement_type' => MovementType::Transfer->value,
            'pharmacy_id' => $request->input('pharmacy_id'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        return view('pharmacies.transfers', [
            'movements' => $this->movementRepository->paginate($filters),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }
}
