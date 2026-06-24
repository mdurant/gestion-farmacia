<?php

namespace App\Enums;

enum TreatmentType: string
{
    case Chronic = 'cronico';
    case Sos = 'sos';
    case Temporary = 'temporal';

    public function label(): string
    {
        return match ($this) {
            self::Chronic => 'Crónico',
            self::Sos => 'SOS',
            self::Temporary => 'Temporal',
        };
    }

    public static function fromExcel(?string $value): self
    {
        return match (strtolower(trim((string) $value))) {
            'sos' => self::Sos,
            'temporal' => self::Temporary,
            default => self::Chronic,
        };
    }
}
