<?php

namespace App\Providers;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\BatchRepositoryInterface;
use App\Contracts\Repositories\CostCenterRepositoryInterface;
use App\Contracts\Repositories\DrugRepositoryInterface;
use App\Contracts\Repositories\InventoryMovementRepositoryInterface;
use App\Contracts\Repositories\PharmacyRepositoryInterface;
use App\Contracts\Repositories\ResidentRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\MovementServiceInterface;
use App\Contracts\Services\ReportServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Repositories\AuditLogRepository;
use App\Repositories\BatchRepository;
use App\Repositories\CostCenterRepository;
use App\Repositories\DrugRepository;
use App\Repositories\InventoryMovementRepository;
use App\Repositories\PharmacyRepository;
use App\Repositories\ResidentRepository;
use App\Repositories\UserRepository;
use App\Services\MovementService;
use App\Services\ReportService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        AuditLogRepositoryInterface::class => AuditLogRepository::class,
        BatchRepositoryInterface::class => BatchRepository::class,
        CostCenterRepositoryInterface::class => CostCenterRepository::class,
        DrugRepositoryInterface::class => DrugRepository::class,
        InventoryMovementRepositoryInterface::class => InventoryMovementRepository::class,
        PharmacyRepositoryInterface::class => PharmacyRepository::class,
        ResidentRepositoryInterface::class => ResidentRepository::class,
        MovementServiceInterface::class => MovementService::class,
        ReportServiceInterface::class => ReportService::class,
        UserRepositoryInterface::class => UserRepository::class,
        UserServiceInterface::class => UserService::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}
