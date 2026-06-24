<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\Resident;
use App\Services\CatalogExportService;
use App\Support\ReportExporter;
use App\Support\RequestFilters;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CatalogExportController extends Controller
{
    public function __construct(
        private readonly CatalogExportService $catalogExportService,
    ) {}

    public function drugs(Request $request, string $format): Response
    {
        $this->authorize('viewAny', Drug::class);
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        [$headers, $rows, $title] = $this->catalogExportService->drugs([
            'search' => RequestFilters::optionalString($request, 'search'),
            'controlled' => $request->boolean('controlled'),
        ]);

        return $this->respond($format, $title, $headers, $rows);
    }

    public function pharmacies(Request $request, string $format): Response
    {
        $this->authorize('viewAny', Pharmacy::class);
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        [$headers, $rows, $title] = $this->catalogExportService->pharmacies([
            'search' => RequestFilters::optionalString($request, 'search'),
            'type' => RequestFilters::optionalString($request, 'type'),
            'cost_center_id' => RequestFilters::optionalInteger($request, 'cost_center_id'),
            'is_active' => RequestFilters::optionalBoolean($request, 'is_active'),
        ]);

        return $this->respond($format, $title, $headers, $rows);
    }

    public function residents(Request $request, string $format): Response
    {
        $this->authorize('viewAny', Resident::class);
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        [$headers, $rows, $title] = $this->catalogExportService->residents([
            'search' => RequestFilters::optionalString($request, 'search'),
            'cost_center_id' => RequestFilters::optionalInteger($request, 'cost_center_id'),
            'is_active' => RequestFilters::optionalBoolean($request, 'is_active'),
        ]);

        return $this->respond($format, $title, $headers, $rows);
    }

    /** @param list<string> $headers @param list<array<int, mixed>> $rows */
    private function respond(string $format, string $title, array $headers, array $rows): Response
    {
        $filename = str($title)->slug().'-'.now()->format('Y-m-d').'.'.$format;

        if ($format === 'csv') {
            return ReportExporter::csv($filename, $headers, $rows);
        }

        return ReportExporter::pdf($filename, 'reports.exports.table', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'filters' => null,
            'generatedAt' => now()->timezone('America/Santiago'),
        ]);
    }
}
