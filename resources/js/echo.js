import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbEnabled = import.meta.env.VITE_REVERB_ENABLED !== 'false';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function resolveWsHost() {
    const configured = import.meta.env.VITE_REVERB_HOST;

    if (! configured || configured === 'localhost') {
        return window.location.hostname;
    }

    return configured;
}

function warnReverbOffline() {
    if (window.__acalisReverbOffline) {
        return;
    }

    window.__acalisReverbOffline = true;

    console.warn(
        '[Acalis] WebSocket Reverb no disponible. '
        + 'Inicie el servidor: php artisan reverb:start --host=127.0.0.1 --port=8080 '
        + '(o use composer dev para levantar todo).',
    );
}

function subscribeToUserNotifications() {
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');

    if (! userId || ! window.Echo) {
        return;
    }

    const channelName = `App.Models.User.${userId}`;

    if (window.__acalisEchoChannel === channelName) {
        return;
    }

    window.__acalisEchoChannel = channelName;

    window.Echo.private(channelName).notification((notification) => {
        if (window.Livewire) {
            window.Livewire.dispatch('realtime-notification-received', notification);
        }
    });
}

if (reverbKey && reverbEnabled) {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: resolveWsHost(),
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                },
            },
        });

        const connection = window.Echo?.connector?.pusher?.connection;

        if (connection) {
            connection.bind('unavailable', warnReverbOffline);
            connection.bind('failed', warnReverbOffline);
            connection.bind('error', warnReverbOffline);
        }

        document.addEventListener('livewire:init', subscribeToUserNotifications);
        document.addEventListener('DOMContentLoaded', subscribeToUserNotifications);
    } catch {
        warnReverbOffline();
    }
}
