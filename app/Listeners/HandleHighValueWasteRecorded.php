<?php

namespace App\Listeners;

use App\Events\HighValueWasteRecorded;
use App\Notifications\HighValueWasteAlert;
use App\Support\RealtimeRecipients;
use Illuminate\Support\Facades\Notification;

class HandleHighValueWasteRecorded
{
    public function handle(HighValueWasteRecorded $event): void
    {
        $recipients = RealtimeRecipients::roles(['admin', 'director_medico']);
        $recipients = RealtimeRecipients::uniqueMailboxes($recipients);

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new HighValueWasteAlert($event->movement));
        }
    }
}
