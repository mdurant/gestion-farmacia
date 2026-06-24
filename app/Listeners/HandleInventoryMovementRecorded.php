<?php

namespace App\Listeners;

use App\Enums\MovementType;
use App\Events\InventoryMovementRecorded;
use App\Models\InventoryMovement;
use App\Notifications\InventoryMovementNotification;
use App\Services\MovementService;
use App\Support\RealtimeRecipients;
use Illuminate\Support\Facades\Notification;

class HandleInventoryMovementRecorded
{
    public function handle(InventoryMovementRecorded $event): void
    {
        $movement = $event->movement;
        $recipients = match ($movement->movement_type) {
            MovementType::Entry => RealtimeRecipients::inventoryStaff(),
            MovementType::ExitAdministration => RealtimeRecipients::clinicalLeads(),
            default => RealtimeRecipients::roles(['director_medico']),
        };

        if ($this->isHandledByHighValueWasteAlert($movement)) {
            return;
        }

        $recipients = RealtimeRecipients::uniqueMailboxes($recipients);

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new InventoryMovementNotification($movement));
        }
    }

    private function isHandledByHighValueWasteAlert(InventoryMovement $movement): bool
    {
        return $movement->movement_type === MovementType::ExitWaste
            && (float) $movement->total_value >= MovementService::highValueWasteThreshold();
    }
}
