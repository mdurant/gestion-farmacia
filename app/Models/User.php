<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Support\DemoAccounts;
use App\Traits\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

    #[Fillable(['name', 'email', 'password', 'first_name', 'last_name', 'rut', 'role', 'is_active', 'activated_at', 'email_verified_at', 'current_session_id'])]
#[Hidden(['password', 'remember_token', 'rut'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'first_name' => 'encrypted',
            'last_name' => 'encrypted',
            'rut' => 'encrypted',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
        ];
    }

    public function isActivated(): bool
    {
        return $this->activated_at !== null;
    }

    public function isPendingActivation(): bool
    {
        return ! $this->isActivated() && ! $this->trashed();
    }

    /** @return HasMany<UserActivationChallenge, $this> */
    public function activationChallenges(): HasMany
    {
        return $this->hasMany(UserActivationChallenge::class);
    }

    /** @return Attribute<string, never> */
    protected function displayName(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->first_name || $this->last_name) {
                return trim("{$this->first_name} {$this->last_name}");
            }

            return (string) $this->name;
        });
    }

    /** @return HasMany<InventoryMovement, $this> */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /** @return HasMany<UserAccessLog, $this> */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(UserAccessLog::class);
    }

    public function routeNotificationForMail(): string
    {
        if (DemoAccounts::isDemoUser($this)) {
            return DemoAccounts::notificationInbox();
        }

        return (string) $this->email;
    }
}
