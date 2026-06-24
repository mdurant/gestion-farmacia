<?php

namespace App\Listeners;

use App\Events\UserStatusChanged;
use App\Notifications\UserStatusChangedNotification;
use App\Support\RealtimeRecipients;
use Illuminate\Support\Facades\Notification;

class HandleUserStatusChanged
{
    public function handle(UserStatusChanged $event): void
    {
        $recipients = RealtimeRecipients::mergeUnique(
            RealtimeRecipients::admins(),
            RealtimeRecipients::clinicalLeads(),
        )->reject(fn ($user) => $user->id === $event->user->id);

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new UserStatusChangedNotification($event->user, $event->action, $event->actor),
            );
        }
    }
}
