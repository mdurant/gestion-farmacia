<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\BatchRepositoryInterface;
use App\Contracts\Repositories\InventoryMovementRepositoryInterface;
use App\Http\Requests\Inventory\UpdateBatchRequest;
use App\Models\Batch;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class BatchController extends Controller
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly InventoryMovementRepositoryInterface $movementRepository,
    ) {}

    public function show(Batch $batch): View
    {
        $this->authorize('view', $batch);

        $batch->load(['drug', 'pharmacy.costCenter']);

        return view('inventory.batches.show', [
            'batch' => $batch,
            'movements' => $this->movementRepository->paginate(['batch_id' => $batch->id], 15),
        ]);
    }

    public function edit(Batch $batch): View
    {
        $this->authorize('update', $batch);

        $batch->load(['drug', 'pharmacy']);

        return view('inventory.batches.edit', compact('batch'));
    }

    public function update(UpdateBatchRequest $request, Batch $batch): RedirectResponse
    {
        $this->batchRepository->update($batch, $request->validated());

        return redirect()
            ->route('inventory.batches.show', $batch)
            ->with('status', 'Lote actualizado correctamente.');
    }

    public function destroy(Batch $batch): RedirectResponse
    {
        $this->authorize('delete', $batch);

        try {
            $this->batchRepository->delete($batch);
        } catch (RuntimeException $e) {
            return back()->withErrors(['delete' => $e->getMessage()]);
        }

        return redirect()
            ->route('inventory.index')
            ->with('status', 'Lote dado de baja correctamente.');
    }
}
