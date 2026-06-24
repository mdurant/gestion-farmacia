<div
    class="relative"
    x-data="{
        open: false,
        markingAll: false,
        toast: null,
        showToast: false,
        hideTimer: null,
        togglePanel() {
            this.open = ! this.open;
            if (this.open) {
                $wire.refreshNotifications();
            }
        },
        closePanel() {
            this.open = false;
        },
        async markAllRead() {
            if (this.markingAll) return;
            this.markingAll = true;
            try {
                await $wire.markAllAsRead();
            } finally {
                this.markingAll = false;
            }
        },
        async markOne(id) {
            await $wire.markAsRead(id);
        },
        showRealtimeToast(detail) {
            this.toast = detail;
            this.showToast = true;
            clearTimeout(this.hideTimer);
            this.hideTimer = setTimeout(() => { this.showToast = false; }, 6000);
        }
    }"
    @realtime-toast.window="showRealtimeToast($event.detail)"
    @keydown.escape.window="closePanel()"
>
    <button
        type="button"
        class="btn btn-ghost btn-circle relative"
        aria-label="Notificaciones"
        aria-expanded="false"
        :aria-expanded="open"
        @click.stop="togglePanel()"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($unreadCount > 0)
            <span class="badge badge-error badge-xs absolute -top-0.5 -right-0.5 min-w-5 px-1" wire:key="unread-badge-{{ $unreadCount }}">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="closePanel()"
        class="absolute end-0 top-full z-50 mt-3 w-80 origin-top-end rounded-box border border-base-300 bg-base-100 shadow-xl sm:w-96"
        role="dialog"
        aria-label="Panel de notificaciones"
    >
        <div class="flex items-center justify-between border-b border-base-300 px-4 py-3">
            <p class="text-sm font-semibold">Notificaciones</p>
            @if ($unreadCount > 0)
                <button
                    type="button"
                    class="btn btn-ghost btn-xs text-primary"
                    :disabled="markingAll"
                    @click.stop.prevent="markAllRead()"
                >
                    <span x-show="!markingAll">Marcar todas leídas</span>
                    <span x-show="markingAll" class="loading loading-spinner loading-xs"></span>
                </button>
            @endif
        </div>

        <ul class="max-h-80 overflow-y-auto py-1">
            @forelse ($notifications as $notification)
                @php($data = $notification->data)
                <li wire:key="notification-{{ $notification->id }}">
                    @unless ($loop->first)
                        <div class="vx-notification-divider" role="separator" aria-hidden="true"></div>
                    @endunless

                    <button
                        type="button"
                        class="vx-notification-item flex w-full gap-3 px-4 py-3 text-left transition {{ $notification->read_at ? 'vx-notification-item--read' : 'vx-notification-item--unread' }}"
                        @click.stop.prevent="markOne('{{ $notification->id }}')"
                    >
                        <span @class([
                            'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                            'bg-success/15 text-success' => ($data['severity'] ?? '') === 'success',
                            'bg-warning/15 text-warning' => ($data['severity'] ?? '') === 'warning',
                            'bg-error/15 text-error' => ($data['severity'] ?? '') === 'error',
                            'bg-info/15 text-info' => ! in_array($data['severity'] ?? '', ['success', 'warning', 'error']),
                        ])>
                            @switch($data['category'] ?? 'info')
                                @case('user') 👤 @break
                                @case('resident') 🏠 @break
                                @case('inventory') 💊 @break
                                @case('waste') ⚠️ @break
                                @case('controlled_drug') 🔒 @break
                                @default 🔔
                            @endswitch
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold leading-snug">{{ $data['title'] ?? 'Alerta' }}</span>
                            <span class="mt-0.5 block text-xs text-base-content/60">{{ $data['message'] ?? '' }}</span>
                            <span class="mt-1 block text-[0.6875rem] text-base-content/45">{{ $notification->created_at?->diffForHumans() }}</span>
                        </span>
                        @unless ($notification->read_at)
                            <span class="mt-2 size-2 shrink-0 rounded-full bg-primary" aria-hidden="true"></span>
                        @endunless
                    </button>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-base-content/50">
                    Sin notificaciones por ahora
                </li>
            @endforelse
        </ul>
    </div>

    <div
        x-show="showToast"
        x-transition
        x-cloak
        class="toast toast-top toast-end z-[60] mt-16"
        style="display: none;"
    >
        <div
            class="alert shadow-lg min-w-72 max-w-sm"
            :class="{
                'alert-success': toast?.severity === 'success',
                'alert-warning': toast?.severity === 'warning',
                'alert-error': toast?.severity === 'error',
                'alert-info': !['success','warning','error'].includes(toast?.severity)
            }"
        >
            <div>
                <p class="font-semibold" x-text="toast?.title"></p>
                <p class="text-sm opacity-90" x-text="toast?.message"></p>
            </div>
            <template x-if="toast?.url">
                <a :href="toast.url" class="btn btn-xs btn-ghost">Ver</a>
            </template>
        </div>
    </div>
</div>
