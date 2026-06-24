<?php

namespace App\Contracts\Repositories;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog;

    /** @return LengthAwarePaginator<int, AuditLog> */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
