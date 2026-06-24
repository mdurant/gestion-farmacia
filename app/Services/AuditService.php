<?php

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /** @param  array<string, mixed>|null  $oldValues */
    public function logModelEvent(Model $model, string $action, ?array $oldValues = null): void
    {
        $auditAction = AuditAction::tryFrom($action) ?? AuditAction::Updated;

        $this->auditLogRepository->create([
            'user_id' => Auth::id(),
            'action' => $auditAction->value,
            'table_name' => $model->getTable(),
            'row_id' => (int) $model->getKey(),
            'old_values' => $oldValues ? $this->sanitizeValues($oldValues) : null,
            'new_values' => $this->sanitizeValues($model->getAttributes()),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /** @param  array<string, mixed>  $values */
    private function sanitizeValues(array $values): array
    {
        unset($values['password'], $values['remember_token']);

        return $values;
    }
}
