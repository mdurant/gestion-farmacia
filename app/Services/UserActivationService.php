<?php

namespace App\Services;

use App\Events\UserStatusChanged;
use App\Models\User;
use App\Models\UserActivationChallenge;
use App\Notifications\UserActivationOtpNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class UserActivationService
{
    public function issueChallenge(User $user, bool $invalidatePrevious = true): void
    {
        if ($user->isActivated()) {
            return;
        }

        $plainCode = $this->generateOtp();

        DB::transaction(function () use ($user, $invalidatePrevious, $plainCode): void {
            if ($invalidatePrevious) {
                UserActivationChallenge::query()
                    ->where('user_id', $user->id)
                    ->whereNull('consumed_at')
                    ->update(['consumed_at' => now()]);
            }

            UserActivationChallenge::query()->create([
                'user_id' => $user->id,
                'code_hash' => Hash::make($plainCode),
                'expires_at' => now()->addMinutes((int) config('acalis.activation.otp_ttl_minutes', 15)),
            ]);

            $user->notify(new UserActivationOtpNotification($plainCode));
        });
    }

    public function findPendingUserByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', Str::lower(trim($email)))
            ->whereNull('activated_at')
            ->whereNull('deleted_at')
            ->first();
    }

    public function verifyChallenge(User $user, string $code): bool
    {
        $challenge = UserActivationChallenge::query()
            ->where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if ($challenge === null) {
            return false;
        }

        if ($challenge->isExpired() || $challenge->hasExceededAttempts()) {
            return false;
        }

        $challenge->increment('attempts');

        if (! Hash::check($code, $challenge->code_hash)) {
            return false;
        }

        $challenge->update(['consumed_at' => now()]);

        return true;
    }

    public function completeActivation(User $user, string $password): User
    {
        if ($user->isActivated()) {
            throw new RuntimeException('La cuenta ya está activada.');
        }

        return DB::transaction(function () use ($user, $password): User {
            $user->update([
                'password' => $password,
                'is_active' => true,
                'activated_at' => now(),
                'email_verified_at' => now(),
            ]);

            UserActivationChallenge::query()
                ->where('user_id', $user->id)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            $user = $user->fresh();

            UserStatusChanged::dispatch($user, 'activated', null);

            return $user;
        });
    }

    public function canResend(User $user): bool
    {
        if ($user->isActivated()) {
            return false;
        }

        $latest = UserActivationChallenge::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if ($latest === null) {
            return true;
        }

        $cooldown = (int) config('acalis.activation.resend_cooldown_seconds', 60);

        return $latest->created_at->addSeconds($cooldown)->isPast();
    }

    private function generateOtp(): string
    {
        $length = (int) config('acalis.activation.otp_length', 6);

        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}
