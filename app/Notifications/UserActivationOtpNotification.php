<?php

namespace App\Notifications;

use App\Support\AcalisMail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserActivationOtpNotification extends Notification
{
    public function __construct(
        private readonly string $otpCode,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = method_exists($notifiable, 'getAttribute')
            ? ($notifiable->display_name ?? $notifiable->name ?? '')
            : '';

        return AcalisMail::otp(
            subject: 'Código de activación de cuenta',
            headline: 'Active su acceso',
            greeting: 'Hola '.$name,
            intro: 'Un administrador registró su cuenta en Acalis Pharma. Use el código de 6 dígitos para completar su alta y definir su contraseña.',
            otpCode: $this->otpCode,
            actionUrl: route('activation.verify.form'),
            footnote: 'Este código expira en '.config('acalis.activation.otp_ttl_minutes', 15).' minutos. Si no solicitó este acceso, ignore el correo.',
        );
    }
}
