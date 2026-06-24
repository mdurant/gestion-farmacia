<?php

namespace App\Notifications;

use App\Models\Batch;
use App\Models\Drug;
use App\Models\User;
use App\Notifications\Concerns\FormatsRealtimeAlert;
use App\Support\AcalisMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ControlledDrugAuthorizationNotification extends Notification implements ShouldQueue
{
    use FormatsRealtimeAlert, Queueable;

    public function __construct(
        private readonly Batch $batch,
        private readonly Drug $drug,
        private readonly User $requestedBy,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return AcalisMail::notification(
            subject: 'Autorización pendiente — fármaco controlado',
            headline: 'Autorización requerida',
            greeting: 'Atención '.$notifiable->display_name,
            intro: 'Un profesional intentó registrar un movimiento de fármaco controlado sin la autorización correspondiente.',
            details: [
                ['label' => 'Fármaco', 'value' => $this->drug->name],
                ['label' => 'Código', 'value' => $this->drug->code],
                ['label' => 'Lote', 'value' => $this->batch->batch_number],
                ['label' => 'Solicitante', 'value' => $this->requestedBy->display_name],
                ['label' => 'Rol', 'value' => $this->requestedBy->role?->label()],
            ],
            actionUrl: route('inventory.index'),
            actionLabel: 'Revisar inventario',
            tone: AcalisMail::TONE_ERROR,
            footnote: 'Solo personal autorizado puede mover fármacos controlados o narcóticos.',
            preheader: 'Autorización pendiente para '.$this->drug->name,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->alertPayload(
            category: 'controlled_drug',
            title: 'Autorización requerida',
            message: "{$this->requestedBy->display_name} solicitó mover {$this->drug->name} (controlado).",
            url: route('inventory.index'),
            severity: 'error',
            extra: [
                'drug_id' => $this->drug->id,
                'batch_id' => $this->batch->id,
                'requested_by' => $this->requestedBy->id,
            ],
        );
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return $this->broadcastAlert($this->toArray($notifiable));
    }
}
