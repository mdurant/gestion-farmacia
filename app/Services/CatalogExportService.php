<?php

namespace App\Services;

use App\Contracts\Repositories\DrugRepositoryInterface;
use App\Contracts\Repositories\PharmacyRepositoryInterface;
use App\Contracts\Repositories\ResidentRepositoryInterface;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\Resident;
use Illuminate\Support\Collection;

class CatalogExportService
{
    public function __construct(
        private readonly DrugRepositoryInterface $drugRepository,
        private readonly PharmacyRepositoryInterface $pharmacyRepository,
        private readonly ResidentRepositoryInterface $residentRepository,
    ) {}

    /** @param array<string, mixed> $filters
     * @return array{0: list<string>, 1: list<array<int, string|int|float|null>>, 2: string}
     */
    public function drugs(array $filters): array
    {
        $drugs = $this->drugRepository->listForExport($filters);

        $rows = $drugs->map(fn (Drug $drug): array => [
            $drug->code,
            $drug->name,
            $drug->category ?? '—',
            $drug->presentation ?? '—',
            $drug->min_stock,
            number_format((float) $drug->unit_cost, 0, ',', '.'),
            ($drug->is_controlled || $drug->is_narcotic) ? 'Sí' : 'No',
            $drug->is_active ? 'Activo' : 'Inactivo',
        ])->all();

        return [
            ['Código', 'Nombre', 'Categoría', 'Presentación', 'Stock mín.', 'Costo ref. CLP', 'Controlado', 'Estado'],
            $rows,
            'Catálogo de fármacos',
        ];
    }

    /** @param array<string, mixed> $filters
     * @return array{0: list<string>, 1: list<array<int, string|int|null>>, 2: string}
     */
    public function pharmacies(array $filters): array
    {
        $pharmacies = $this->pharmacyRepository->listForExport($filters);

        $rows = $pharmacies->map(fn (Pharmacy $pharmacy): array => [
            $pharmacy->code,
            $pharmacy->name,
            $pharmacy->type->label(),
            $pharmacy->costCenter?->name ?? '—',
            $pharmacy->batches_in_stock_count ?? 0,
            $pharmacy->is_active ? 'Activa' : 'Inactiva',
        ])->all();

        return [
            ['Código', 'Nombre', 'Tipo', 'Centro de costo', 'Lotes c/stock', 'Estado'],
            $rows,
            'Bodegas',
        ];
    }

    /** @param array<string, mixed> $filters
     * @return array{0: list<string>, 1: list<array<int, string|null>>, 2: string}
     */
    public function residents(array $filters): array
    {
        /** @var Collection<int, Resident> $residents */
        $residents = $this->residentRepository->listForExport($filters);

        $rows = $residents->map(fn (Resident $resident): array => [
            $resident->full_name,
            $resident->rut ?? '—',
            $resident->room_number ?? '—',
            $resident->costCenter?->name ?? '—',
            $resident->healthInsurance?->name ?? '—',
            $resident->birth_date?->format('d/m/Y') ?? '—',
            $resident->admission_date?->format('d/m/Y') ?? '—',
            $resident->is_active ? 'Activo' : 'Inactivo',
        ])->all();

        return [
            ['Residente', 'RUT', 'Habitación', 'Centro de costo', 'Previsión', 'Nacimiento', 'Ingreso', 'Estado'],
            $rows,
            'Residentes',
        ];
    }
}
