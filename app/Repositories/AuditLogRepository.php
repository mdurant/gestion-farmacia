<?php

namespace App\Repositories;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog
    {
        return AuditLog::query()->create($data);
    }

    /** @return LengthAwarePaginator<int, AuditLog> */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with('user')
            ->when($filters['table_name'] ?? null, fn ($q, $table) => $q->where('table_name', $table))
            ->when($filters['action'] ?? null, fn ($q, $action) => $q->where('action', $action))
            ->when($filters['row_id'] ?? null, fn ($q, $rowId) => $q->where('row_id', $rowId))
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
