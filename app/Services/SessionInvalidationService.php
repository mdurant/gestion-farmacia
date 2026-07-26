<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SessionInvalidationService
{
    /**
     * Invalida todas las sesiones persistidas y tokens de dispositivo del usuario.
     */
    public function invalidateAllFor(User $user): void
    {
        $user->forceFill([
            'current_session_id' => null,
            'remember_token' => Str::random(60),
        ])->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }
    }
}
