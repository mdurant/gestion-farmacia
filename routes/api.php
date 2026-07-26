<?php

use App\Http\Controllers\InventoryMovementController;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::middleware([
    StartSession::class,
    ShareErrorsFromSession::class,
    'auth',
    'active',
    'session.single',
    'session.policy',
    'throttle:critical-inventory',
])->group(function () {
    Route::post('/inventory/waste', [InventoryMovementController::class, 'storeWaste'])
        ->middleware('permission:inventory.waste')
        ->name('inventory.waste.store');
});
