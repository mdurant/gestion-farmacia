<?php

namespace Database\Seeders;

use App\Enums\PharmacyType;
use App\Models\Batch;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\Resident;
use App\Models\User;
use App\Support\DemoAccounts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        User::query()
            ->whereIn('email', DemoAccounts::legacyEmails())
            ->delete();

        foreach (DemoAccounts::seederRecords() as $demo) {
            $user = User::query()->updateOrCreate(
                ['email' => $demo['email']],
                [
                    'name' => "{$demo['first_name']} {$demo['last_name']}",
                    'first_name' => $demo['first_name'],
                    'last_name' => $demo['last_name'],
                    'rut' => $demo['rut'],
                    'password' => Hash::make('password'),
                    'role' => $demo['role'],
                    'is_active' => true,
                    'activated_at' => now(),
                    'email_verified_at' => now(),
                ],
            );
            $user->syncRoles([$demo['role']->value]);
        }

        $costCenterPiso2 = CostCenter::query()->updateOrCreate(
            ['code' => 'PISO-2'],
            [
                'name' => 'Piso 2',
                'floor' => '2',
                'pavilion' => 'B',
                'description' => 'Centro de costos piso 2',
            ],
        );

        $costCenterCritico = CostCenter::query()->updateOrCreate(
            ['code' => 'CRIT-UTI'],
            [
                'name' => 'Paciente Crítico · UTI',
                'floor' => '1',
                'pavilion' => 'C',
                'description' => 'Unidad de tratamiento intensivo',
            ],
        );

        $costCenter = CostCenter::query()->updateOrCreate(
            ['code' => 'PISO-1'],
            [
                'name' => 'Piso 1',
                'floor' => '1',
                'pavilion' => 'A',
                'description' => 'Centro de costos piso 1',
            ],
        );

        $centralPharmacy = Pharmacy::query()->updateOrCreate(
            ['code' => 'BC-001'],
            [
                'name' => 'Bodega Central',
                'type' => PharmacyType::Central,
                'cost_center_id' => $costCenter->id,
            ],
        );

        Pharmacy::query()->updateOrCreate(
            ['code' => 'BP-101'],
            [
                'name' => 'Botiquín Piso 1',
                'type' => PharmacyType::FloorKit,
                'cost_center_id' => $costCenter->id,
            ],
        );

        Pharmacy::query()->updateOrCreate(
            ['code' => 'EMG-001'],
            [
                'name' => 'Módulo Emergencia',
                'type' => PharmacyType::EmergencyModule,
                'cost_center_id' => $costCenterCritico->id,
                'description' => 'Stock de respuesta rápida para urgencias',
            ],
        );

        Pharmacy::query()->updateOrCreate(
            ['code' => 'BP-201'],
            [
                'name' => 'Botiquín Piso 2',
                'type' => PharmacyType::FloorKit,
                'cost_center_id' => $costCenterPiso2->id,
            ],
        );

        $this->call(ClinicalCatalogSeeder::class);

        $paracetamolPresentation = \App\Models\DrugPresentation::query()->where('code', 'COM')->value('id');

        $drug = Drug::query()->updateOrCreate(
            ['code' => 'FAR-001'],
            [
                'name' => 'Paracetamol 500 mg',
                'category' => 'Analgésico',
                'presentation' => 'Comprimido',
                'drug_presentation_id' => $paracetamolPresentation,
                'min_stock' => 50,
                'unit_cost' => 120,
            ],
        );

        Drug::query()->updateOrCreate(
            ['code' => 'FAR-CTRL-001'],
            [
                'name' => 'Morfina 10 mg',
                'category' => 'Opioide',
                'presentation' => 'Ampolla',
                'is_controlled' => true,
                'is_narcotic' => true,
                'min_stock' => 5,
                'unit_cost' => 8500,
            ],
        );

        Batch::query()->updateOrCreate(
            [
                'drug_id' => $drug->id,
                'pharmacy_id' => $centralPharmacy->id,
                'batch_number' => 'L-2026-001',
            ],
            [
                'expiration_date' => now()->addMonths(6),
                'quantity' => 200,
                'unit_cost' => 120,
                'supplier_name' => 'Cenabast',
                'received_at' => now(),
            ],
        );

        $controlled = Drug::query()->where('code', 'FAR-CTRL-001')->first();
        if ($controlled) {
            Batch::query()->updateOrCreate(
                [
                    'drug_id' => $controlled->id,
                    'pharmacy_id' => $centralPharmacy->id,
                    'batch_number' => 'L-CTRL-001',
                ],
                [
                    'expiration_date' => now()->addMonths(12),
                    'quantity' => 20,
                    'unit_cost' => 8500,
                    'supplier_name' => 'Cenabast',
                    'received_at' => now(),
                ],
            );
        }

        $floorPharmacy = Pharmacy::query()->where('code', 'BP-101')->first();
        if ($floorPharmacy && $drug) {
            Batch::query()->updateOrCreate(
                [
                    'drug_id' => $drug->id,
                    'pharmacy_id' => $floorPharmacy->id,
                    'batch_number' => 'L-2026-002',
                ],
                [
                    'expiration_date' => now()->addDays(20),
                    'quantity' => 15,
                    'unit_cost' => 120,
                    'received_at' => now()->subWeek(),
                ],
            );
        }

        Resident::query()->updateOrCreate(
            ['rut' => '12.345.678-9'],
            [
                'first_name' => 'María',
                'last_name' => 'González',
                'birth_date' => '1945-03-15',
                'cost_center_id' => $costCenter->id,
                'room_number' => '101A',
                'emergency_contact_name' => 'Pedro González',
                'emergency_contact_phone' => '+56 9 8765 4321',
            ],
        );

        Resident::query()->updateOrCreate(
            ['rut' => '14.567.890-1'],
            [
                'first_name' => 'Jorge',
                'last_name' => 'Silva',
                'birth_date' => '1938-11-22',
                'cost_center_id' => $costCenterPiso2->id,
                'room_number' => '205A',
            ],
        );

        Resident::query()->updateOrCreate(
            ['rut' => '16.789.012-3'],
            [
                'first_name' => 'Elena',
                'last_name' => 'Rojas',
                'birth_date' => '1950-07-08',
                'cost_center_id' => $costCenterCritico->id,
                'room_number' => 'UTI-1',
                'medical_notes' => 'Paciente crítico · control estricto de medicación',
            ],
        );

        $this->call(TreatmentDatabaseSeeder::class);
        $this->call(ChartDemoSeeder::class);
    }
}