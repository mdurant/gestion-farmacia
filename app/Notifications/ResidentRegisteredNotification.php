<?php

namespace App\Notifications;

use App\Models\Resident;
use App\Models\User;
use App\Notifications\Concerns\FormatsRealtimeAlert;
use App\Support\AcalisMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResidentRegisteredNotification extends Notification implements ShouldQueue
{
    use FormatsRealtimeAlert, Queueable;

    public function __construct(
        private readonly Resident $resident,
        private readonly ?User $actor = null,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->resident->loadMissing('costCenter');

        return AcalisMail::notification(
            subject: 'Nuevo residente registrado',
            headline: 'Alta de residente',
            greeting: 'Hola '.$notifiable->display_name,
            intro: 'Se registró un nuevo residente en la residencia. Los datos quedaron disponibles para trazabilidad clínica.',
            details: [
                ['label' => 'Residente', 'value' => $this->resident->full_name],
                ['label' => 'Habitación', 'value' => $this->resident->room_number],
                ['label' => 'Centro de costo', 'value' => $this->resident->costCenter?->name],
                ['label' => 'Registrado por', 'value' => $this->actor?->display_name],
            ],
            actionUrl: route('residents.show', $this->resident),
            actionLabel: 'Ver ficha del residente',
            tone: AcalisMail::TONE_SUCCESS,
            preheader: 'Nuevo residente: '.$this->resident->full_name,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->alertPayload(
            category: 'resident',
            title: 'Nuevo residente',
            message: "{$this->resident->full_name} fue registrado en la residencia.",
            url: route('residents.show', $this->resident),
            severity: 'info',
            extra: [
                'resident_id' => $this->resident->id,
                'actor' => $this->actor?->display_name,
            ],
        );
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return $this->broadcastAlert($this->toArray($notifiable));
    }
}
