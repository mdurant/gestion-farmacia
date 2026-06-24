<?php

namespace App\Notifications\Concerns;

use Illuminate\Notifications\Messages\BroadcastMessage;

trait FormatsRealtimeAlert
{
    /** @return array<string, mixed> */
    protected function alertPayload(
        string $category,
        string $title,
        string $message,
        ?string $url = null,
        string $severity = 'info',
        array $extra = [],
    ): array {
        return array_merge([
            'category' => $category,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'severity' => $severity,
        ], $extra);
    }

    /** @param array<string, mixed> $payload */
    protected function broadcastAlert(array $payload): BroadcastMessage
    {
        return new BroadcastMessage($payload);
    }
}
