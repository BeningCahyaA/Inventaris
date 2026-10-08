import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const reverbAppKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbAppKey) {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbAppKey,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    } catch (e) {
        console.warn('[Echo] Gagal menginisialisasi Laravel Echo:', e.message);
    }
} else {
    console.warn('[Echo] VITE_REVERB_APP_KEY tidak ditemukan. Realtime notifications dinonaktifkan.');
}
