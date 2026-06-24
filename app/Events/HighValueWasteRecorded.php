<?php

namespace App\Events;

use App\Models\InventoryMovement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HighValueWasteRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly InventoryMovement $movement,
    ) {}
}
