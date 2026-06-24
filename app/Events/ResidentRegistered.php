<?php

namespace App\Events;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResidentRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Resident $resident,
        public readonly ?User $actor = null,
    ) {}
}
