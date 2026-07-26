<?php

namespace App\Console\Commands;

use App\Support\DemoAccounts;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class RepairEncryptedAttributesCommand extends Command
{
    protected $signature = 'acalis:repair-encryption
        {--dry-run : Solo informar, no escribir}';

    protected $description = 'Repara atributos encrypted ilegibles tras cambio de APP_KEY (usuarios demo / fallback name)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $usersFixed = 0;
        $residentsBroken = 0;

        foreach (DB::table('users')->cursor() as $row) {
            if (! $this->attributeBroken($row->first_name ?? null)) {
                continue;
            }

            $demo = collect(DemoAccounts::seederRecords())
                ->first(fn (array $item): bool => strtolower($item['email']) === strtolower((string) $row->email));

            if ($demo !== null) {
                $first = $demo['first_name'];
                $last = $demo['last_name'];
                $rut = $demo['rut'];
            } else {
                $parts = preg_split('/\s+/', trim((string) $row->name), 2) ?: [];
                $first = $parts[0] ?? 'Usuario';
                $last = $parts[1] ?? (string) $row->id;
                $rut = '00.000.000-0';
            }

            $this->line("Usuario #{$row->id} {$row->email}: re-cifrando desde ".($demo ? 'demo' : 'name'));

            if (! $dry) {
                DB::table('users')->where('id', $row->id)->update([
                    'first_name' => Crypt::encryptString($first),
                    'last_name' => Crypt::encryptString($last),
                    'rut' => Crypt::encryptString($rut),
                    'name' => trim("{$first} {$last}"),
                    'updated_at' => now(),
                ]);
            }

            $usersFixed++;
        }

        foreach (DB::table('residents')->cursor() as $row) {
            $brokenName = $this->attributeBroken($row->first_name ?? null);
            $brokenBirth = $this->attributeBroken($row->birth_date ?? null);
            $brokenAdmission = $this->attributeBroken($row->admission_date ?? null);

            if (! $brokenName && ! $brokenBirth && ! $brokenAdmission) {
                continue;
            }

            $residentsBroken++;
            $this->warn("Residente #{$row->id}: PII/fechas ilegibles — aplicando placeholder con APP_KEY actual.");

            if ($dry) {
                continue;
            }

            $payload = ['updated_at' => now()];

            if ($brokenName) {
                $payload['first_name'] = Crypt::encryptString('Residente');
                $payload['last_name'] = Crypt::encryptString('#'.$row->id);
                $payload['rut'] = Crypt::encryptString('00.000.000-'.($row->id % 10));
                $payload['room_number'] = null;
                $payload['allergies'] = null;
                $payload['rescue_service'] = null;
                $payload['diagnosis'] = null;
                $payload['emergency_contact_name'] = null;
                $payload['emergency_contact_phone'] = null;
                $payload['medical_notes'] = Crypt::encryptString(
                    'PII reiniciada tras rotación de APP_KEY. Reingresar datos clínicos.'
                );
            }

            if ($brokenBirth) {
                $payload['birth_date'] = Crypt::encryptString('1900-01-01');
            }

            if ($brokenAdmission) {
                $payload['admission_date'] = Crypt::encryptString(now()->toDateString());
            }

            // Limpiar otros campos encrypted rotos.
            foreach ([
                'room_number',
                'allergies',
                'rescue_service',
                'diagnosis',
                'emergency_contact_name',
                'emergency_contact_phone',
                'medical_notes',
                'rut',
            ] as $column) {
                if ($this->attributeBroken($row->{$column} ?? null) && ! array_key_exists($column, $payload)) {
                    $payload[$column] = null;
                }
            }

            DB::table('residents')->where('id', $row->id)->update($payload);
        }

        $this->newLine();
        $this->info($dry
            ? "Dry-run: {$usersFixed} usuario(s) y {$residentsBroken} residente(s) afectados."
            : "Reparados {$usersFixed} usuario(s). Placeholders en {$residentsBroken} residente(s).");

        if ($residentsBroken > 0) {
            $this->comment('Sin la APP_KEY anterior no se pueden recuperar nombres clínicos reales; use backup o reingrese datos.');
        }

        return self::SUCCESS;
    }

    private function attributeBroken(?string $raw): bool
    {
        if ($raw === null || $raw === '') {
            return false;
        }

        if (! str_starts_with($raw, 'eyJ')) {
            return false;
        }

        try {
            Crypt::decryptString($raw);

            return false;
        } catch (DecryptException) {
            return true;
        } catch (\Throwable) {
            return true;
        }
    }
}
