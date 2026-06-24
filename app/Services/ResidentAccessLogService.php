<?php

namespace App\Services;

use App\Enums\ResidentAccessAction;
use App\Models\Resident;
use App\Models\ResidentAccessLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ResidentAccessLogService
{
    /** @param  array<string, mixed>|null  $oldValues */
    /** @param  array<string, mixed>|null  $newValues */
    public function log(
        Resident $resident,
        ResidentAccessAction $action,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): void {
        $this->persistLog($resident->id, $action, $oldValues, $newValues);
    }

    /** @param  array<string, mixed>|null  $metadata */
    public function logModuleAccess(ResidentAccessAction $action, ?array $metadata = null): void
    {
        $this->persistLog(null, $action, null, $metadata);
    }

    /** @param  array<string, mixed>|null  $oldValues */
    /** @param  array<string, mixed>|null  $newValues */
    private function persistLog(
        ?int $residentId,
        ResidentAccessAction $action,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        ResidentAccessLog::query()->create([
            'user_id' => Auth::id(),
            'resident_id' => $residentId,
            'action' => $action,
            'old_values' => $oldValues ? $this->sanitize($oldValues) : null,
            'new_values' => $newValues ? $this->sanitize($newValues) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'browser' => $this->detectBrowser(Request::userAgent()),
            'accessed_at' => now(),
        ]);
    }

    /** @return array<string, mixed> */
    public function snapshot(Resident $resident): array
    {
        return $this->sanitize($resident->getAttributes());
    }

    /** @param  array<string, mixed>  $values */
    private function sanitize(array $values): array
    {
        unset($values['password'], $values['remember_token']);

        return $values;
    }

    private function detectBrowser(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Microsoft Edge',
            str_contains($userAgent, 'Chrome/') && ! str_contains($userAgent, 'Edg/') => 'Google Chrome',
            str_contains($userAgent, 'Firefox/') => 'Mozilla Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome/') => 'Safari',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            default => 'Otro navegador',
        };
    }
}
