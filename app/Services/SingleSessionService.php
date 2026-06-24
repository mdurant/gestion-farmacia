<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SingleSessionService
{
    public const SESSION_TOKEN_KEY = 'acalis_device_token';

    public function isEnabled(): bool
    {
        return (bool) config('acalis.session.single_device', true);
    }

    public function hasActiveSessionElsewhere(User $user): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $user->refresh();

        return $user->current_session_id !== null;
    }

    public function claimSession(User $user, Request $request): string
    {
        $token = Str::random(40);

        if ($this->isEnabled()) {
            $user->newQuery()->whereKey($user->getKey())->update(['current_session_id' => $token]);
            $user->setAttribute('current_session_id', $token);
            $request->session()->put(self::SESSION_TOKEN_KEY, $token);
        }

        return $token;
    }

    public function isCurrentSession(User $user, Request $request): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        $user->refresh();

        $sessionToken = $request->session()->get(self::SESSION_TOKEN_KEY);

        if (! is_string($sessionToken) || $sessionToken === '') {
            return $user->current_session_id === null;
        }

        if ($user->current_session_id === null) {
            return true;
        }

        return hash_equals($user->current_session_id, $sessionToken);
    }

    public function releaseSession(User $user, Request $request): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $sessionToken = $request->session()->get(self::SESSION_TOKEN_KEY);

        if (
            is_string($sessionToken)
            && $user->current_session_id !== null
            && hash_equals($user->current_session_id, $sessionToken)
        ) {
            $user->newQuery()->whereKey($user->getKey())->update(['current_session_id' => null]);
            $user->setAttribute('current_session_id', null);
        }

        $request->session()->forget(self::SESSION_TOKEN_KEY);
    }

    public function purgeStaleSessionsForUser(User $user, string $exceptSessionId): void
    {
        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->where('id', '!=', $exceptSessionId)
            ->delete();
    }
}
