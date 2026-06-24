<?php

use App\Http\Controllers\AccessLogController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CatalogExportController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'active', 'session.single', 'session.policy'])->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::middleware('permission:inventory.view')->prefix('inventario')->name('inventory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InventoryController::class, 'index'])->name('index');
        Route::get('/movimientos', [\App\Http\Controllers\InventoryController::class, 'movements'])->name('movements.index');
        Route::get('/farmacos', [\App\Http\Controllers\InventoryController::class, 'drugs'])->name('drugs.index');
        Route::get('/farmacos/export/{format}', [CatalogExportController::class, 'drugs'])
            ->where('format', 'csv|pdf')
            ->name('drugs.export');

        Route::middleware('permission:pharmacies.manage')->group(function () {
            Route::get('/farmacos/crear', [\App\Http\Controllers\DrugController::class, 'create'])->name('drugs.create');
            Route::post('/farmacos', [\App\Http\Controllers\DrugController::class, 'store'])->name('drugs.store');
        });

        Route::get('/farmacos/{drug}', [\App\Http\Controllers\DrugController::class, 'show'])->name('drugs.show');

        Route::get('/lotes/{batch}', [\App\Http\Controllers\BatchController::class, 'show'])->name('batches.show');

        Route::middleware('permission:pharmacies.manage')->group(function () {
            Route::get('/farmacos/{drug}/editar', [\App\Http\Controllers\DrugController::class, 'edit'])->name('drugs.edit');
            Route::put('/farmacos/{drug}', [\App\Http\Controllers\DrugController::class, 'update'])->name('drugs.update');
            Route::get('/lotes/{batch}/editar', [\App\Http\Controllers\BatchController::class, 'edit'])->name('batches.edit');
            Route::put('/lotes/{batch}', [\App\Http\Controllers\BatchController::class, 'update'])->name('batches.update');
            Route::delete('/lotes/{batch}', [\App\Http\Controllers\BatchController::class, 'destroy'])->name('batches.destroy');
        });

        Route::middleware('permission:inventory.move')->group(function () {
            Route::get('/movimientos/entrada', [\App\Http\Controllers\InventoryMovementController::class, 'createEntry'])->name('movements.entry.create');
            Route::post('/movimientos/entrada', [\App\Http\Controllers\InventoryMovementController::class, 'storeEntry'])->name('movements.entry.store');
            Route::get('/movimientos/traslado', [\App\Http\Controllers\InventoryMovementController::class, 'createTransfer'])->name('movements.transfer.create');
            Route::post('/movimientos/traslado', [\App\Http\Controllers\InventoryMovementController::class, 'storeTransfer'])->name('movements.transfer.store');
            Route::get('/movimientos/administracion', [\App\Http\Controllers\InventoryMovementController::class, 'createAdministration'])->name('movements.administration.create');
            Route::post('/movimientos/administracion', [\App\Http\Controllers\InventoryMovementController::class, 'storeAdministration'])->name('movements.administration.store');
            Route::get('/movimientos/vencimiento', [\App\Http\Controllers\InventoryMovementController::class, 'createExpiration'])->name('movements.expiration.create');
            Route::post('/movimientos/vencimiento', [\App\Http\Controllers\InventoryMovementController::class, 'storeExpiration'])->name('movements.expiration.store');
        });

        Route::middleware('permission:inventory.waste')->group(function () {
            Route::get('/movimientos/merma', [\App\Http\Controllers\InventoryMovementController::class, 'createWaste'])->name('movements.waste.create');
            Route::post('/movimientos/merma', [\App\Http\Controllers\InventoryMovementController::class, 'storeWasteWeb'])->name('movements.waste.store');
        });
    });

    Route::middleware('permission:inventory.view')->prefix('bodegas')->name('pharmacies.')->group(function () {
        Route::get('/', [PharmacyController::class, 'index'])->name('index');
        Route::get('/export/{format}', [CatalogExportController::class, 'pharmacies'])
            ->where('format', 'csv|pdf')
            ->name('export');
        Route::get('/traslados', [PharmacyController::class, 'transfers'])->name('transfers');

        Route::prefix('centros-de-costo')->name('cost-centers.')->group(function () {
            Route::get('/', [CostCenterController::class, 'index'])->name('index');

            Route::middleware('permission:pharmacies.manage')->group(function () {
                Route::get('/crear', [CostCenterController::class, 'create'])->name('create');
                Route::post('/', [CostCenterController::class, 'store'])->name('store');
            });

            Route::get('/{costCenter}', [CostCenterController::class, 'show'])->name('show');

            Route::middleware('permission:pharmacies.manage')->group(function () {
                Route::get('/{costCenter}/editar', [CostCenterController::class, 'edit'])->name('edit');
                Route::put('/{costCenter}', [CostCenterController::class, 'update'])->name('update');
                Route::delete('/{costCenter}', [CostCenterController::class, 'destroy'])->name('destroy');
            });
        });

        Route::middleware('permission:pharmacies.manage')->group(function () {
            Route::get('/crear', [PharmacyController::class, 'create'])->name('create');
            Route::post('/', [PharmacyController::class, 'store'])->name('store');
        });

        Route::get('/{pharmacy}', [PharmacyController::class, 'show'])->name('show');

        Route::middleware('permission:pharmacies.manage')->group(function () {
            Route::get('/{pharmacy}/editar', [PharmacyController::class, 'edit'])->name('edit');
            Route::put('/{pharmacy}', [PharmacyController::class, 'update'])->name('update');
            Route::delete('/{pharmacy}', [PharmacyController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware('permission:residents.view')->prefix('residentes')->name('residents.')->group(function () {
        Route::get('/acceso', [\App\Http\Controllers\ResidentDataAccessController::class, 'show'])->name('gate.show');
        Route::post('/acceso', [\App\Http\Controllers\ResidentDataAccessController::class, 'confirm'])
            ->middleware('throttle:resident-data-gate')
            ->name('gate.confirm');

        Route::middleware('resident.data.gate')->group(function () {
            Route::get('/', [ResidentController::class, 'index'])->name('index');
            Route::get('/export/{format}', [CatalogExportController::class, 'residents'])
                ->where('format', 'csv|pdf')
                ->name('export');

            Route::middleware('permission:residents.manage')->group(function () {
                Route::get('/crear', [ResidentController::class, 'create'])->name('create');
                Route::post('/', [ResidentController::class, 'store'])->name('store');
            });

            Route::get('/{resident}', [ResidentController::class, 'show'])->name('show');

            Route::middleware('permission:residents.manage')->group(function () {
                Route::get('/{resident}/editar', [ResidentController::class, 'edit'])->name('edit');
                Route::put('/{resident}', [ResidentController::class, 'update'])->name('update');
                Route::delete('/{resident}', [ResidentController::class, 'destroy'])->name('destroy');
            });
        });
    });

    Route::middleware('permission:reports.internal')->prefix('reportes')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/kardex', [ReportController::class, 'kardex'])->name('kardex');
        Route::get('/consumo-residentes', [ReportController::class, 'residentConsumption'])->name('resident-consumption');
        Route::get('/export/{report}/{format}', [ReportController::class, 'export'])
            ->where('format', 'csv|pdf')
            ->name('export');

        Route::middleware('permission:reports.executive')->group(function () {
            Route::get('/valorizacion', [ReportController::class, 'valuation'])->name('valuation');
            Route::get('/mermas-mensuales', [ReportController::class, 'monthlyWaste'])->name('monthly-waste');
            Route::get('/proyeccion-compra', [ReportController::class, 'purchaseProjection'])->name('purchase-projection');
        });
    });

    Route::get('/soporte', SupportController::class)
        ->middleware('permission:support.access')
        ->name('support');

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/crear', [UserController::class, 'create'])->name('users.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
        Route::get('/usuarios/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/usuarios/{user}/auditoria-acceso/export/{format}', [AccessLogController::class, 'exportUser'])
            ->where('format', 'csv|pdf')
            ->name('users.access-log.export');
        Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/usuarios/{user}/estado', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/usuarios/{user}/reenviar-activacion', [UserController::class, 'resendActivation'])->name('users.resend-activation');
        Route::patch('/usuarios/{userId}/restaurar', [UserController::class, 'restore'])->name('users.restore');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/auditoria', [AuditLogController::class, 'index'])->name('audit.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/auditoria-acceso/export/{format}', [AccessLogController::class, 'exportProfile'])
        ->where('format', 'csv|pdf')
        ->name('profile.access-log.export');
});

if (app()->environment(['local', 'testing'])) {
    Route::middleware(['auth', 'active', 'session.single', 'session.policy'])
        ->prefix('dev/sesion-demo')
        ->name('dev.session-demo.')
        ->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Dev\SessionDemoController::class, 'show'])->name('index');
            Route::get('/estado', [\App\Http\Controllers\Dev\SessionDemoController::class, 'status'])->name('status');
            Route::post('/simular-otro-equipo', [\App\Http\Controllers\Dev\SessionDemoController::class, 'simulateOtherDevice'])
                ->name('simulate');
        });

    Route::prefix('dev/errores-http')->name('dev.http-errors.')->group(function (): void {
        Route::get('/', function () {
            return view('errors.gallery', [
                'codes' => \App\Support\HttpErrorCatalog::codes(),
                'catalog' => \App\Support\HttpErrorCatalog::all(),
            ]);
        })->name('index');

        Route::get('/{code}', function (int $code) {
            abort_unless(array_key_exists($code, \App\Support\HttpErrorCatalog::all()), 404);

            return response()->view('errors.show', [
                'code' => $code,
                'exception' => null,
            ], $code);
        })->where('code', '[0-9]+')->name('preview');
    });
}

