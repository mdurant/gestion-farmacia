<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\InventoryMovement;
use App\Models\Resident;
use App\Models\SystemAlert;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $criticalStock = Batch::query()
            ->join('drugs', 'batches.drug_id', '=', 'drugs.id')
            ->whereColumn('batches.quantity', '<=', 'drugs.min_stock')
            ->where('batches.quantity', '>', 0)
            ->select('batches.*')
            ->with(['drug', 'pharmacy'])
            ->limit(5)
            ->get();

        $expiringAlerts = Batch::query()
            ->with(['drug', 'pharmacy'])
            ->whereDate('expiration_date', '<=', now()->addDays(30))
            ->where('quantity', '>', 0)
            ->orderBy('expiration_date')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'stats' => [
                'residents' => Resident::query()->where('is_active', true)->count(),
                'movements_today' => InventoryMovement::query()->whereDate('movement_at', today())->count(),
                'critical_alerts' => SystemAlert::query()->whereNull('read_at')->count(),
                'active_batches' => Batch::query()->where('quantity', '>', 0)->count(),
            ],
            'criticalStock' => $criticalStock,
            'expiringAlerts' => $expiringAlerts,
            'recentMovements' => InventoryMovement::query()
                ->with(['drug', 'pharmacy', 'user'])
                ->latest('movement_at')
                ->limit(8)
                ->get(),
            'systemAlerts' => SystemAlert::query()
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }
}
