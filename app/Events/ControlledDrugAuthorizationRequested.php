<?php

namespace App\Events;

use App\Models\Batch;
use App\Models\Drug;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ControlledDrugAuthorizationRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Batch $batch,
        public readonly Drug $drug,
        public readonly User $requestedBy,
    ) {}
}
