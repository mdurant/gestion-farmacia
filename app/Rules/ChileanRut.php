<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ChileanRut implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->isValid($value)) {
            $fail('El RUT ingresado no es válido.');
        }
    }

    private function isValid(string $rut): bool
    {
        $normalized = strtoupper(preg_replace('/[.\-\s]/', '', $rut) ?? '');

        if (! preg_match('/^(\d{7,8})([0-9K])$/', $normalized, $matches)) {
            return false;
        }

        $body = $matches[1];
        $verifier = $matches[2];
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($body) - 1; $i >= 0; $i--) {
            $sum += (int) $body[$i] * $multiplier;
            $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
        }

        $expected = 11 - ($sum % 11);
        $calculated = match ($expected) {
            11 => '0',
            10 => 'K',
            default => (string) $expected,
        };

        return $verifier === $calculated;
    }
}
