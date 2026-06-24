<?php

use App\Http\Controllers\InventoryMovementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'throttle:critical-inventory'])->group(function () {
    Route::post('/inventory/waste', [InventoryMovementController::class, 'storeWaste'])
        ->middleware('permission:inventory.waste')
        ->name('inventory.waste.store');
});
