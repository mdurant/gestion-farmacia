<?php

namespace App\Console\Commands;

use App\Models\AuthorizationCode;
use App\Models\Drug;
use App\Models\User;
use App\Services\AuthorizationCodeService;
use Illuminate\Console\Command;

class IssueAuthorizationCodeCommand extends Command
{
    protected $signature = 'acalis:issue-auth-code
        {email : Correo del emisor (debe tener drugs.controlled.authorize)}
        {--purpose=controlled_drug : controlled_drug|high_value_waste}
        {--drug= : Código del fármaco (requerido para controlled_drug)}
        {--ttl= : Minutos de vigencia}';

    protected $description = 'Emite un código de autorización de un solo uso (controlados o merma alto valor)';

    public function handle(AuthorizationCodeService $codes): int
    {
        $issuer = User::query()->where('email', $this->argument('email'))->first();

        if ($issuer === null) {
            $this->error('Usuario emisor no encontrado.');

            return self::FAILURE;
        }

        $purpose = (string) $this->option('purpose');
        $drug = null;

        if ($purpose === AuthorizationCode::PURPOSE_CONTROLLED_DRUG) {
            $drugCode = $this->option('drug');
            if (! is_string($drugCode) || $drugCode === '') {
                $this->error('Debe indicar --drug=CODIGO para fármacos controlados.');

                return self::FAILURE;
            }

            $drug = Drug::query()->where('code', $drugCode)->first();
            if ($drug === null) {
                $this->error('Fármaco no encontrado.');

                return self::FAILURE;
            }
        }

        try {
            $plain = $codes->issue(
                $issuer,
                $purpose,
                $drug,
                $this->option('ttl') !== null ? (int) $this->option('ttl') : null,
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Código emitido (mostrar una sola vez): '.$plain);

        return self::SUCCESS;
    }
}
