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

class UserStatusChangedNotification extends Notification implements ShouldQueue
{
    use FormatsRealtimeAlert, Queueable;

    public function __construct(
        private readonly User $subjectUser,
        private readonly string $action,
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
            subject: 'Cambio de estado de usuario',
            headline: 'Estado de usuario actualizado',
            greeting: 'Hola '.$notifiable->display_name,
            intro: 'Se modificó el estado de acceso de un miembro del personal clínico.',
            details: [
                ['label' => 'Usuario', 'value' => $this->subjectUser->display_name],
                ['label' => 'Correo', 'value' => $this->subjectUser->email],
                ['label' => 'Rol', 'value' => $this->subjectUser->role?->label()],
                ['label' => 'Acción', 'value' => ucfirst($this->actionLabel())],
                ['label' => 'Realizado por', 'value' => $this->actor?->display_name],
            ],
            actionUrl: route('users.show', $this->subjectUser),
            actionLabel: 'Ver ficha del usuario',
            tone: $this->mailTone(),
            preheader: $this->subjectUser->display_name.' fue '.$this->actionLabel(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->alertPayload(
            category: 'user',
            title: 'Estado de usuario',
            message: "{$this->subjectUser->display_name} fue {$this->actionLabel()}.",
            url: route('users.show', $this->subjectUser),
            severity: match ($this->action) {
                'deactivated', 'deleted' => 'warning',
                'activated', 'restored' => 'success',
                default => 'info',
            },
            extra: [
                'user_id' => $this->subjectUser->id,
                'action' => $this->action,
                'actor' => $this->actor?->display_name,
            ],
        );
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return $this->broadcastAlert($this->toArray($notifiable));
    }

    private function actionLabel(): string
    {
        return match ($this->action) {
            'activated' => 'reactivado',
            'deactivated' => 'desactivado',
            'deleted' => 'dado de baja',
            'restored' => 'restaurado',
            default => $this->action,
        };
    }

    private function mailTone(): string
    {
        return match ($this->action) {
            'activated', 'restored' => AcalisMail::TONE_SUCCESS,
            'deactivated', 'deleted' => AcalisMail::TONE_WARNING,
            default => AcalisMail::TONE_INFO,
        };
    }
}
