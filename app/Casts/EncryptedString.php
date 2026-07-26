<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * String cifrado en reposo. Si el MAC falla (APP_KEY distinta / dato corrupto),
 * no tumba la app: devuelve null y deja traza en log.
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class EncryptedString implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $raw = (string) $value;

        try {
            return Crypt::decryptString($raw);
        } catch (DecryptException $e) {
            // Payload que parece cifrado Laravel pero no abre con la APP_KEY actual.
            if ($this->looksLikeLaravelPayload($raw)) {
                Log::warning('Fallo al descifrar atributo (APP_KEY distinta o dato corrupto).', [
                    'model' => $model::class,
                    'id' => $model->getKey(),
                    'attribute' => $key,
                    'message' => $e->getMessage(),
                ]);

                return null;
            }

            // Legacy en claro (antes del cast encrypted).
            return $raw;
        } catch (Throwable $e) {
            Log::warning('Error inesperado al descifrar atributo.', [
                'model' => $model::class,
                'id' => $model->getKey(),
                'attribute' => $key,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encryptString((string) $value);
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
