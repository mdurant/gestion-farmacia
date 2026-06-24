<?php

namespace App\Listeners;

use App\Events\ResidentRegistered;
use App\Notifications\ResidentRegisteredNotification;
use App\Support\RealtimeRecipients;
use Illuminate\Support\Facades\Notification;

class HandleResidentRegistered
{
    public function handle(ResidentRegistered $event): void
    {
        $recipients = RealtimeRecipients::uniqueMailboxes(
            RealtimeRecipients::clinicalLeads(),
        );

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new ResidentRegisteredNotification($event->resident, $event->actor),
            );
        }
    }
}
