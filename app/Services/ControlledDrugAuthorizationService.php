<?php

namespace App\Services;

use App\Enums\Permission;
use App\Events\ControlledDrugAuthorizationRequested;
use App\Exceptions\ControlledDrugAuthorizationRequiredException;
use App\Models\AuthorizationCode;
use App\Models\Batch;
use App\Models\Drug;
use App\Models\User;

class ControlledDrugAuthorizationService
{
    public function __construct(
        private readonly AuthorizationCodeService $authorizationCodes,
    ) {}

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

        if ($this->consumeAuthorizationCode($drug, $user, $authorizationCode)) {
            return;
        }

        ControlledDrugAuthorizationRequested::dispatch($batch, $drug, $user);

        throw new ControlledDrugAuthorizationRequiredException($drug->name);
    }

    public function requiresAuthorization(Drug $drug): bool
    {
        return $drug->is_controlled || $drug->is_narcotic;
    }

    private function consumeAuthorizationCode(Drug $drug, User $user, ?string $authorizationCode): bool
    {
        if ($authorizationCode === null || trim($authorizationCode) === '') {
            return false;
        }

        return $this->authorizationCodes->consume(
            AuthorizationCode::PURPOSE_CONTROLLED_DRUG,
            $authorizationCode,
            $user,
            $drug,
        );
    }
}
