<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CostCenterRepositoryInterface;
use App\Contracts\Repositories\InventoryMovementRepositoryInterface;
use App\Contracts\Repositories\ResidentRepositoryInterface;
use App\Enums\MovementType;
use App\Enums\ResidentAccessAction;
use App\Events\ResidentRegistered;
use App\Http\Requests\Residents\StoreResidentRequest;
use App\Http\Requests\Residents\UpdateResidentRequest;
use App\Models\HealthInsurance;
use App\Models\Resident;
use App\Services\ResidentAccessLogService;
use App\Support\RequestFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResidentController extends Controller
{
    public function __construct(
        private readonly ResidentRepositoryInterface $residentRepository,
        private readonly CostCenterRepositoryInterface $costCenterRepository,
        private readonly InventoryMovementRepositoryInterface $movementRepository,
        private readonly ResidentAccessLogService $residentAccessLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Resident::class);

        $filters = [
            'search' => RequestFilters::optionalString($request, 'search'),
            'cost_center_id' => RequestFilters::optionalInteger($request, 'cost_center_id'),
            'is_active' => RequestFilters::optionalBoolean($request, 'is_active'),
        ];

        return view('residents.index', [
            'residents' => $this->residentRepository->paginate($filters),
            'costCenters' => $this->costCenterRepository->activeOptions(),
            'filters' => $filters,
            'stats' => [
                'total' => Resident::query()->count(),
                'active' => Resident::query()->where('is_active', true)->count(),
                'with_medications' => Resident::query()
                    ->whereHas('treatments', fn ($q) => $q->where('is_active', true))
                    ->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Resident::class);

        return view('residents.create', [
            'costCenters' => $this->costCenterRepository->activeOptions(),
            'healthInsurances' => HealthInsurance::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreResidentRequest $request): RedirectResponse
    {
        $resident = $this->residentRepository->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->residentAccessLogService->log(
            $resident,
            ResidentAccessAction::Create,
            newValues: $this->residentAccessLogService->snapshot($resident),
        );

        ResidentRegistered::dispatch($resident, $request->user());

        return redirect()
            ->route('residents.show', $resident)
            ->with('status', 'Residente registrado correctamente.');
    }

    public function show(Resident $resident): View
    {
        $this->authorize('view', $resident);

        $resident->load(['costCenter', 'healthInsurance', 'treatments.drug', 'treatments.presentation']);

        $this->residentAccessLogService->log(
            $resident,
            ResidentAccessAction::View,
            newValues: $this->residentAccessLogService->snapshot($resident),
        );

        return view('residents.show', [
            'resident' => $resident,
            'administrations' => $this->movementRepository->paginate([
                'resident_id' => $resident->id,
                'movement_type' => MovementType::ExitAdministration->value,
            ], 15),
            'stats' => [
                'total_administrations' => $resident->movements()
                    ->where('movement_type', MovementType::ExitAdministration->value)
                    ->count(),
                'last_administration' => $resident->movements()
                    ->where('movement_type', MovementType::ExitAdministration->value)
                    ->latest('movement_at')
                    ->value('movement_at'),
                'active_treatments' => $resident->treatments()->where('is_active', true)->count(),
            ],
        ]);
    }

    public function edit(Resident $resident): View
    {
        $this->authorize('update', $resident);

        $this->residentAccessLogService->log(
            $resident,
            ResidentAccessAction::View,
            newValues: $this->residentAccessLogService->snapshot($resident),
        );

        return view('residents.edit', [
            'resident' => $resident,
            'costCenters' => $this->costCenterRepository->activeOptions(),
            'healthInsurances' => HealthInsurance::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateResidentRequest $request, Resident $resident): RedirectResponse
    {
        $oldSnapshot = $this->residentAccessLogService->snapshot($resident);

        $this->residentRepository->update($resident, [
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $resident->refresh();

        $this->residentAccessLogService->log(
            $resident,
            ResidentAccessAction::Update,
            oldValues: $oldSnapshot,
            newValues: $this->residentAccessLogService->snapshot($resident),
        );

        return redirect()
            ->route('residents.show', $resident)
            ->with('status', 'Residente actualizado correctamente.');
    }

    public function destroy(Resident $resident): RedirectResponse
    {
        $this->authorize('delete', $resident);

        $oldSnapshot = $this->residentAccessLogService->snapshot($resident);

        $this->residentRepository->delete($resident);

        $this->residentAccessLogService->log(
            $resident,
            ResidentAccessAction::Delete,
            oldValues: $oldSnapshot,
        );

        return redirect()
            ->route('residents.index')
            ->with('status', 'Residente dado de baja correctamente.');
    }
}
