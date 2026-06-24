<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\FormatsRealtimeAlert;
use App\Support\AcalisMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification implements ShouldQueue
{
    use FormatsRealtimeAlert, Queueable;

    public function __construct(
        private readonly User $createdUser,
        private readonly ?User $actor = null,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return AcalisMail::notification(
            subject: 'Nuevo usuario registrado',
            headline: 'Alta de personal',
            greeting: 'Hola '.$notifiable->display_name,
            intro: 'Se registró un nuevo usuario en el sistema con acceso institucional.',
            details: [
                ['label' => 'Nombre', 'value' => $this->createdUser->display_name],
                ['label' => 'Correo', 'value' => $this->createdUser->email],
                ['label' => 'Rol', 'value' => $this->createdUser->role?->label()],
                ['label' => 'Estado', 'value' => $this->createdUser->is_active ? 'Activo' : 'Inactivo'],
                ['label' => 'Registrado por', 'value' => $this->actor?->display_name],
            ],
            actionUrl: route('users.show', $this->createdUser),
            actionLabel: 'Ver ficha del usuario',
            tone: AcalisMail::TONE_INFO,
            preheader: 'Nuevo usuario: '.$this->createdUser->display_name,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->alertPayload(
            category: 'user',
            title: 'Nuevo usuario',
            message: "{$this->createdUser->display_name} fue dado de alta como {$this->createdUser->role?->label()}.",
            url: route('users.show', $this->createdUser),
            severity: 'info',
            extra: [
                'user_id' => $this->createdUser->id,
                'actor' => $this->actor?->display_name,
            ],
        );
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return $this->broadcastAlert($this->toArray($notifiable));
    }
}
