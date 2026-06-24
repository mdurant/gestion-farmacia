<div class="mb-6 flex flex-wrap items-center gap-2">
    @can('create', \App\Models\InventoryMovement::class)
        <a href="{{ route('inventory.movements.entry.create') }}" class="btn btn-success btn-sm gap-2 shadow-sm">
            <x-ui.icon name="entry" class="size-4" />
            Entrada
        </a>
        <a href="{{ route('inventory.movements.transfer.create') }}" class="btn btn-info btn-sm gap-2 text-info-content shadow-sm">
            <x-ui.icon name="transfer" class="size-4" />
            Traslado
        </a>
        <a href="{{ route('inventory.movements.administration.create') }}" class="btn btn-primary btn-sm gap-2 shadow-sm">
            <x-ui.icon name="administration" class="size-4" />
            Administración
        </a>
        <a href="{{ route('inventory.movements.expiration.create') }}" class="btn btn-warning btn-sm gap-2 shadow-sm">
            <x-ui.icon name="expiration" class="size-4" />
            Vencimiento
        </a>
    @endcan
    @can('registerWaste', \App\Models\InventoryMovement::class)
        <a href="{{ route('inventory.movements.waste.create') }}" class="btn btn-error btn-sm gap-2 shadow-sm">
            <x-ui.icon name="waste" class="size-4" />
            Merma
        </a>
    @endcan
    <a href="{{ route('inventory.movements.index') }}" class="btn btn-ghost btn-sm ms-auto gap-2">
        <x-ui.icon name="history" class="size-4" />
        Historial de movimientos
    </a>
</div>
