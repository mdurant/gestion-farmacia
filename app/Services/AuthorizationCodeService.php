<?php

namespace App\Services;

use App\Enums\Permission;
use App\Models\AuthorizationCode;
use App\Models\Drug;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class AuthorizationCodeService
{
    public function issue(
        User $issuer,
        string $purpose,
        ?Drug $drug = null,
        ?int $ttlMinutes = null,
    ): string {
        if (! $issuer->can(Permission::ControlledDrugAuthorize->value)) {
            throw new RuntimeException('No tiene permiso para emitir códigos de autorización.');
        }

        if (! in_array($purpose, [
            AuthorizationCode::PURPOSE_CONTROLLED_DRUG,
            AuthorizationCode::PURPOSE_HIGH_VALUE_WASTE,
        ], true)) {
            throw new InvalidArgumentException('Propósito de autorización no válido.');
        }

        if ($purpose === AuthorizationCode::PURPOSE_CONTROLLED_DRUG && $drug === null) {
            throw new InvalidArgumentException('Debe indicar el fármaco controlado.');
        }

        $ttl = $ttlMinutes ?? (int) config('acalis.authorization.code_ttl_minutes', 15);
        $plain = strtoupper(Str::random(4).'-'.Str::random(8));

        AuthorizationCode::query()->create([
            'purpose' => $purpose,
            'drug_id' => $drug?->id,
            'issued_by' => $issuer->id,
            'code_hash' => $this->hash($plain),
            'expires_at' => now()->addMinutes(max(1, $ttl)),
        ]);

        return $plain;
    }

    public function consume(string $purpose, string $plainCode, User $consumer, ?Drug $drug = null): bool
    {
        $hash = $this->hash($plainCode);

        return DB::transaction(function () use ($purpose, $hash, $consumer, $drug): bool {
            $query = AuthorizationCode::query()
                ->where('purpose', $purpose)
                ->where('code_hash', $hash)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate();

            if ($drug !== null) {
                $query->where(function ($inner) use ($drug): void {
                    $inner->whereNull('drug_id')->orWhere('drug_id', $drug->id);
                });
            }

            /** @var AuthorizationCode|null $code */
            $code = $query->first();

            if ($code === null) {
                return false;
            }

            $code->forceFill([
                'used_by' => $consumer->id,
                'used_at' => now(),
            ])->save();

            return true;
        });
    }

    private function hash(string $plainCode): string
    {
        return hash('sha256', strtoupper(trim($plainCode)));
    }
}
