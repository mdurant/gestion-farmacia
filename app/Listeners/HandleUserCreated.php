<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Notifications\UserCreatedNotification;
use App\Support\RealtimeRecipients;
use Illuminate\Support\Facades\Notification;

class HandleUserCreated
{
    public function handle(UserCreated $event): void
    {
        $recipients = RealtimeRecipients::admins()
            ->reject(fn ($user) => $user->id === $event->user->id);

        $recipients = RealtimeRecipients::uniqueMailboxes($recipients);

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new UserCreatedNotification($event->user, $event->actor),
            );
        }
    }
}
