<?php

namespace App\Enums;

enum AuditAction: string
{
    case Created = 'creacion';
    case Updated = 'modificacion';
    case Deleted = 'eliminacion';
    case TermsAccepted = 'aceptacion_terminos';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Creación',
            self::Updated => 'Modificación',
            self::Deleted => 'Eliminación',
            self::TermsAccepted => 'Aceptación de términos',
        };
    }
}
