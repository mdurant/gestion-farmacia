<?php

namespace Database\Seeders;

use App\Enums\MovementType;
use App\Enums\PharmacyType;
use App\Models\Batch;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Resident;
use App\Models\SystemAlert;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ChartDemoSeeder extends Seeder
{
    /** @var list<array{code: string, name: string, category: string, min_stock: int, max_stock: int, unit_cost: float, stock?: int, is_controlled?: bool}> */
    private array $demoDrugs = [
        ['code' => 'FAR-002', 'name' => 'Losartán 50 mg', 'category' => 'Cardiovascular', 'min_stock' => 80, 'max_stock' => 400, 'unit_cost' => 85, 'stock' => 220],
        ['code' => 'FAR-003', 'name' => 'Metformina 850 mg', 'category' => 'Diabetes', 'min_stock' => 100, 'max_stock' => 500, 'unit_cost' => 45, 'stock' => 310],
        ['code' => 'FAR-004', 'name' => 'Omeprazol 20 mg', 'category' => 'Gastrointestinal', 'min_stock' => 60, 'max_stock' => 300, 'unit_cost' => 55, 'stock' => 145],
        ['code' => 'FAR-005', 'name' => 'Aspirina 100 mg', 'category' => 'Cardiovascular', 'min_stock' => 120, 'max_stock' => 600, 'unit_cost' => 25, 'stock' => 480],
        ['code' => 'FAR-006', 'name' => 'Enalapril 10 mg', 'category' => 'Cardiovascular', 'min_stock' => 70, 'max_stock' => 350, 'unit_cost' => 65, 'stock' => 190],
        ['code' => 'FAR-007', 'name' => 'Amoxicilina 500 mg', 'category' => 'Antibiótico', 'min_stock' => 40, 'max_stock' => 200, 'unit_cost' => 180, 'stock' => 8],
        ['code' => 'FAR-008', 'name' => 'Insulina NPH 100 UI/ml', 'category' => 'Diabetes', 'min_stock' => 15, 'max_stock' => 60, 'unit_cost' => 4200, 'stock' => 4],
        ['code' => 'FAR-009', 'name' => 'Furosemida 40 mg', 'category' => 'Diurético', 'min_stock' => 50, 'max_stock' => 250, 'unit_cost' => 35, 'stock' => 95],
        ['code' => 'FAR-010', 'name' => 'Haloperidol 5 mg', 'category' => 'Psicotrópico', 'min_stock' => 20, 'max_stock' => 80, 'unit_cost' => 320, 'stock' => 42, 'is_controlled' => true],
    ];

    public function run(): void
    {
        if (! Pharmacy::query()->exists()) {
            $this->command?->warn('ChartDemoSeeder requiere datos base. Ejecuta DatabaseSeeder primero.');

            return;
        }

        $context = $this->bootstrapContext();
        $this->seedExtraDrugs($context);
        $this->seedExtraResidents($context);
        $this->seedBatches($context);
        $this->seedHistoricalMovements($context);
        $this->seedSystemAlerts($context);

        $this->command?->info(sprintf(
            'ChartDemoSeeder: %d movimientos demo (últimos 6 meses).',
            InventoryMovement::query()->count(),
        ));
    }

    /** @return array{users: Collection<int, User>, pharmacies: Collection<int, Pharmacy>, residents: Collection<int, Resident>, drugs: Collection<int, Drug>, batches: Collection<int, Batch>, costCenters: Collection<int, CostCenter>} */
    private function bootstrapContext(): array
    {
        return [
            'users' => User::query()->where('is_active', true)->get(),
            'pharmacies' => Pharmacy::query()->with('costCenter')->get(),
            'residents' => Resident::query()->where('is_active', true)->get(),
            'drugs' => Drug::query()->where('is_active', true)->get(),
            'batches' => Batch::query()->with(['drug', 'pharmacy'])->get(),
            'costCenters' => CostCenter::query()->get(),
        ];
    }

    /** @param array<string, mixed> $context */
    private function seedExtraDrugs(array &$context): void
    {
        foreach ($this->demoDrugs as $definition) {
            Drug::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'category' => $definition['category'],
                    'presentation' => 'Comprimido',
                    'min_stock' => $definition['min_stock'],
                    'max_stock' => $definition['max_stock'],
                    'unit_cost' => $definition['unit_cost'],
                    'is_controlled' => $definition['is_controlled'] ?? false,
                    'is_active' => true,
                ],
            );
        }

        $context['drugs'] = Drug::query()->where('is_active', true)->get();
    }

    /** @param array<string, mixed> $context */
    private function seedExtraResidents(array &$context): void
    {
        $templates = [
            ['rut' => '18.234.567-8', 'first_name' => 'Rosa', 'last_name' => 'Muñoz', 'room' => '102B', 'code' => 'PISO-1'],
            ['rut' => '19.345.678-9', 'first_name' => 'Alberto', 'last_name' => 'Pérez', 'room' => '103A', 'code' => 'PISO-1'],
            ['rut' => '20.456.789-0', 'first_name' => 'Carmen', 'last_name' => 'Vega', 'room' => '206B', 'code' => 'PISO-2'],
            ['rut' => '21.567.890-1', 'first_name' => 'Héctor', 'last_name' => 'Díaz', 'room' => '207A', 'code' => 'PISO-2'],
            ['rut' => '22.678.901-2', 'first_name' => 'Lucía', 'last_name' => 'Torres', 'room' => 'UTI-2', 'code' => 'CRIT-UTI'],
        ];

        foreach ($templates as $template) {
            $costCenter = $context['costCenters']->firstWhere('code', $template['code']);
            if ($costCenter === null) {
                continue;
            }

            Resident::query()->updateOrCreate(
                ['rut' => $template['rut']],
                [
                    'first_name' => $template['first_name'],
                    'last_name' => $template['last_name'],
                    'birth_date' => fake()->dateTimeBetween('-95 years', '-70 years')->format('Y-m-d'),
                    'cost_center_id' => $costCenter->id,
                    'room_number' => $template['room'],
                    'is_active' => true,
                ],
            );
        }

        $context['residents'] = Resident::query()->where('is_active', true)->get();
    }

    /** @param array<string, mixed> $context */
    private function seedBatches(array &$context): void
    {
        /** @var Pharmacy|null $central */
        $central = $context['pharmacies']->first(fn (Pharmacy $p) => $p->type === PharmacyType::Central);
        if ($central === null) {
            return;
        }

        foreach ($this->demoDrugs as $definition) {
            $drug = $context['drugs']->firstWhere('code', $definition['code']);
            if ($drug === null) {
                continue;
            }

            Batch::query()->updateOrCreate(
                [
                    'drug_id' => $drug->id,
                    'pharmacy_id' => $central->id,
                    'batch_number' => 'DEMO-'.$definition['code'],
                ],
                [
                    'expiration_date' => now()->addMonths(random_int(3, 14)),
                    'quantity' => $definition['stock'],
                    'unit_cost' => $definition['unit_cost'],
                    'supplier_name' => 'Cenabast',
                    'received_at' => now()->subMonths(2),
                ],
            );
        }

        /** @var Pharmacy|null $floorKit */
        $floorKit = $context['pharmacies']->first(fn (Pharmacy $p) => $p->type === PharmacyType::FloorKit);
        $paracetamol = $context['drugs']->firstWhere('code', 'FAR-001');

        if ($floorKit !== null && $paracetamol !== null) {
            Batch::query()->updateOrCreate(
                [
                    'drug_id' => $paracetamol->id,
                    'pharmacy_id' => $floorKit->id,
                    'batch_number' => 'DEMO-FLOOR-001',
                ],
                [
                    'expiration_date' => now()->addDays(18),
                    'quantity' => 12,
                    'unit_cost' => 120,
                    'received_at' => now()->subWeeks(3),
                ],
            );
        }

        $context['batches'] = Batch::query()->with(['drug', 'pharmacy'])->where('quantity', '>', 0)->get();
    }

    /** @param array<string, mixed> $context */
    private function seedHistoricalMovements(array $context): void
    {
        /** @var Collection<int, User> $users */
        $users = $context['users'];
        /** @var Collection<int, Pharmacy> $pharmacies */
        $pharmacies = $context['pharmacies'];
        /** @var Collection<int, Resident> $residents */
        $residents = $context['residents'];
        /** @var Collection<int, Batch> $batches */
        $batches = $context['batches'];

        if ($users->isEmpty() || $pharmacies->isEmpty() || $batches->isEmpty()) {
            return;
        }

        $wasteMultipliers = [0.6, 0.9, 1.4, 1.8, 1.1, 0.8, 1.0];
        $rows = [];
        $now = now();

        for ($monthOffset = 6; $monthOffset >= 0; $monthOffset--) {
            $monthStart = $now->copy()->subMonths($monthOffset)->startOfMonth();
            $monthEnd = $monthOffset === 0 ? $now->copy() : $monthStart->copy()->endOfMonth();
            $wasteFactor = $wasteMultipliers[6 - $monthOffset] ?? 1.0;

            $adminCount = $monthOffset === 0 ? 18 : random_int(35, 55);
            $entryCount = random_int(8, 14);
            $wasteCount = (int) max(2, round(random_int(4, 9) * $wasteFactor));
            $expirationCount = random_int(1, 4);
            $transferCount = random_int(4, 10);

            for ($i = 0; $i < $adminCount; $i++) {
                $rows[] = $this->buildMovementRow(
                    MovementType::ExitAdministration,
                    $this->randomBatch($batches),
                    $users->random(),
                    $this->randomDateBetween($monthStart, $monthEnd),
                    $residents->random(),
                );
            }

            for ($i = 0; $i < $entryCount; $i++) {
                $rows[] = $this->buildMovementRow(
                    MovementType::Entry,
                    $this->randomBatch($batches),
                    $users->random(),
                    $this->randomDateBetween($monthStart, $monthEnd),
                );
            }

            for ($i = 0; $i < $wasteCount; $i++) {
                $batch = $this->randomBatch($batches);
                if ($batch->drug?->code === 'FAR-CTRL-001' && $i === 0) {
                    $batch = $batches->firstWhere('drug.code', 'FAR-CTRL-001') ?? $batch;
                }

                $rows[] = $this->buildMovementRow(
                    MovementType::ExitWaste,
                    $batch,
                    $users->random(),
                    $this->randomDateBetween($monthStart, $monthEnd),
                    quantityOverride: $batch->drug?->code === 'FAR-CTRL-001' ? random_int(6, 10) : null,
                );
            }

            for ($i = 0; $i < $expirationCount; $i++) {
                $rows[] = $this->buildMovementRow(
                    MovementType::ExitExpiration,
                    $this->randomBatch($batches),
                    $users->random(),
                    $this->randomDateBetween($monthStart, $monthEnd),
                );
            }

            for ($i = 0; $i < $transferCount; $i++) {
                $source = $this->randomBatch($batches);
                $destination = $pharmacies->firstWhere('id', '!=', $source->pharmacy_id) ?? $pharmacies->random();

                $rows[] = $this->buildMovementRow(
                    MovementType::Transfer,
                    $source,
                    $users->random(),
                    $this->randomDateBetween($monthStart, $monthEnd),
                    destinationPharmacyId: $destination->id,
                );
            }
        }

        InventoryMovement::withoutEvents(function () use ($rows): void {
            foreach (array_chunk($rows, 150) as $chunk) {
                InventoryMovement::query()->insert($chunk);
            }
        });
    }

    /** @param array<string, mixed> $context */
    private function seedSystemAlerts(array $context): void
    {
        /** @var Collection<int, Batch> $batches */
        $batches = $context['batches'];

        $criticalBatch = $batches->first(fn (Batch $b) => $b->drug?->code === 'FAR-008');
        if ($criticalBatch !== null && $criticalBatch->drug !== null) {
            SystemAlert::query()->updateOrCreate(
                [
                    'type' => 'low_stock',
                    'drug_id' => $criticalBatch->drug_id,
                    'batch_id' => $criticalBatch->id,
                    'pharmacy_id' => $criticalBatch->pharmacy_id,
                ],
                [
                    'severity' => 'error',
                    'title' => 'Stock crítico',
                    'message' => "El fármaco {$criticalBatch->drug->name} está bajo el mínimo ({$criticalBatch->quantity}/{$criticalBatch->drug->min_stock}).",
                    'metadata' => [
                        'current_stock' => $criticalBatch->quantity,
                        'min_stock' => $criticalBatch->drug->min_stock,
                    ],
                    'read_at' => null,
                ],
            );
        }

        $expiringBatch = $batches->first(fn (Batch $b) => $b->batch_number === 'DEMO-FLOOR-001')
            ?? $batches->first(fn (Batch $b) => $b->batch_number === 'L-2026-002');

        if ($expiringBatch !== null && $expiringBatch->drug !== null) {
            SystemAlert::query()->updateOrCreate(
                [
                    'type' => 'expiring_soon',
                    'drug_id' => $expiringBatch->drug_id,
                    'batch_id' => $expiringBatch->id,
                    'pharmacy_id' => $expiringBatch->pharmacy_id,
                ],
                [
                    'severity' => 'warning',
                    'title' => 'Vencimiento próximo',
                    'message' => "El lote {$expiringBatch->batch_number} de {$expiringBatch->drug->name} vence el {$expiringBatch->expiration_date->format('d/m/Y')}.",
                    'metadata' => [
                        'expiration_date' => $expiringBatch->expiration_date->toDateString(),
                    ],
                    'read_at' => null,
                ],
            );
        }

        $lowAntibiotic = $batches->first(fn (Batch $b) => $b->drug?->code === 'FAR-007');
        if ($lowAntibiotic !== null && $lowAntibiotic->drug !== null) {
            SystemAlert::query()->updateOrCreate(
                [
                    'type' => 'low_stock',
                    'drug_id' => $lowAntibiotic->drug_id,
                    'batch_id' => $lowAntibiotic->id,
                    'pharmacy_id' => $lowAntibiotic->pharmacy_id,
                ],
                [
                    'severity' => 'error',
                    'title' => 'Stock crítico',
                    'message' => "El fármaco {$lowAntibiotic->drug->name} requiere reposición urgente.",
                    'metadata' => ['current_stock' => $lowAntibiotic->quantity],
                    'read_at' => null,
                ],
            );
        }
    }

    /** @param Collection<int, Batch> $batches */
    private function randomBatch(Collection $batches): Batch
    {
        return $batches->random();
    }

    private function randomDateBetween(Carbon $from, Carbon $to): Carbon
    {
        if ($from->greaterThanOrEqualTo($to)) {
            return $to->copy();
        }

        $seconds = $to->getTimestamp() - $from->getTimestamp();

        return $from->copy()->addSeconds(random_int(0, max(1, $seconds)));
    }

    /** @return array<string, mixed> */
    private function buildMovementRow(
        MovementType $type,
        Batch $batch,
        User $user,
        Carbon $movementAt,
        ?Resident $resident = null,
        ?int $destinationPharmacyId = null,
        ?int $quantityOverride = null,
    ): array {
        $drug = $batch->drug;
        $pharmacy = $batch->pharmacy;
        $unitCost = (float) $batch->unit_cost;
        $quantity = $quantityOverride ?? match ($type) {
            MovementType::Entry => random_int(20, 120),
            MovementType::ExitAdministration => random_int(1, 4),
            MovementType::ExitWaste => random_int(1, 8),
            MovementType::ExitExpiration => random_int(2, 15),
            MovementType::Transfer => random_int(5, 25),
        };

        $reason = match ($type) {
            MovementType::Entry => 'Entrada demo · Cenabast',
            MovementType::ExitAdministration => 'Administración a residente',
            MovementType::ExitWaste => 'Salida por merma',
            MovementType::ExitExpiration => 'Salida por vencimiento',
            MovementType::Transfer => 'Traslado entre bodegas',
        };

        $timestamp = $movementAt->format('Y-m-d H:i:s');

        return [
            'movement_type' => $type->value,
            'pharmacy_id' => $pharmacy?->id ?? $batch->pharmacy_id,
            'destination_pharmacy_id' => $type === MovementType::Transfer ? $destinationPharmacyId : null,
            'batch_id' => $batch->id,
            'drug_id' => $batch->drug_id,
            'cost_center_id' => $pharmacy?->cost_center_id ?? $batch->pharmacy?->cost_center_id,
            'resident_id' => $type === MovementType::ExitAdministration ? $resident?->id : null,
            'user_id' => $user->id,
            'prescription_id' => $type === MovementType::ExitAdministration ? 'RX-DEMO-'.random_int(1000, 9999) : null,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_value' => round($unitCost * $quantity, 2),
            'reason' => $reason,
            'notes' => 'Dato demo para gráficos',
            'movement_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}
