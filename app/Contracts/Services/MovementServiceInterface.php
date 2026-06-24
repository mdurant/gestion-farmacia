<?php

namespace App\Contracts\Services;

use App\DTOs\Inventory\AdministrationMovementData;
use App\DTOs\Inventory\EntryMovementData;
use App\DTOs\Inventory\ExpirationMovementData;
use App\DTOs\Inventory\TransferMovementData;
use App\DTOs\Inventory\WasteMovementData;
use App\Models\InventoryMovement;

interface MovementServiceInterface
{
    public function processWasteExit(WasteMovementData $data): InventoryMovement;

    public function processEntry(EntryMovementData $data): InventoryMovement;

    public function processTransfer(TransferMovementData $data): InventoryMovement;

    public function processAdministration(AdministrationMovementData $data): InventoryMovement;

    public function processExpirationExit(ExpirationMovementData $data): InventoryMovement;
}
