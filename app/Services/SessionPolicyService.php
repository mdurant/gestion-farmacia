<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionPolicyService
{
    public function initializeSession(Request $request): void
    {
        $now = now()->timestamp;

        $request->session()->put([
            'session_authenticated_at' => $now,
            'session_last_activity_at' => $now,
        ]);
    }

    public function touchActivity(Request $request): void
    {
        if (! Auth::check()) {
            return;
        }

        $request->session()->put('session_last_activity_at', now()->timestamp);
    }

    public function renewActivity(Request $request): bool
    {
        if (! Auth::check()) {
            return false;
        }

        if ($this->absoluteLifetimeExpired($request)) {
            return false;
        }

        $request->session()->put('session_last_activity_at', now()->timestamp);

        return true;
    }

    public function absoluteLifetimeExpired(Request $request): bool
    {
        $startedAt = $request->session()->get('session_authenticated_at');

        if (! is_int($startedAt)) {
            return false;
        }

        $lifetimeSeconds = $this->absoluteLifetimeSeconds();

        return (now()->timestamp - $startedAt) >= $lifetimeSeconds;
    }

    public function idleExpired(Request $request): bool
    {
        $lastActivity = $request->session()->get('session_last_activity_at');

        if (! is_int($lastActivity)) {
            return false;
        }

        $idleSeconds = $this->idleThresholdSeconds();
        $warningSeconds = $this->warningCountdownSeconds();

        return (now()->timestamp - $lastActivity) >= ($idleSeconds + $warningSeconds);
    }

    /** @return array<string, int|bool> */
    public function status(Request $request): array
    {
        $now = now()->timestamp;
        $startedAt = (int) $request->session()->get('session_authenticated_at', $now);
        $lastActivity = (int) $request->session()->get('session_last_activity_at', $startedAt);

        $absoluteLifetime = $this->absoluteLifetimeSeconds();
        $idleThreshold = $this->idleThresholdSeconds();
        $warningSeconds = $this->warningCountdownSeconds();

        $absoluteRemaining = max(0, $absoluteLifetime - ($now - $startedAt));
        $idleElapsed = max(0, $now - $lastActivity);
        $idleRemaining = max(0, $idleThreshold - $idleElapsed);

        return [
            'absolute_lifetime_seconds' => $absoluteLifetime,
            'idle_threshold_seconds' => $idleThreshold,
            'warning_countdown_seconds' => $warningSeconds,
            'absolute_remaining_seconds' => $absoluteRemaining,
            'idle_elapsed_seconds' => $idleElapsed,
            'idle_remaining_seconds' => $idleRemaining,
            'show_warning' => $idleElapsed >= $idleThreshold && $absoluteRemaining > 0,
            'warning_remaining_seconds' => max(
                0,
                $warningSeconds - max(0, $idleElapsed - $idleThreshold),
            ),
            'expired' => $absoluteRemaining <= 0 || $idleElapsed >= ($idleThreshold + $warningSeconds),
        ];
    }

    public function absoluteLifetimeSeconds(): int
    {
        return (int) config('acalis.session.absolute_lifetime_minutes', 60) * 60;
    }

    public function idleThresholdSeconds(): int
    {
        return (int) config('acalis.session.idle_minutes', 15) * 60;
    }

    public function warningCountdownSeconds(): int
    {
        return (int) config('acalis.session.warning_countdown_seconds', 60);
    }
}
