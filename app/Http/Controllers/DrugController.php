<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\DrugRepositoryInterface;
use App\Contracts\Repositories\InventoryMovementRepositoryInterface;
use App\Http\Requests\Inventory\StoreDrugRequest;
use App\Http\Requests\Inventory\UpdateDrugRequest;
use App\Models\Batch;
use App\Models\Drug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DrugController extends Controller
{
    public function __construct(
        private readonly DrugRepositoryInterface $drugRepository,
        private readonly InventoryMovementRepositoryInterface $movementRepository,
    ) {}

    public function create(): View
    {
        $this->authorize('create', Drug::class);

        return view('inventory.drugs.create');
    }

    public function store(StoreDrugRequest $request): RedirectResponse
    {
        $drug = $this->drugRepository->create([
            ...$request->validated(),
            'is_controlled' => $request->boolean('is_controlled'),
            'is_narcotic' => $request->boolean('is_narcotic'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('inventory.drugs.show', $drug)
            ->with('status', 'Fármaco registrado correctamente.');
    }

    public function show(Drug $drug): View
    {
        $this->authorize('view', $drug);

        $drug->load(['batches.pharmacy']);

        $stockByPharmacy = $drug->batches
            ->groupBy('pharmacy_id')
            ->map(fn ($batches) => $batches->sum('quantity'));

        return view('inventory.drugs.show', [
            'drug' => $drug,
            'stockByPharmacy' => $stockByPharmacy,
            'totalStock' => $drug->batches->sum('quantity'),
            'movements' => $this->movementRepository->paginate(['drug_id' => $drug->id]),
        ]);
    }

    public function edit(Drug $drug): View
    {
        $this->authorize('update', $drug);

        return view('inventory.drugs.edit', compact('drug'));
    }

    public function update(UpdateDrugRequest $request, Drug $drug): RedirectResponse
    {
        $this->drugRepository->update($drug, [
            ...$request->validated(),
            'is_controlled' => $request->boolean('is_controlled'),
            'is_narcotic' => $request->boolean('is_narcotic'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('inventory.drugs.show', $drug)
            ->with('status', 'Fármaco actualizado correctamente.');
    }
}
