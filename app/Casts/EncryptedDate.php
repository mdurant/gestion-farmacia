<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Fecha clínica cifrada en reposo, con compatibilidad de lectura para valores legacy en claro.
 *
 * @implements CastsAttributes<Carbon|null, string|null>
 */
class EncryptedDate implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $plain = $this->decryptOrLegacy((string) $value, $model, $key);

        if ($plain === null || $plain === '') {
            return null;
        }

        try {
            return Carbon::parse($plain)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->toDateString()
            : Carbon::parse((string) $value)->toDateString();

        return Crypt::encryptString($date);
    }

    private function decryptOrLegacy(string $value, Model $model, string $key): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            if ($this->looksLikeLaravelPayload($value)) {
                Log::warning('Fallo al descifrar fecha (APP_KEY distinta o dato corrupto).', [
                    'model' => $model::class,
                    'id' => $model->getKey(),
                    'attribute' => $key,
                    'message' => $e->getMessage(),
                ]);

                return null;
            }

            // Legacy en claro (Y-m-d o datetime).
            return $value;
        } catch (Throwable $e) {
            Log::warning('Error inesperado al descifrar fecha.', [
                'model' => $model::class,
                'id' => $model->getKey(),
                'attribute' => $key,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function looksLikeLaravelPayload(string $value): bool
    {
        if (! str_starts_with($value, 'eyJ')) {
            return false;
        }

        $decoded = json_decode(base64_decode($value, true) ?: '', true);

        return is_array($decoded) && isset($decoded['iv'], $decoded['value'], $decoded['mac']);
    }
}
