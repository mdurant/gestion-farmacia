<?php

namespace Database\Seeders;

use App\Enums\TreatmentType;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\DrugPresentation;
use App\Models\HealthInsurance;
use App\Models\Resident;
use App\Models\ResidentTreatment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TreatmentDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/treatment_database.json');

        if (! is_file($path)) {
            $this->command?->warn('No se encontró treatment_database.json — omitiendo carga clínica.');

            return;
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $defaultCostCenter = CostCenter::query()->firstOrCreate(
            ['code' => 'RES-GEN'],
            ['name' => 'Residencia General', 'floor' => '1', 'pavilion' => 'A'],
        );

        $residentCache = [];
        $drugCache = [];

        foreach ($rows as $row) {
            $residentKey = (string) ($row['Rut'] ?? $row['Residente']);
            $resident = $residentCache[$residentKey] ??= $this->upsertResident($row, $defaultCostCenter->id);
            $drug = $drugCache[$this->drugKey($row)] ??= $this->upsertDrug($row);

            $this->upsertTreatment($resident, $drug, $row);
        }

        $this->command?->info(sprintf(
            'Carga clínica: %d residentes, %d fármacos, %d tratamientos.',
            Resident::query()->count(),
            Drug::query()->count(),
            ResidentTreatment::query()->count(),
        ));
    }

    /** @param  array<string, mixed>  $row */
    private function upsertResident(array $row, int $costCenterId): Resident
    {
        [$firstName, $lastName] = $this->splitFullName((string) $row['Residente']);
        $healthInsuranceId = ClinicalCatalogSeeder::resolveHealthInsuranceId($row['Prevision Salud'] ?? null);

        return Resident::query()->updateOrCreate(
            ['rut' => (string) $row['Rut']],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => $this->parseDate($row['Fecha de Nacimiento'] ?? null),
                'admission_date' => $this->parseDate($row['Fecha Ingreso'] ?? null),
                'cost_center_id' => $costCenterId,
                'health_insurance_id' => $healthInsuranceId,
                'room_number' => (string) ($row['Habitacion'] ?? ''),
                'allergies' => (string) ($row['Alergias'] ?? ''),
                'rescue_service' => (string) ($row['Servicio Rescate'] ?? ''),
                'diagnosis' => (string) ($row['Diagnostico'] ?? ''),
                'is_active' => true,
            ],
        );
    }

    /** @param  array<string, mixed>  $row */
    private function upsertDrug(array $row): Drug
    {
        $name = trim((string) $row['Medicamento']);
        $presentationName = ClinicalCatalogSeeder::normalizePresentationName((string) ($row['Presentacion'] ?? ''));
        $presentationId = DrugPresentation::query()->where('name', $presentationName)->value('id');

        return Drug::query()->updateOrCreate(
            ['name' => $name],
            [
                'code' => 'MED-'.Str::upper(substr(md5($name), 0, 10)),
                'presentation' => $presentationName,
                'drug_presentation_id' => $presentationId,
                'active_ingredient' => $this->extractActiveIngredient($name),
                'category' => 'Tratamiento',
                'min_stock' => 0,
                'is_active' => true,
            ],
        );
    }

    /** @param  array<string, mixed>  $row */
    private function upsertTreatment(Resident $resident, Drug $drug, array $row): void
    {
        $presentationId = ClinicalCatalogSeeder::resolvePresentationId($row['Presentacion'] ?? null);
        $schedule = $this->normalizeTime($row['Horarios'] ?? null);

        ResidentTreatment::query()->updateOrCreate(
            [
                'resident_id' => $resident->id,
                'drug_id' => $drug->id,
                'schedule_time' => $schedule,
                'treatment_type' => TreatmentType::fromExcel($row['Tratamiento'] ?? null)->value,
            ],
            [
                'drug_presentation_id' => $presentationId ?? $drug->drug_presentation_id,
                'daily_dose' => (float) ($row['Dosis Diaria'] ?? 0),
                'monthly_dose' => (float) ($row['Dosis Mensual'] ?? 0),
                'observations' => $row['Observaciones'] ?? null,
                'starts_at' => $this->parseDate($row['Inicio'] ?? null),
                'ends_at' => $this->parseDate($row['Termino'] ?? null),
                'is_active' => true,
            ],
        );
    }

    /** @return array{0: string, 1: string} */
    private function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        if (count($parts) <= 1) {
            return [$fullName, ''];
        }

        if (count($parts) === 2) {
            return [$parts[0], $parts[1]];
        }

        $lastName = array_pop($parts);
        $secondLast = array_pop($parts);

        return [implode(' ', $parts), trim("{$secondLast} {$lastName}")];
    }

    /** @param  array<string, mixed>  $row */
    private function drugKey(array $row): string
    {
        return Str::upper(trim((string) $row['Medicamento']));
    }

    private function extractActiveIngredient(string $name): string
    {
        return trim(strtok($name, '()'));
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
