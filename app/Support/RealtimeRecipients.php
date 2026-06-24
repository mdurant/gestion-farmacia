<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

final class RealtimeRecipients
{
    /** @return Collection<int, User> */
    public static function admins(): Collection
    {
        return User::role('admin')->where('is_active', true)->get();
    }

    /** @return Collection<int, User> */
    public static function clinicalLeads(): Collection
    {
        return User::role(['director_medico', 'enfermero_jefe'])
            ->where('is_active', true)
            ->get();
    }

    /** @return Collection<int, User> */
    public static function inventoryStaff(): Collection
    {
        return User::role(['admin', 'director_medico', 'enfermero_jefe'])
            ->where('is_active', true)
            ->get();
    }

    /** @param list<string> $roles @return Collection<int, User> */
    public static function roles(array $roles): Collection
    {
        return User::role($roles)->where('is_active', true)->get();
    }

    /** @param Collection<int, User> ...$groups @return Collection<int, User> */
    public static function mergeUnique(Collection ...$groups): Collection
    {
        return self::uniqueMailboxes(
            collect($groups)->flatten(1),
        );
    }

    /**
     * Un destinatario por bandeja de correo (evita duplicados con cuentas demo Gmail compartidas).
     *
     * @param  Collection<int, User>  $users
     * @return Collection<int, User>
     */
    public static function uniqueMailboxes(Collection $users): Collection
    {
        return $users
            ->unique(fn (User $user): string => strtolower($user->routeNotificationForMail()))
            ->values();
    }
}
