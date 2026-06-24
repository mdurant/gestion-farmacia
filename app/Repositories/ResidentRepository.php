<?php

namespace App\Repositories;

use App\Contracts\Repositories\ResidentRepositoryInterface;
use App\Models\Resident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorImpl;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ResidentRepository implements ResidentRepositoryInterface
{
    public function findOrFail(int $id): Resident
    {
        return Resident::query()->with('costCenter')->findOrFail($id);
    }

    /** @param array<string, mixed> $filters */
    public function listForExport(array $filters = []): Collection
    {
        $residents = $this->baseFilteredQuery($filters)->get();

        if ($search = $filters['search'] ?? null) {
            $needle = mb_strtolower(trim($search));
            $needleRut = $this->normalizeRut($needle);

            return $residents->filter(function (Resident $resident) use ($needle, $needleRut): bool {
                return $this->matchesEncryptedSearch($resident, $needle, $needleRut);
            })->values();
        }

        return $residents;
    }

    /** @param array<string, mixed> $filters */
    private function baseFilteredQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        return Resident::query()
            ->with(['costCenter', 'healthInsurance'])
            ->when($filters['cost_center_id'] ?? null, fn ($q, $id) => $q->where('cost_center_id', $id))
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null, fn ($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->orderByDesc('created_at');
    }

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->baseFilteredQuery($filters);

        if ($search = $filters['search'] ?? null) {
            return $this->paginateWithEncryptedSearch($query->get(), $search, $perPage);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /** @param Collection<int, Resident> $residents */
    private function paginateWithEncryptedSearch(Collection $residents, string $search, int $perPage): LengthAwarePaginator
    {
        $needle = mb_strtolower(trim($search));
        $needleRut = $this->normalizeRut($needle);

        $filtered = $residents->filter(function (Resident $resident) use ($needle, $needleRut): bool {
            return $this->matchesEncryptedSearch($resident, $needle, $needleRut);
        })->values();

        $page = Paginator::resolveCurrentPage();
        $items = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginatorImpl(
            $items,
            $filtered->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    private function matchesEncryptedSearch(Resident $resident, string $needle, string $needleRut): bool
    {
        $text = fn (?string $value): string => mb_strtolower(trim($value ?? ''));

        if ($needleRut !== '' && str_contains($this->normalizeRut($text($resident->rut)), $needleRut)) {
            return true;
        }

        return str_contains($text($resident->full_name), $needle)
            || str_contains($text($resident->first_name), $needle)
            || str_contains($text($resident->last_name), $needle)
            || str_contains($text($resident->room_number), $needle);
    }

    private function normalizeRut(string $value): string
    {
        return preg_replace('/[.\-\s]/', '', mb_strtolower(trim($value))) ?? '';
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Resident
    {
        return Resident::query()->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Resident $resident, array $data): Resident
    {
        $resident->update($data);

        return $resident->fresh(['costCenter']);
    }

    public function delete(Resident $resident): void
    {
        $resident->delete();
    }
}
