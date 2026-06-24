<?php

namespace App\Enums;

enum ResidentAccessAction: string
{
    case View = 'consulta';
    case Create = 'creacion';
    case Update = 'modificacion';
    case Delete = 'baja';
    case ModuleAccessGranted = 'acceso_modulo_autorizado';
    case ModuleAccessDenied = 'acceso_modulo_denegado';

    public function label(): string
    {
        return match ($this) {
            self::View => 'Consulta / visualización',
            self::Create => 'Creación',
            self::Update => 'Modificación',
            self::Delete => 'Baja lógica',
            self::ModuleAccessGranted => 'Acceso al módulo autorizado',
            self::ModuleAccessDenied => 'Intento de acceso denegado',
        };
    }
}
