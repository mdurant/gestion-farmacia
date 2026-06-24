<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\BatchRepositoryInterface;
use App\Contracts\Services\MovementServiceInterface;
use App\DTOs\Inventory\AdministrationMovementData;
use App\DTOs\Inventory\EntryMovementData;
use App\DTOs\Inventory\ExpirationMovementData;
use App\DTOs\Inventory\TransferMovementData;
use App\DTOs\Inventory\WasteMovementData;
use App\Exceptions\ControlledDrugAuthorizationRequiredException;
use App\Http\Requests\Inventory\AdministrationMovementRequest;
use App\Http\Requests\Inventory\EntryMovementRequest;
use App\Http\Requests\Inventory\ExpirationMovementRequest;
use App\Http\Requests\Inventory\TransferMovementRequest;
use App\Http\Requests\Inventory\WasteMovementRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Resident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class InventoryMovementController extends Controller
{
    public function __construct(
        private readonly MovementServiceInterface $movementService,
        private readonly BatchRepositoryInterface $batchRepository,
    ) {}

    public function storeWaste(WasteMovementRequest $request): JsonResponse
    {
        $movement = $this->movementService->processWasteExit(
            new WasteMovementData(
                batchId: $request->integer('batch_id'),
                pharmacyId: $request->integer('pharmacy_id'),
                costCenterId: $request->integer('cost_center_id'),
                userId: (int) $request->user()->id,
                quantity: $request->integer('quantity'),
                reason: $request->string('reason')->toString(),
                notes: $request->input('notes'),
                authorizationCode: $request->input('authorization_code'),
            ),
        );

        return (new InventoryMovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }

    public function createWaste(): View
    {
        $this->authorize('registerWaste', InventoryMovement::class);

        return view('inventory.movements.waste', $this->movementFormData());
    }

    public function storeWasteWeb(WasteMovementRequest $request): RedirectResponse
    {
        try {
            $this->movementService->processWasteExit(
                new WasteMovementData(
                    batchId: $request->integer('batch_id'),
                    pharmacyId: $request->integer('pharmacy_id'),
                    costCenterId: $request->integer('cost_center_id'),
                    userId: (int) $request->user()->id,
                    quantity: $request->integer('quantity'),
                    reason: $request->string('reason')->toString(),
                    notes: $request->input('notes'),
                    authorizationCode: $request->input('authorization_code'),
                ),
            );
        } catch (ControlledDrugAuthorizationRequiredException|RuntimeException $e) {
            return back()->withInput()->withErrors(['authorization_code' => $e->getMessage()]);
        }

        return redirect()
            ->route('inventory.movements.index')
            ->with('status', 'Salida por merma registrada correctamente.');
    }

    public function createEntry(): View
    {
        $this->authorize('create', InventoryMovement::class);

        return view('inventory.movements.entry', $this->movementFormData(includeDrugs: true));
    }

    public function storeEntry(EntryMovementRequest $request): RedirectResponse
    {
        try {
            $this->movementService->processEntry(new EntryMovementData(
                drugId: $request->integer('drug_id'),
                pharmacyId: $request->integer('pharmacy_id'),
                costCenterId: $request->integer('cost_center_id'),
                userId: (int) $request->user()->id,
                batchNumber: $request->string('batch_number')->toString(),
                expirationDate: $request->string('expiration_date')->toString(),
                quantity: $request->integer('quantity'),
                unitCost: (float) $request->input('unit_cost'),
                supplierName: $request->input('supplier_name'),
                supplierDocument: $request->input('supplier_document'),
                notes: $request->input('notes'),
            ));
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()
            ->route('inventory.index')
            ->with('status', 'Entrada de inventario registrada correctamente.');
    }

    public function createTransfer(): View
    {
        $this->authorize('create', InventoryMovement::class);

        return view('inventory.movements.transfer', $this->movementFormData());
    }

    public function storeTransfer(TransferMovementRequest $request): RedirectResponse
    {
        try {
            $this->movementService->processTransfer(new TransferMovementData(
                batchId: $request->integer('batch_id'),
                sourcePharmacyId: $request->integer('source_pharmacy_id'),
                destinationPharmacyId: $request->integer('destination_pharmacy_id'),
                costCenterId: $request->integer('cost_center_id'),
                userId: (int) $request->user()->id,
                quantity: $request->integer('quantity'),
                notes: $request->input('notes'),
                authorizationCode: $request->input('authorization_code'),
            ));
        } catch (ControlledDrugAuthorizationRequiredException|RuntimeException $e) {
            return back()->withInput()->withErrors(['authorization_code' => $e->getMessage()]);
        }

        return redirect()
            ->route('inventory.movements.index')
            ->with('status', 'Traslado entre bodegas registrado correctamente.');
    }

    public function createAdministration(Request $request): View
    {
        $this->authorize('create', InventoryMovement::class);

        return view('inventory.movements.administration', [
            ...$this->movementFormData(),
            'residents' => Resident::query()
                ->where('is_active', true)
                ->get()
                ->sortBy(fn (Resident $resident) => $resident->last_name)
                ->values(),
            'preselectedResidentId' => $request->integer('resident_id') ?: null,
        ]);
    }

    public function storeAdministration(AdministrationMovementRequest $request): RedirectResponse
    {
        try {
            $this->movementService->processAdministration(new AdministrationMovementData(
                batchId: $request->integer('batch_id'),
                pharmacyId: $request->integer('pharmacy_id'),
                costCenterId: $request->integer('cost_center_id'),
                residentId: $request->integer('resident_id'),
                userId: (int) $request->user()->id,
                quantity: $request->integer('quantity'),
                prescriptionId: $request->input('prescription_id'),
                notes: $request->input('notes'),
                authorizationCode: $request->input('authorization_code'),
            ));
        } catch (ControlledDrugAuthorizationRequiredException|RuntimeException $e) {
            return back()->withInput()->withErrors(['authorization_code' => $e->getMessage()]);
        }

        return redirect()
            ->route('inventory.movements.index')
            ->with('status', 'Administración a residente registrada correctamente.');
    }

    public function createExpiration(): View
    {
        $this->authorize('create', InventoryMovement::class);

        return view('inventory.movements.expiration', $this->movementFormData());
    }

    public function storeExpiration(ExpirationMovementRequest $request): RedirectResponse
    {
        try {
            $this->movementService->processExpirationExit(new ExpirationMovementData(
                batchId: $request->integer('batch_id'),
                pharmacyId: $request->integer('pharmacy_id'),
                costCenterId: $request->integer('cost_center_id'),
                userId: (int) $request->user()->id,
                quantity: $request->integer('quantity'),
                notes: $request->input('notes'),
                authorizationCode: $request->input('authorization_code'),
            ));
        } catch (ControlledDrugAuthorizationRequiredException|RuntimeException $e) {
            return back()->withInput()->withErrors(['authorization_code' => $e->getMessage()]);
        }

        return redirect()
            ->route('inventory.movements.index')
            ->with('status', 'Salida por vencimiento registrada correctamente.');
    }

    /** @return array<string, mixed> */
    private function movementFormData(bool $includeDrugs = false): array
    {
        $data = [
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(),
            'costCenters' => CostCenter::query()->orderBy('name')->get(),
            'batches' => \App\Models\Batch::query()
                ->with(['drug', 'pharmacy'])
                ->where('quantity', '>', 0)
                ->orderBy('expiration_date')
                ->get(),
        ];

        if ($includeDrugs) {
            $data['drugs'] = Drug::query()->where('is_active', true)->orderBy('name')->get();
        }

        return $data;
    }
}
