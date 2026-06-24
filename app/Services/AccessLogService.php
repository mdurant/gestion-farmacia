<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAccessLog;
use App\Support\UserAgentParser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccessLogService
{
    public const PENDING_LOGIN_KEY = 'pending_device_login';

    public function __construct(
        private readonly GeoLocationService $geoLocation,
    ) {}

    public function recordConnection(User $user, Request $request, string $sessionToken): UserAccessLog
    {
        return UserAccessLog::query()->create([
            'user_id' => $user->id,
            'session_token' => $sessionToken,
            'browser' => UserAgentParser::browser($request->userAgent()),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'location' => $this->geoLocation->resolve($request->ip()),
            'connected_at' => now(),
        ]);
    }

    public function recordDisconnection(
        User $user,
        ?string $sessionToken,
        string $reason = 'logout',
    ): void {
        if (! is_string($sessionToken) || $sessionToken === '') {
            return;
        }

        UserAccessLog::query()
            ->where('user_id', $user->id)
            ->where('session_token', $sessionToken)
            ->whereNull('disconnected_at')
            ->update([
                'disconnected_at' => now(),
                'disconnect_reason' => $reason,
            ]);
    }

    /** @return array{browser: string, connected_at: ?Carbon, location: string}|null */
    public function getActiveSessionInfo(User $user): ?array
    {
        $user->refresh();

        $token = $user->current_session_id;
        if ($token === null) {
            return null;
        }

        $log = UserAccessLog::query()
            ->where('user_id', $user->id)
            ->where('session_token', $token)
            ->whereNull('disconnected_at')
            ->latest('connected_at')
            ->first();

        if ($log !== null) {
            return [
                'browser' => $log->browser ?? 'Desconocido',
                'connected_at' => $log->connected_at,
                'location' => $log->location ?? 'Ubicación no disponible',
            ];
        }

        $session = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->first();

        if ($session === null) {
            return [
                'browser' => 'Desconocido',
                'connected_at' => null,
                'location' => 'Ubicación no disponible',
            ];
        }

        return [
            'browser' => UserAgentParser::browser($session->user_agent) ?? 'Desconocido',
            'connected_at' => Carbon::createFromTimestamp((int) $session->last_activity, 'America/Santiago'),
            'location' => $this->geoLocation->resolve($session->ip_address),
        ];
    }

    public function storePendingLogin(Request $request, User $user): void
    {
        $request->session()->put(self::PENDING_LOGIN_KEY, [
            'user_id' => $user->id,
            'remember' => $request->boolean('remember'),
            'terms_version' => (string) config('acalis.terms.version', '1.0.0'),
            'expires_at' => now()->addMinutes(5)->timestamp,
        ]);
    }

    /** @return array{user_id: int, remember: bool, terms_version: string}|null */
    public function pullPendingLogin(Request $request): ?array
    {
        $pending = $request->session()->pull(self::PENDING_LOGIN_KEY);

        if (! is_array($pending)) {
            return null;
        }

        $expiresAt = (int) ($pending['expires_at'] ?? 0);
        if ($expiresAt < now()->timestamp) {
            return null;
        }

        $userId = (int) ($pending['user_id'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        return [
            'user_id' => $userId,
            'remember' => (bool) ($pending['remember'] ?? false),
            'terms_version' => (string) ($pending['terms_version'] ?? config('acalis.terms.version', '1.0.0')),
        ];
    }

    /** @return LengthAwarePaginator<int, UserAccessLog> */
    public function paginateForUser(User $user, int $perPage = 25): LengthAwarePaginator
    {
        return UserAccessLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('connected_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{0: list<string>, 1: list<list<string>>}
     */
    public function exportDataForUser(User $user): array
    {
        $headers = [
            'Fecha conexión',
            'Hora conexión',
            'Fecha desconexión',
            'Hora desconexión',
            'Navegador',
            'Ubicación',
            'IP',
            'Estado',
        ];

        $rows = UserAccessLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('connected_at')
            ->get()
            ->map(function (UserAccessLog $log): array {
                $connected = $log->connected_at?->timezone('America/Santiago');
                $disconnected = $log->disconnected_at?->timezone('America/Santiago');

                return [
                    $connected?->format('d/m/Y') ?? '—',
                    $connected?->format('H:i:s') ?? '—',
                    $disconnected?->format('d/m/Y') ?? '—',
                    $disconnected?->format('H:i:s') ?? '—',
                    $log->browser ?? '—',
                    $log->location ?? '—',
                    $log->ip_address ?? '—',
                    $log->isActive() ? 'Activa' : ($log->disconnect_reason ?? 'Cerrada'),
                ];
            })
            ->all();

        return [$headers, $rows];
    }
}
