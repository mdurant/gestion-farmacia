<?php

namespace App\Notifications;

use App\Models\InventoryMovement;
use App\Notifications\Concerns\FormatsRealtimeAlert;
use App\Support\AcalisMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HighValueWasteAlert extends Notification implements ShouldQueue
{
    use FormatsRealtimeAlert, Queueable;

    public function __construct(
        private readonly InventoryMovement $movement,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->movement->loadMissing(['drug', 'pharmacy', 'user', 'batch']);

        $drugName = $this->movement->drug?->name ?? 'Fármaco';

        return AcalisMail::notification(
            subject: 'Alerta: merma de alto valor',
            headline: 'Merma de alto valor',
            greeting: 'Atención '.$notifiable->display_name,
            intro: 'Se registró una merma que supera el umbral gerencial y requiere su revisión.',
            details: [
                ['label' => 'Fármaco', 'value' => $drugName],
                ['label' => 'Lote', 'value' => $this->movement->batch?->batch_number],
                ['label' => 'Cantidad', 'value' => (string) $this->movement->quantity.' uds.'],
                ['label' => 'Valor', 'value' => '$'.number_format((float) $this->movement->total_value, 0, ',', '.')],
                ['label' => 'Motivo', 'value' => $this->movement->reason],
                ['label' => 'Bodega', 'value' => $this->movement->pharmacy?->name],
                ['label' => 'Registrado por', 'value' => $this->movement->user?->display_name],
            ],
            actionUrl: route('reports.monthly-waste'),
            actionLabel: 'Ver reporte de mermas',
            tone: AcalisMail::TONE_ERROR,
            footnote: 'Umbral de alerta: $50.000 CLP o superior.',
            preheader: "Merma de alto valor en {$drugName}",
        );
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $drugName = $this->movement->drug?->name ?? 'Fármaco';

        return $this->alertPayload(
            category: 'waste',
            title: 'Merma de alto valor',
            message: "{$drugName}: $".number_format((float) $this->movement->total_value, 0, ',', '.').' en merma.',
            url: route('reports.monthly-waste'),
            severity: 'error',
            extra: [
                'movement_id' => $this->movement->id,
                'type' => 'high_value_waste',
                'drug' => $drugName,
                'reason' => $this->movement->reason,
                'total_value' => $this->movement->total_value,
            ],
        );
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return $this->broadcastAlert($this->toArray($notifiable));
    }
}
