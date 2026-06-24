<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case MedicalDirector = 'director_medico';
    case HeadNurse = 'enfermero_jefe';
    case NursingTechnician = 'tens';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::MedicalDirector => 'Director Médico',
            self::HeadNurse => 'Enfermero Jefe',
            self::NursingTechnician => 'TENS',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
