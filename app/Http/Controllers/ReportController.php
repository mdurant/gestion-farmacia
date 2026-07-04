<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CostCenterRepositoryInterface;
use App\Contracts\Services\ReportServiceInterface;
use App\Enums\ReportType;
use App\Enums\MovementType;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\Resident;
use App\Models\User;
use App\Support\ReportExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportServiceInterface $reportService,
        private readonly CostCenterRepositoryInterface $costCenterRepository,
    ) {}

    public function index(): View
    {
        $this->authorize('reports.internal');

        return view('reports.index');
    }

    public function kardex(Request $request): View
    {
        $this->authorize('reports.internal');

        $filters = $this->reportService->filtersFromRequest($request);

        return view('reports.kardex', [
            ...$this->filterOptions(),
            'filters' => $filters,
            'movements' => $this->reportService->kardex($filters),
        ]);
    }

    public function residentConsumption(Request $request): View
    {
        $this->authorize('reports.internal');

        $filters = $this->reportService->filtersFromRequest($request);

        return view('reports.resident-consumption', [
            ...$this->filterOptions(),
            'filters' => $filters,
            'rows' => $this->reportService->residentConsumption($filters),
        ]);
    }

    public function valuation(Request $request): View
    {
        $this->authorize('reports.executive');

        $filters = $this->reportService->filtersFromRequest($request);

        return view('reports.valuation', [
            ...$this->filterOptions(),
            'filters' => $filters,
            'data' => $this->reportService->valuation($filters),
        ]);
    }

    public function monthlyWaste(Request $request): View
    {
        $this->authorize('reports.executive');

        $filters = $this->reportService->filtersFromRequest($request);

        return view('reports.monthly-waste', [
            ...$this->filterOptions(),
            'filters' => $filters,
            'rows' => $this->reportService->monthlyWaste($filters),
        ]);
    }

    public function purchaseProjection(Request $request): View
    {
        $this->authorize('reports.executive');

        $filters = $this->reportService->filtersFromRequest($request);

        return view('reports.purchase-projection', [
            ...$this->filterOptions(),
            'filters' => $filters,
            'rows' => $this->reportService->purchaseProjection($filters),
        ]);
    }

    public function charts(Request $request): View
    {
        $this->authorize('reports.executive');

        if (! $request->filled('from')) {
            $request->merge(['from' => now()->subMonths(6)->toDateString()]);
        }

        $filters = $this->reportService->filtersFromRequest($request);

        return view('reports.charts', [
            ...$this->filterOptions(),
            'filters' => $filters,
            'charts' => $this->reportService->charts($filters),
        ]);
    }

    public function export(Request $request, string $report, string $format): Response
    {
        $type = ReportType::fromSlug($report);
        $filters = $this->reportService->filtersFromRequest($request);

        if ($type->isExecutive()) {
            $this->authorize('reports.executive');
        } else {
            $this->authorize('reports.internal');
        }

        abort_if($type === ReportType::Charts, 404);
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        [$headers, $rows, $title] = match ($type) {
            ReportType::Kardex => [
                ['Fecha', 'Tipo', 'Fármaco', 'Código', 'Lote', 'Cantidad', 'Bodega', 'Centro costo', 'Profesional', 'Valor CLP'],
                $this->reportService->kardexRows($filters),
                'Kardex',
            ],
            ReportType::ResidentConsumption => [
                ['Residente', 'Habitación', 'Fármaco', 'Cantidad', 'Valor CLP', 'Administraciones'],
                $this->reportService->residentConsumptionRows($filters),
                'Consumo por residente',
            ],
            ReportType::Valuation => [
                ['Fármaco', 'Código', 'Unidades', 'Valor CLP'],
                $this->reportService->valuationRows($filters),
                'Valorización de inventario',
            ],
            ReportType::MonthlyWaste => [
                ['Mes', 'Movimientos', 'Cantidad', 'Valor CLP'],
                $this->reportService->monthlyWasteRows($filters),
                'Mermas mensuales',
            ],
            ReportType::PurchaseProjection => [
                ['Fármaco', 'Código', 'Stock actual', 'Stock mínimo', 'Objetivo', 'Compra sugerida', 'Costo estimado CLP', 'Estado'],
                $this->reportService->purchaseProjectionRows($filters),
                'Proyección de compra',
            ],
        };

        $filename = str($title)->slug().'-'.now()->format('Y-m-d').'.'.$format;

        if ($format === 'csv') {
            return ReportExporter::csv($filename, $headers, $rows);
        }

        return ReportExporter::pdf($filename, 'reports.exports.table', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'filters' => $filters,
            'generatedAt' => now()->timezone('America/Santiago'),
        ]);
    }

    /** @return array<string, mixed> */
    private function filterOptions(): array
    {
        return [
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(),
            'costCenters' => $this->costCenterRepository->activeOptions(),
            'drugs' => Drug::query()->where('is_active', true)->orderBy('name')->get(),
            'residents' => Resident::query()->where('is_active', true)->get()->sortBy('last_name')->values(),
            'professionals' => User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->sortBy(fn (User $user) => mb_strtolower($user->display_name))
                ->values(),
            'movementTypes' => MovementType::cases(),
        ];
    }
}
