<?php

namespace App\Providers;

use App\Events\ControlledDrugAuthorizationRequested;
use App\Events\HighValueWasteRecorded;
use App\Events\InventoryMovementRecorded;
use App\Events\ResidentRegistered;
use App\Events\UserCreated;
use App\Events\UserStatusChanged;
use App\Listeners\HandleControlledDrugAuthorizationRequested;
use App\Listeners\HandleHighValueWasteRecorded;
use App\Listeners\HandleInventoryMovementRecorded;
use App\Listeners\HandleResidentRegistered;
use App\Listeners\HandleUserCreated;
use App\Listeners\HandleUserStatusChanged;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /** @var array<class-string, list<class-string>> */
    protected $listen = [
        InventoryMovementRecorded::class => [
            HandleInventoryMovementRecorded::class,
        ],
        HighValueWasteRecorded::class => [
            HandleHighValueWasteRecorded::class,
        ],
        UserCreated::class => [
            HandleUserCreated::class,
        ],
        UserStatusChanged::class => [
            HandleUserStatusChanged::class,
        ],
        ResidentRegistered::class => [
            HandleResidentRegistered::class,
        ],
        ControlledDrugAuthorizationRequested::class => [
            HandleControlledDrugAuthorizationRequested::class,
        ],
    ];
}
