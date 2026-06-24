<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CostCenterRepositoryInterface;
use App\Http\Requests\Pharmacies\StoreCostCenterRequest;
use App\Http\Requests\Pharmacies\UpdateCostCenterRequest;
use App\Models\CostCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CostCenterController extends Controller
{
    public function __construct(
        private readonly CostCenterRepositoryInterface $costCenterRepository,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CostCenter::class);

        return view('pharmacies.cost-centers.index', [
            'costCenters' => $this->costCenterRepository->paginate([
                'search' => $request->string('search')->toString() ?: null,
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : null,
            ]),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CostCenter::class);

        return view('pharmacies.cost-centers.create');
    }

    public function store(StoreCostCenterRequest $request): RedirectResponse
    {
        $costCenter = $this->costCenterRepository->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('pharmacies.cost-centers.show', $costCenter)
            ->with('status', 'Centro de costo registrado correctamente.');
    }

    public function show(CostCenter $costCenter): View
    {
        $this->authorize('view', $costCenter);

        $costCenter->load('pharmacies');

        return view('pharmacies.cost-centers.show', compact('costCenter'));
    }

    public function edit(CostCenter $costCenter): View
    {
        $this->authorize('update', $costCenter);

        return view('pharmacies.cost-centers.edit', compact('costCenter'));
    }

    public function update(UpdateCostCenterRequest $request, CostCenter $costCenter): RedirectResponse
    {
        $this->costCenterRepository->update($costCenter, [
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('pharmacies.cost-centers.show', $costCenter)
            ->with('status', 'Centro de costo actualizado correctamente.');
    }

    public function destroy(CostCenter $costCenter): RedirectResponse
    {
        $this->authorize('delete', $costCenter);

        if ($costCenter->pharmacies()->exists()) {
            return back()->withErrors(['delete' => 'No se puede eliminar un centro de costo con bodegas asociadas.']);
        }

        $costCenter->delete();

        return redirect()
            ->route('pharmacies.cost-centers.index')
            ->with('status', 'Centro de costo dado de baja correctamente.');
    }
}
