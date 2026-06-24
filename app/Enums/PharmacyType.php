<?php

namespace App\Enums;

enum PharmacyType: string
{
    case Central = 'bodega_central';
    case FloorKit = 'botiquin_piso';
    case EmergencyModule = 'modulo_emergencia';

    public function label(): string
    {
        return match ($this) {
            self::Central => 'Bodega Central',
            self::FloorKit => 'Botiquín por Piso',
            self::EmergencyModule => 'Módulo de Emergencia',
        };
    }
}
