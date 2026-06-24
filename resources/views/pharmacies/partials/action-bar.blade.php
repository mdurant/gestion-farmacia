<div class="mb-6 flex flex-wrap items-center gap-2">
    @can('create', \App\Models\Pharmacy::class)
        <a href="{{ route('pharmacies.create') }}" class="btn btn-primary btn-sm gap-2 shadow-sm">
            <x-ui.icon name="warehouse" class="size-4" />
            Nueva bodega
        </a>
        <a href="{{ route('pharmacies.cost-centers.index') }}" class="btn btn-secondary btn-sm gap-2 shadow-sm">
            <x-ui.icon name="cost-center" class="size-4" />
            Centros de costo
        </a>
    @endcan
    @can('create', \App\Models\InventoryMovement::class)
        <a href="{{ route('inventory.movements.transfer.create') }}" class="btn btn-info btn-sm gap-2 text-info-content shadow-sm">
            <x-ui.icon name="transfer" class="size-4" />
            Nuevo traslado
        </a>
    @endcan
    <a href="{{ route('pharmacies.transfers') }}" class="btn btn-ghost btn-sm ms-auto gap-2">
        <x-ui.icon name="history" class="size-4" />
        Historial de traslados
    </a>
</div>
