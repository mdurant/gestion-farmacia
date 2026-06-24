<?php

namespace Database\Seeders;

use App\Models\DrugPresentation;
use App\Models\HealthInsurance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClinicalCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $presentations = [
            ['code' => 'APL', 'name' => 'Aplicación'],
            ['code' => 'CAP', 'name' => 'Cápsula'],
            ['code' => 'COM', 'name' => 'Comprimido'],
            ['code' => 'ENJ', 'name' => 'Enjuague bucal'],
            ['code' => 'GOT', 'name' => 'Gotas'],
            ['code' => 'GR', 'name' => 'Gramo'],
            ['code' => 'INH', 'name' => 'Inhalaciones'],
            ['code' => 'ML', 'name' => 'Mililitro'],
            ['code' => 'PAR', 'name' => 'Parche'],
            ['code' => 'PUF', 'name' => 'Puff'],
            ['code' => 'SOB', 'name' => 'Sobre'],
            ['code' => 'SPR', 'name' => 'Spray'],
            ['code' => 'UI', 'name' => 'UI'],
        ];

        foreach ($presentations as $presentation) {
            DrugPresentation::query()->updateOrCreate(
                ['code' => $presentation['code']],
                ['name' => $presentation['name'], 'is_active' => true],
            );
        }

        $insurances = [
            ['code' => 'FONASA', 'name' => 'Fonasa'],
            ['code' => 'IPS', 'name' => 'IPS'],
            ['code' => 'ISAPRE', 'name' => 'Isapre'],
            ['code' => 'DIPRECA', 'name' => 'Dipreca'],
            ['code' => 'SP', 'name' => 'SP'],
            ['code' => 'OTRO', 'name' => 'Otro'],
        ];

        foreach ($insurances as $insurance) {
            HealthInsurance::query()->updateOrCreate(
                ['code' => $insurance['code']],
                ['name' => $insurance['name'], 'is_active' => true],
            );
        }
    }

    public static function resolvePresentationId(?string $raw): ?int
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $normalized = self::normalizePresentationName($raw);

        return DrugPresentation::query()->where('name', $normalized)->value('id');
    }

    public static function resolveHealthInsuranceId(?string $raw): ?int
    {
        if ($raw === null || trim($raw) === '') {
            return HealthInsurance::query()->where('code', 'OTRO')->value('id');
        }

        $name = trim($raw);

        return HealthInsurance::query()
            ->where('name', $name)
            ->orWhere('code', Str::upper($name))
            ->value('id');
    }

    public static function normalizePresentationName(string $raw): string
    {
        $key = Str::lower(trim($raw));

        return match ($key) {
            'aplicación', 'aplicacion' => 'Aplicación',
            'capsula', 'cápsula' => 'Cápsula',
            'comprimido' => 'Comprimido',
            'gota', 'gotas' => 'Gotas',
            'gramo', 'gr' => 'Gramo',
            'ml', '1ml', 'mililitro' => 'Mililitro',
            'ui' => 'UI',
            default => Str::title(trim($raw)),
        };
    }
}
