<?php

namespace App\Notifications;

use App\Enums\MovementType;
use App\Models\InventoryMovement;
use App\Notifications\Concerns\FormatsRealtimeAlert;
use App\Support\AcalisMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InventoryMovementNotification extends Notification implements ShouldQueue
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
        $this->movement->loadMissing(['drug', 'pharmacy', 'user', 'batch', 'resident']);

        return AcalisMail::notification(
            subject: $this->mailSubject(),
            headline: $this->alertTitle(),
            greeting: 'Hola '.$notifiable->display_name,
            intro: 'Se registró un nuevo movimiento en el inventario farmacéutico. Revise el detalle a continuación.',
            details: [
                ['label' => 'Tipo', 'value' => $this->movement->movement_type->label()],
                ['label' => 'Fármaco', 'value' => $this->movement->drug?->name],
                ['label' => 'Lote', 'value' => $this->movement->batch?->batch_number],
                ['label' => 'Cantidad', 'value' => (string) $this->movement->quantity.' uds.'],
                ['label' => 'Valor', 'value' => '$'.number_format((float) $this->movement->total_value, 0, ',', '.')],
                ['label' => 'Bodega', 'value' => $this->movement->pharmacy?->name],
                ['label' => 'Profesional', 'value' => $this->movement->user?->display_name],
                ['label' => 'Residente', 'value' => $this->movement->resident?->full_name],
                ['label' => 'Fecha', 'value' => $this->movement->movement_at?->timezone('America/Santiago')->format('d/m/Y H:i')],
            ],
            actionUrl: route('inventory.index'),
            actionLabel: 'Ver inventario',
            tone: $this->mailTone(),
            preheader: $this->movement->movement_type->label().': '.($this->movement->drug?->name ?? 'movimiento registrado'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $drugName = $this->movement->drug?->name ?? 'Fármaco';

        return $this->alertPayload(
            category: 'inventory',
            title: $this->alertTitle(),
            message: "{$this->movement->movement_type->label()}: {$drugName} · {$this->movement->quantity} uds.",
            url: route('inventory.index'),
            severity: $this->alertSeverity(),
            extra: [
                'movement_id' => $this->movement->id,
                'type' => $this->movement->movement_type->value,
                'drug' => $drugName,
                'quantity' => $this->movement->quantity,
                'total_value' => $this->movement->total_value,
            ],
        );
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return $this->broadcastAlert($this->toArray($notifiable));
    }

    private function mailSubject(): string
    {
        return match ($this->movement->movement_type) {
            MovementType::Entry => 'Ingreso de medicamentos registrado',
            MovementType::ExitAdministration => 'Administración a residente registrada',
            MovementType::ExitWaste => 'Salida por merma registrada',
            MovementType::ExitExpiration => 'Salida por vencimiento registrada',
            MovementType::Transfer => 'Traslado entre bodegas registrado',
        };
    }

    private function alertTitle(): string
    {
        return match ($this->movement->movement_type) {
            MovementType::Entry => 'Ingreso de medicamentos',
            MovementType::ExitAdministration => 'Administración a residente',
            MovementType::ExitWaste => 'Salida por merma',
            MovementType::ExitExpiration => 'Salida por vencimiento',
            MovementType::Transfer => 'Traslado entre bodegas',
        };
    }

    private function mailTone(): string
    {
        return match ($this->movement->movement_type) {
            MovementType::Entry => AcalisMail::TONE_SUCCESS,
            MovementType::ExitWaste, MovementType::ExitExpiration => AcalisMail::TONE_WARNING,
            default => AcalisMail::TONE_INFO,
        };
    }

    private function alertSeverity(): string
    {
        return match ($this->movement->movement_type) {
            MovementType::Entry => 'success',
            MovementType::ExitWaste, MovementType::ExitExpiration => 'warning',
            default => 'info',
        };
    }
}
