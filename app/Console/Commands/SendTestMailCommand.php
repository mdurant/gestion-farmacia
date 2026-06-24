<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestMailCommand extends Command
{
    protected $signature = 'acalis:mail-test {email? : Destinatario de prueba (por defecto: cuenta de notificaciones)}';

    protected $description = 'Envía un correo de prueba usando la configuración Gmail de la plataforma';

    public function handle(): int
    {
        $recipient = (string) ($this->argument('email') ?: config('acalis.mail.notifications_address'));

        if ($recipient === '') {
            $this->error('Configure ACALIS_EMAIL_NOTIFICATIONS en .env o indique un destinatario.');

            return self::FAILURE;
        }

        $from = (string) config('mail.from.address');

        try {
            Mail::raw(
                'Este es un correo de prueba enviado desde Acalis Pharma Web mediante Gmail SMTP.',
                function ($message) use ($recipient, $from): void {
                    $message->to($recipient)
                        ->subject('Prueba de correo — Acalis Pharma')
                        ->from($from, (string) config('mail.from.name'));
                },
            );
        } catch (\Throwable $exception) {
            $this->error('No se pudo enviar el correo: '.$exception->getMessage());

            if (str_contains($exception->getMessage(), '535')) {
                $this->newLine();
                $this->warn('Gmail rechazó usuario o contraseña (código 535). La configuración SMTP de Laravel es correcta; el problema está en las credenciales de Google.');
                $this->line('  1. Active verificación en 2 pasos en la cuenta Google.');
                $this->line('  2. Genere una contraseña de aplicación en: https://myaccount.google.com/apppasswords');
                $this->line('  3. Use esa contraseña de 16 caracteres en ACALIS_EMAIL_NOTIFICATIONS_PASSWORD (no la contraseña normal de Gmail).');
                $this->line('  4. Ejecute: php artisan config:clear && php artisan acalis:mail-test');
            }

            return self::FAILURE;
        }

        $this->info("Correo de prueba enviado a {$recipient} desde {$from}.");

        return self::SUCCESS;
    }
}
