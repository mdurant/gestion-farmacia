<?php

namespace App\Services;

use App\Contracts\Services\ReportServiceInterface;
use App\DTOs\Reports\ReportFilters;
use App\Enums\MovementType;
use App\Models\Batch;
use App\Models\Drug;
use App\Models\InventoryMovement;
use App\Models\Resident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService implements ReportServiceInterface
{
    public function filtersFromRequest(Request $request): ReportFilters
    {
        $movementType = $request->input('movement_type');
        if (is_string($movementType) && MovementType::tryFrom($movementType) === null) {
            $movementType = null;
        }

        return new ReportFilters(
            from: $request->input('from', now()->subMonth()->toDateString()),
            to: $request->input('to', now()->toDateString()),
            pharmacyId: $request->integer('pharmacy_id') ?: null,
            costCenterId: $request->integer('cost_center_id') ?: null,
            drugId: $request->integer('drug_id') ?: null,
            residentId: $request->integer('resident_id') ?: null,
            movementType: is_string($movementType) && $movementType !== '' ? $movementType : null,
            userId: $request->integer('user_id') ?: null,
        );
    }

    public function kardex(ReportFilters $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->movementQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function residentConsumption(ReportFilters $filters): Collection
    {
        $movements = $this->movementQuery($filters)
            ->where('movement_type', MovementType::ExitAdministration->value)
            ->get();

        $residents = Resident::query()
            ->whereIn('id', $movements->pluck('resident_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        return $movements
            ->groupBy('resident_id')
            ->map(function (Collection $items, $residentId) use ($residents) {
                $resident = $residents->get($residentId);

                return (object) [
                    'resident_id' => (int) $residentId,
                    'resident_name' => $resident?->full_name ?? 'Residente #'.$residentId,
                    'room_number' => $resident?->room_number,
                    'total_quantity' => $items->sum('quantity'),
                    'total_value' => $items->sum('total_value'),
                    'administrations_count' => $items->count(),
                    'drugs' => $items->groupBy('drug_id')->map(fn (Collection $drugItems) => (object) [
                        'drug_name' => $drugItems->first()?->drug?->name,
                        'quantity' => $drugItems->sum('quantity'),
                        'value' => $drugItems->sum('total_value'),
                    ])->values(),
                ];
            })
            ->sortBy('resident_name')
            ->values();
    }

    public function valuation(ReportFilters $filters): array
    {
        $query = Batch::query()
            ->with(['drug', 'pharmacy'])
            ->where('quantity', '>', 0)
            ->when($filters->pharmacyId, fn ($q, $id) => $q->where('pharmacy_id', $id))
            ->when($filters->drugId, fn ($q, $id) => $q->where('drug_id', $id))
            ->when($filters->costCenterId, fn ($q, $id) => $q->whereHas('pharmacy', fn ($pq) => $pq->where('cost_center_id', $id)));

        $batches = $query->get();

        $byPharmacy = $batches->groupBy('pharmacy_id')->map(fn (Collection $items) => (object) [
            'pharmacy_name' => $items->first()?->pharmacy?->name ?? '—',
            'total_units' => $items->sum('quantity'),
            'total_value' => $items->sum(fn (Batch $batch) => $batch->quantity * (float) $batch->unit_cost),
        ])->values();

        $byDrug = $batches->groupBy('drug_id')->map(fn (Collection $items) => (object) [
            'drug_name' => $items->first()?->drug?->name ?? '—',
            'drug_code' => $items->first()?->drug?->code,
            'total_units' => $items->sum('quantity'),
            'total_value' => $items->sum(fn (Batch $batch) => $batch->quantity * (float) $batch->unit_cost),
        ])->sortByDesc('total_value')->values();

        return [
            'total_value' => $batches->sum(fn (Batch $batch) => $batch->quantity * (float) $batch->unit_cost),
            'total_units' => $batches->sum('quantity'),
            'batch_count' => $batches->count(),
            'by_pharmacy' => $byPharmacy,
            'by_drug' => $byDrug,
        ];
    }

    public function monthlyWaste(ReportFilters $filters): Collection
    {
        return $this->movementQuery($filters)
            ->where('movement_type', MovementType::ExitWaste->value)
            ->get()
            ->groupBy(fn (InventoryMovement $movement) => $movement->movement_at?->format('Y-m') ?? 'sin-fecha')
            ->map(fn (Collection $items, string $month) => (object) [
                'month' => $month,
                'month_label' => $this->formatMonthLabel($month),
                'movements_count' => $items->count(),
                'total_quantity' => $items->sum('quantity'),
                'total_value' => $items->sum('total_value'),
            ])
            ->sortByDesc('month')
            ->values();
    }

    public function purchaseProjection(ReportFilters $filters): Collection
    {
        return Drug::query()
            ->where('is_active', true)
            ->when($filters->drugId, fn ($q, $id) => $q->where('id', $id))
            ->orderBy('name')
            ->get()
            ->map(function (Drug $drug) use ($filters) {
                $stockQuery = Batch::query()->where('drug_id', $drug->id)->where('quantity', '>', 0);
                if ($filters->pharmacyId) {
                    $stockQuery->where('pharmacy_id', $filters->pharmacyId);
                }

                $currentStock = (int) $stockQuery->sum('quantity');
                $target = $drug->max_stock ?? ($drug->min_stock * 2);
                $deficit = max(0, $target - $currentStock);

                return (object) [
                    'drug' => $drug,
                    'current_stock' => $currentStock,
                    'min_stock' => $drug->min_stock,
                    'max_stock' => $drug->max_stock,
                    'target_stock' => $target,
                    'suggested_purchase' => $deficit,
                    'is_critical' => $currentStock <= $drug->min_stock,
                    'estimated_cost' => $deficit * (float) $drug->unit_cost,
                ];
            })
            ->filter(fn ($row) => $row->is_critical || $row->suggested_purchase > 0)
            ->values();
    }

    public function kardexRows(ReportFilters $filters): array
    {
        return $this->movementQuery($filters)->get()->map(fn (InventoryMovement $m) => [
            'Fecha' => $m->movement_at?->timezone('America/Santiago')->format('d/m/Y H:i'),
            'Tipo' => $m->movement_type->label(),
            'Fármaco' => $m->drug?->name,
            'Código' => $m->drug?->code,
            'Lote' => $m->batch?->batch_number,
            'Cantidad' => $m->quantity,
            'Bodega' => $m->pharmacy?->name,
            'Centro costo' => $m->costCenter?->name,
            'Profesional' => $m->user?->display_name,
            'Valor CLP' => $m->total_value,
        ])->all();
    }

    public function residentConsumptionRows(ReportFilters $filters): array
    {
        return $this->residentConsumption($filters)->flatMap(function ($row) {
            return $row->drugs->map(fn ($drug) => [
                'Residente' => $row->resident_name,
                'Habitación' => $row->room_number,
                'Fármaco' => $drug->drug_name,
                'Cantidad' => $drug->quantity,
                'Valor CLP' => $drug->value,
                'Administraciones' => $row->administrations_count,
            ]);
        })->all();
    }

    public function valuationRows(ReportFilters $filters): array
    {
        $data = $this->valuation($filters);

        return $data['by_drug']->map(fn ($row) => [
            'Fármaco' => $row->drug_name,
            'Código' => $row->drug_code,
            'Unidades' => $row->total_units,
            'Valor CLP' => $row->total_value,
        ])->all();
    }

    public function monthlyWasteRows(ReportFilters $filters): array
    {
        return $this->monthlyWaste($filters)->map(fn ($row) => [
            'Mes' => $row->month_label,
            'Movimientos' => $row->movements_count,
            'Cantidad' => $row->total_quantity,
            'Valor CLP' => $row->total_value,
        ])->all();
    }

    public function purchaseProjectionRows(ReportFilters $filters): array
    {
        return $this->purchaseProjection($filters)->map(fn ($row) => [
            'Fármaco' => $row->drug->name,
            'Código' => $row->drug->code,
            'Stock actual' => $row->current_stock,
            'Stock mínimo' => $row->min_stock,
            'Objetivo' => $row->target_stock,
            'Compra sugerida' => $row->suggested_purchase,
            'Costo estimado CLP' => $row->estimated_cost,
            'Estado' => $row->is_critical ? 'Crítico' : 'Reposición',
        ])->all();
    }

    public function charts(ReportFilters $filters): array
    {
        $batches = $this->batchQuery($filters)->with('drug')->get();
        $movements = $this->movementQuery($filters)->with('drug')->get();

        return [
            'inventory_by_category' => $this->chartInventoryByCategory($batches),
            'expiry_gauge' => $this->chartExpiryGauge($batches),
            'consumption_trend' => $this->chartConsumptionTrend($movements),
            'rotation_bubble' => $this->chartRotationBubble($batches, $movements),
            'supplier_scatter' => $this->chartSupplierScatter($batches, $movements),
            'purchases_by_supplier' => $this->chartPurchasesBySupplier($batches, $movements),
            'loss_control' => $this->chartLossControl($movements),
            'movement_funnel' => $this->chartMovementFunnel($movements),
        ];
    }

    /** @param Collection<int, Batch> $batches */
    private function chartInventoryByCategory(Collection $batches): array
    {
        $byCategory = $batches
            ->groupBy(fn (Batch $batch) => $batch->drug?->category ?: 'Sin categoría')
            ->map(function (Collection $items, string $category) {
                $currentValue = $items->sum(fn (Batch $batch) => $batch->quantity * (float) $batch->unit_cost);
                $drugs = $items->groupBy('drug_id');
                $minValue = $drugs->sum(function (Collection $drugBatches) {
                    /** @var Batch $first */
                    $first = $drugBatches->first();
                    $minStock = (int) ($first->drug?->min_stock ?? 0);
                    $unitCost = (float) ($first->drug?->unit_cost ?? $first->unit_cost);

                    return $minStock * $unitCost;
                });

                return [
                    'category' => $category,
                    'current_value' => round($currentValue, 0),
                    'min_value' => round($minValue, 0),
                ];
            })
            ->sortBy('category')
            ->values();

        return [
            'labels' => $byCategory->pluck('category')->all(),
            'current_value' => $byCategory->pluck('current_value')->all(),
            'min_value' => $byCategory->pluck('min_value')->all(),
        ];
    }

    /** @param Collection<int, Batch> $batches */
    private function chartExpiryGauge(Collection $batches): array
    {
        $totalUnits = (int) $batches->sum('quantity');
        $within90 = (int) $batches
            ->filter(fn (Batch $batch) => $batch->expiration_date?->lte(now()->addDays(90)))
            ->sum('quantity');
        $within180 = (int) $batches
            ->filter(fn (Batch $batch) => $batch->expiration_date?->lte(now()->addDays(180)))
            ->sum('quantity');

        $percent = $totalUnits > 0 ? round(($within90 / $totalUnits) * 100, 1) : 0.0;

        $level = match (true) {
            $percent <= 10 => 'green',
            $percent <= 25 => 'yellow',
            default => 'red',
        };

        return [
            'total_units' => $totalUnits,
            'within_90' => $within90,
            'within_180' => $within180,
            'percent' => $percent,
            'level' => $level,
            'thresholds' => [
                'green' => 10,
                'yellow' => 25,
            ],
        ];
    }

    /** @param Collection<int, InventoryMovement> $movements */
    private function chartConsumptionTrend(Collection $movements): array
    {
        $admins = $movements->where('movement_type', MovementType::ExitAdministration);

        $months = $admins
            ->groupBy(fn (InventoryMovement $m) => $m->movement_at?->format('Y-m') ?? 'sin-fecha')
            ->sortKeys();

        $labels = [];
        $controlled = [];
        $nonControlled = [];

        foreach ($months as $month => $items) {
            if ($month === 'sin-fecha') {
                continue;
            }

            $labels[] = $this->formatMonthLabel($month);
            $controlled[] = round($items
                ->filter(fn (InventoryMovement $m) => (bool) ($m->drug?->is_controlled || $m->drug?->is_narcotic))
                ->sum('total_value'), 0);
            $nonControlled[] = round($items
                ->filter(fn (InventoryMovement $m) => ! ($m->drug?->is_controlled || $m->drug?->is_narcotic))
                ->sum('total_value'), 0);
        }

        return [
            'labels' => $labels,
            'controlled' => $controlled,
            'non_controlled' => $nonControlled,
        ];
    }

    /**
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, InventoryMovement>  $movements
     */
    private function chartRotationBubble(Collection $batches, Collection $movements): array
    {
        $admins = $movements->where('movement_type', MovementType::ExitAdministration);
        $stockByDrug = $batches->groupBy('drug_id');

        return $admins
            ->groupBy('drug_id')
            ->map(function (Collection $items, $drugId) use ($stockByDrug) {
                /** @var InventoryMovement $first */
                $first = $items->first();
                $drug = $first->drug;
                $stockBatches = $stockByDrug->get($drugId, collect());
                $stockValue = $stockBatches->sum(fn (Batch $batch) => $batch->quantity * (float) $batch->unit_cost);
                $rotation = (float) $items->sum('quantity');
                $consumedValue = (float) $items->sum('total_value');
                $unitCost = (float) ($drug?->unit_cost ?? $first->unit_cost);
                // Margen operativo proxy: valor consumido relativo al stock retenido (mayor = más eficiente).
                $efficiency = $stockValue > 0
                    ? round(($consumedValue / $stockValue) * 100, 1)
                    : round($consumedValue > 0 ? 100 : 0, 1);

                return [
                    'label' => $drug?->name ?? 'Fármaco #'.$drugId,
                    'x' => $rotation,
                    'y' => $efficiency,
                    'r' => max(4, min(28, sqrt(max($unitCost, 1)) / 2)),
                    'unit_cost' => $unitCost,
                    'stock_value' => round($stockValue, 0),
                    'consumed_value' => round($consumedValue, 0),
                ];
            })
            ->sortByDesc('x')
            ->take(20)
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, InventoryMovement>  $movements
     */
    private function chartSupplierScatter(Collection $batches, Collection $movements): array
    {
        $exits = $movements->filter(fn (InventoryMovement $m) => $m->movement_type->isExit());
        $entries = $movements->where('movement_type', MovementType::Entry);

        $suppliers = $batches
            ->pluck('supplier_name')
            ->merge($entries->map(fn (InventoryMovement $m) => $m->batch?->supplier_name))
            ->filter()
            ->unique()
            ->values();

        if ($suppliers->isEmpty()) {
            return [];
        }

        $batchesBySupplier = $batches->groupBy(fn (Batch $b) => $b->supplier_name ?: 'Sin proveedor');

        return $suppliers->map(function (string $supplier) use ($batchesBySupplier, $exits, $entries) {
            $supplierBatches = $batchesBySupplier->get($supplier, collect());
            $batchIds = $supplierBatches->pluck('id');

            $leadDays = $supplierBatches
                ->filter(fn (Batch $batch) => $batch->received_at !== null)
                ->map(function (Batch $batch) use ($exits) {
                    $firstExit = $exits
                        ->where('batch_id', $batch->id)
                        ->sortBy('movement_at')
                        ->first();

                    if ($firstExit?->movement_at === null) {
                        return null;
                    }

                    return max(0, $batch->received_at->diffInDays($firstExit->movement_at));
                })
                ->filter(fn ($days) => $days !== null);

            // Proxy de tiempo de entrega: días promedio entre entradas consecutivas del proveedor.
            $entryDates = $entries
                ->filter(fn (InventoryMovement $m) => $batchIds->contains($m->batch_id) || ($m->batch?->supplier_name === $supplier))
                ->pluck('movement_at')
                ->filter()
                ->sort()
                ->values();

            $gaps = [];
            for ($i = 1; $i < $entryDates->count(); $i++) {
                $gaps[] = $entryDates[$i - 1]->diffInDays($entryDates[$i]);
            }

            $avgDeliveryDays = count($gaps) > 0
                ? round(array_sum($gaps) / count($gaps), 1)
                : ($leadDays->isNotEmpty() ? round($leadDays->avg(), 1) : 0.0);

            $exitItems = $exits->filter(fn (InventoryMovement $m) => $batchIds->contains($m->batch_id)
                || ($m->batch?->supplier_name === $supplier));

            $administered = (float) $exitItems
                ->where('movement_type', MovementType::ExitAdministration)
                ->sum('quantity');
            $lost = (float) $exitItems
                ->filter(fn (InventoryMovement $m) => in_array($m->movement_type, [
                    MovementType::ExitWaste,
                    MovementType::ExitExpiration,
                ], true))
                ->sum('quantity');

            $total = $administered + $lost;
            $compliance = $total > 0 ? round(($administered / $total) * 100, 1) : 0.0;

            return [
                'label' => $supplier,
                'x' => $avgDeliveryDays,
                'y' => $compliance,
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, InventoryMovement>  $movements
     */
    private function chartPurchasesBySupplier(Collection $batches, Collection $movements): array
    {
        $entries = $movements->where('movement_type', MovementType::Entry);

        $bySupplier = $entries
            ->groupBy(function (InventoryMovement $m) use ($batches) {
                return $m->batch?->supplier_name
                    ?? $batches->firstWhere('id', $m->batch_id)?->supplier_name
                    ?? 'Sin proveedor';
            })
            ->map(fn (Collection $items, string $supplier) => [
                'supplier' => $supplier,
                'value' => round($items->sum('total_value'), 0),
            ])
            ->sortByDesc('value')
            ->values();

        if ($bySupplier->isEmpty()) {
            $bySupplier = $batches
                ->groupBy(fn (Batch $b) => $b->supplier_name ?: 'Sin proveedor')
                ->map(fn (Collection $items, string $supplier) => [
                    'supplier' => $supplier,
                    'value' => round($items->sum(fn (Batch $b) => $b->quantity * (float) $b->unit_cost), 0),
                ])
                ->sortByDesc('value')
                ->values();
        }

        return [
            'labels' => $bySupplier->pluck('supplier')->all(),
            'values' => $bySupplier->pluck('value')->all(),
        ];
    }

    /** @param Collection<int, InventoryMovement> $movements */
    private function chartLossControl(Collection $movements): array
    {
        $losses = $movements->filter(fn (InventoryMovement $m) => in_array($m->movement_type, [
            MovementType::ExitWaste,
            MovementType::ExitExpiration,
        ], true));

        $byDay = $losses
            ->groupBy(fn (InventoryMovement $m) => $m->movement_at?->toDateString() ?? 'sin-fecha')
            ->sortKeys();

        $labels = [];
        $values = [];

        foreach ($byDay as $day => $items) {
            if ($day === 'sin-fecha') {
                continue;
            }
            $labels[] = Carbon::parse($day)->format('d/m');
            $values[] = round($items->sum('total_value'), 0);
        }

        $count = count($values);
        $mean = $count > 0 ? array_sum($values) / $count : 0.0;
        $variance = $count > 1
            ? array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $values)) / ($count - 1)
            : 0.0;
        $std = sqrt($variance);
        $ucl = round($mean + (2 * $std), 0);
        $lcl = round(max(0, $mean - (2 * $std)), 0);
        $meanRounded = round($mean, 0);

        $alerts = [];
        foreach ($values as $index => $value) {
            if ($value > $ucl || $value < $lcl) {
                $alerts[] = [
                    'index' => $index,
                    'label' => $labels[$index],
                    'value' => $value,
                ];
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'mean' => $meanRounded,
            'ucl' => $ucl,
            'lcl' => $lcl,
            'alerts' => $alerts,
        ];
    }

    /** @param Collection<int, InventoryMovement> $movements */
    private function chartMovementFunnel(Collection $movements): array
    {
        $stages = [
            'Entradas (recepción)' => MovementType::Entry,
            'Traslados entre bodegas' => MovementType::Transfer,
            'Administraciones' => MovementType::ExitAdministration,
            'Mermas y vencimientos' => null,
        ];

        $labels = [];
        $values = [];

        foreach ($stages as $label => $type) {
            $labels[] = $label;
            if ($type === null) {
                $values[] = round($movements
                    ->filter(fn (InventoryMovement $m) => in_array($m->movement_type, [
                        MovementType::ExitWaste,
                        MovementType::ExitExpiration,
                    ], true))
                    ->sum('total_value'), 0);
            } else {
                $values[] = round($movements->where('movement_type', $type)->sum('total_value'), 0);
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /** @return Builder<Batch> */
    private function batchQuery(ReportFilters $filters): Builder
    {
        return Batch::query()
            ->where('quantity', '>', 0)
            ->when($filters->pharmacyId, fn ($q, $id) => $q->where('pharmacy_id', $id))
            ->when($filters->drugId, fn ($q, $id) => $q->where('drug_id', $id))
            ->when($filters->costCenterId, fn ($q, $id) => $q->whereHas(
                'pharmacy',
                fn ($pq) => $pq->where('cost_center_id', $id),
            ));
    }

    /** @return Builder<InventoryMovement> */
    private function movementQuery(ReportFilters $filters): Builder
    {
        return InventoryMovement::query()
            ->with(['drug', 'pharmacy', 'destinationPharmacy', 'batch', 'costCenter', 'user', 'resident'])
            ->when($filters->from, fn ($q, $from) => $q->whereDate('movement_at', '>=', $from))
            ->when($filters->to, fn ($q, $to) => $q->whereDate('movement_at', '<=', $to))
            ->when($filters->pharmacyId, fn ($q, $id) => $q->where(function ($inner) use ($id): void {
                $inner->where('pharmacy_id', $id)->orWhere('destination_pharmacy_id', $id);
            }))
            ->when($filters->costCenterId, fn ($q, $id) => $q->where('cost_center_id', $id))
            ->when($filters->drugId, fn ($q, $id) => $q->where('drug_id', $id))
            ->when($filters->residentId, fn ($q, $id) => $q->where('resident_id', $id))
            ->when($filters->movementType, fn ($q, $type) => $q->where('movement_type', $type))
            ->when($filters->userId, fn ($q, $id) => $q->where('user_id', $id))
            ->orderByDesc('movement_at');
    }

    private function formatMonthLabel(string $month): string
    {
        if ($month === 'sin-fecha') {
            return 'Sin fecha';
        }

        [$year, $monthNum] = explode('-', $month);
        $months = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        return ($months[(int) $monthNum] ?? $month).' '.$year;
    }
}
