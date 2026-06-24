<?php

namespace App\Enums;

enum MovementType: string
{
    case Entry = 'entrada';
    case ExitAdministration = 'salida_administracion';
    case ExitWaste = 'salida_merma';
    case ExitExpiration = 'salida_vencimiento';
    case Transfer = 'traslado';

    public function label(): string
    {
        return match ($this) {
            self::Entry => 'Entrada',
            self::ExitAdministration => 'Administración a paciente',
            self::ExitWaste => 'Salida por merma',
            self::ExitExpiration => 'Salida por vencimiento',
            self::Transfer => 'Traslado entre bodegas',
        };
    }

    public function isExit(): bool
    {
        return in_array($this, [
            self::ExitAdministration,
            self::ExitWaste,
            self::ExitExpiration,
        ], true);
    }
}
