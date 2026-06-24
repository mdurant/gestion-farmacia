<div class="mb-6 flex flex-wrap items-center gap-2">
    @can('create', \App\Models\Resident::class)
        <a href="{{ route('residents.create') }}" class="btn btn-primary btn-sm gap-2 shadow-sm">
            <x-ui.icon name="resident" class="size-4" />
            Nuevo residente
        </a>
    @endcan
    @can('create', \App\Models\InventoryMovement::class)
        <a href="{{ route('inventory.movements.administration.create') }}" class="btn btn-secondary btn-sm gap-2 shadow-sm">
            <x-ui.icon name="administration" class="size-4" />
            Registrar administración
        </a>
    @endcan
</div>
