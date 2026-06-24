<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    /** @var Collection<int, \Illuminate\Notifications\DatabaseNotification> */
    public Collection $notifications;

    public function mount(): void
    {
        $this->notifications = collect();
        $this->refreshNotifications();
    }

    public function refreshNotifications(): void
    {
        $user = auth()->user();
        $this->unreadCount = $user->unreadNotifications()->count();
        $this->notifications = $user->notifications()->limit(12)->get();
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->where('id', $notificationId)->first();

        if ($notification !== null) {
            $notification->markAsRead();
        }

        $this->refreshNotifications();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        $this->refreshNotifications();
    }

    /** @param  array<string, mixed>|null  $notification */
    #[On('realtime-notification-received')]
    public function onRealtimeNotificationReceived(?array $notification = null): void
    {
        $this->refreshNotifications();

        $data = is_array($notification) ? $notification : [];

        $this->dispatch(
            'realtime-toast',
            title: $data['title'] ?? 'Nueva alerta',
            message: $data['message'] ?? '',
            severity: $data['severity'] ?? 'info',
            url: $data['url'] ?? null,
        );
    }

    public function render(): View
    {
        return view('livewire.notification-bell');
    }
}
