<?php

namespace App\Contracts\Services;

use App\DTOs\Reports\ReportFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ReportServiceInterface
{
    public function filtersFromRequest(\Illuminate\Http\Request $request): ReportFilters;

    /** @return LengthAwarePaginator<int, \App\Models\InventoryMovement> */
    public function kardex(ReportFilters $filters, int $perPage = 25): LengthAwarePaginator;

    /** @return Collection<int, object> */
    public function residentConsumption(ReportFilters $filters): Collection;

    /** @return array<string, mixed> */
    public function valuation(ReportFilters $filters): array;

    /** @return Collection<int, object> */
    public function monthlyWaste(ReportFilters $filters): Collection;

    /** @return Collection<int, object> */
    public function purchaseProjection(ReportFilters $filters): Collection;

    /** @return list<array<string, mixed>> */
    public function kardexRows(ReportFilters $filters): array;

    /** @return list<array<string, mixed>> */
    public function residentConsumptionRows(ReportFilters $filters): array;

    /** @return list<array<string, mixed>> */
    public function valuationRows(ReportFilters $filters): array;

    /** @return list<array<string, mixed>> */
    public function monthlyWasteRows(ReportFilters $filters): array;

    /** @return list<array<string, mixed>> */
    public function purchaseProjectionRows(ReportFilters $filters): array;
}
