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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService implements ReportServiceInterface
{
    public function filtersFromRequest(Request $request): ReportFilters
    {
        return new ReportFilters(
            from: $request->input('from', now()->subMonth()->toDateString()),
            to: $request->input('to', now()->toDateString()),
            pharmacyId: $request->integer('pharmacy_id') ?: null,
            costCenterId: $request->integer('cost_center_id') ?: null,
            drugId: $request->integer('drug_id') ?: null,
            residentId: $request->integer('resident_id') ?: null,
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
