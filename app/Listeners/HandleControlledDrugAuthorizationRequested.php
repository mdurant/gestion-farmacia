<?php

namespace App\Listeners;

use App\Events\ControlledDrugAuthorizationRequested;
use App\Notifications\ControlledDrugAuthorizationNotification;
use App\Support\RealtimeRecipients;
use Illuminate\Support\Facades\Notification;

class HandleControlledDrugAuthorizationRequested
{
    public function handle(ControlledDrugAuthorizationRequested $event): void
    {
        $recipients = RealtimeRecipients::uniqueMailboxes(
            RealtimeRecipients::roles(['director_medico', 'admin']),
        );

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new ControlledDrugAuthorizationNotification(
                    $event->batch,
                    $event->drug,
                    $event->requestedBy,
                ),
            );
        }
    }
}
