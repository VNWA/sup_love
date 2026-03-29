import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Pusher: typeof Pusher;
    }
}

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY as string | undefined;
const host = (import.meta.env.VITE_REVERB_HOST as string | undefined) ?? 'localhost';
const port = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
const scheme = (import.meta.env.VITE_REVERB_SCHEME as string | undefined) ?? 'http';

export const echo =
    typeof reverbKey === 'string' && reverbKey.length > 0
        ? new Echo({
              broadcaster: 'reverb',
              key: reverbKey,
              wsHost: host,
              wsPort: port,
              wssPort: port,
              forceTLS: scheme === 'https',
              enabledTransports: ['ws', 'wss'],
          })
        : null;

export type WheelRoundEventPayload = {
    wheelRoomId: number;
    wheelRoundId: number;
    roundNumber: number;
};

/**
 * Lắng nghe realtime vòng quay trong phòng (Reverb / Pusher protocol).
 */
export function subscribeWheelRoom(
    roomId: number,
    handlers: {
        onRoundStarted?: (payload: WheelRoundEventPayload) => void;
        onRoundEnded?: (payload: WheelRoundEventPayload) => void;
    },
): () => void {
    if (echo === null) {
        return () => {};
    }

    const channel = echo.channel(`wheel-room.${roomId}`);

    channel.listen('.wheel.round.started', (e: WheelRoundEventPayload) => {
        handlers.onRoundStarted?.(e);
    });
    channel.listen('.wheel.round.ended', (e: WheelRoundEventPayload) => {
        handlers.onRoundEnded?.(e);
    });

    return () => {
        echo?.leave(`wheel-room.${roomId}`);
    };
}
