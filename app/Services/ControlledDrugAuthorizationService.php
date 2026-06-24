<?php

namespace App\Services;

use App\Enums\Permission;
use App\Events\ControlledDrugAuthorizationRequested;
use App\Exceptions\ControlledDrugAuthorizationRequiredException;
use App\Models\Batch;
use App\Models\Drug;
use App\Models\User;

class ControlledDrugAuthorizationService
{
    public function assertMovementAllowed(Batch $batch, User $user, ?string $authorizationCode = null): void
    {
        $batch->loadMissing('drug');
        $drug = $batch->drug;

        if ($drug === null || ! $this->requiresAuthorization($drug)) {
            return;
        }

        if ($user->can(Permission::ControlledDrugAuthorize->value)) {
            return;
        }

        if ($this->hasValidAuthorizationCode($drug, $authorizationCode)) {
            return;
        }

        ControlledDrugAuthorizationRequested::dispatch($batch, $drug, $user);

        throw new ControlledDrugAuthorizationRequiredException($drug->name);
    }

    public function requiresAuthorization(Drug $drug): bool
    {
        return $drug->is_controlled || $drug->is_narcotic;
    }

    private function hasValidAuthorizationCode(Drug $drug, ?string $authorizationCode): bool
    {
        if ($authorizationCode === null || $authorizationCode === '') {
            return false;
        }

        $expectedPrefix = strtoupper(substr($drug->code, 0, 3));

        return str_starts_with(strtoupper($authorizationCode), $expectedPrefix)
            && strlen($authorizationCode) >= 8;
    }
}
