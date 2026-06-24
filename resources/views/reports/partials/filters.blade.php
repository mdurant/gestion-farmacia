<form method="GET" class="filter-toolbar space-y-4">
    <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
        <x-ui.field label="Desde" for="from">
            <x-ui.input id="from" type="date" name="from" value="{{ $filters->from }}" />
        </x-ui.field>
        <x-ui.field label="Hasta" for="to">
            <x-ui.input id="to" type="date" name="to" value="{{ $filters->to }}" />
        </x-ui.field>
        <x-ui.field label="Bodega" for="pharmacy_id">
            <x-ui.select id="pharmacy_id" name="pharmacy_id">
                <option value="">Todas</option>
                @foreach ($pharmacies as $pharmacy)
                    <option value="{{ $pharmacy->id }}" @selected($filters->pharmacyId == $pharmacy->id)>{{ $pharmacy->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.field>
        <x-ui.field label="Centro de costo" for="cost_center_id">
            <x-ui.select id="cost_center_id" name="cost_center_id">
                <option value="">Todos</option>
                @foreach ($costCenters as $center)
                    <option value="{{ $center->id }}" @selected($filters->costCenterId == $center->id)>{{ $center->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.field>
        @if ($showDrugFilter ?? true)
            <x-ui.field label="Fármaco" for="drug_id">
                <x-ui.select id="drug_id" name="drug_id">
                    <option value="">Todos</option>
                    @foreach ($drugs as $drug)
                        <option value="{{ $drug->id }}" @selected($filters->drugId == $drug->id)>{{ $drug->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        @endif
        @if ($showResidentFilter ?? false)
            <x-ui.field label="Residente" for="resident_id">
                <x-ui.select id="resident_id" name="resident_id">
                    <option value="">Todos</option>
                    @foreach ($residents as $resident)
                        <option value="{{ $resident->id }}" @selected($filters->residentId == $resident->id)>{{ $resident->full_name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="btn btn-primary btn-sm">Aplicar filtros</button>
        <a href="{{ url()->current() }}" class="btn btn-ghost btn-sm">Limpiar</a>
        @isset($exportReport)
            <div class="ms-auto flex gap-2">
                <a href="{{ route('reports.export', ['report' => $exportReport, 'format' => 'csv'] + request()->query()) }}"
                   class="btn btn-sm btn-success btn-outline gap-2" target="_blank" rel="noopener noreferrer">
                    <x-ui.icon name="excel" class="size-4" />
                    Exportar Excel
                </a>
                <a href="{{ route('reports.export', ['report' => $exportReport, 'format' => 'pdf'] + request()->query()) }}"
                   class="btn btn-sm btn-error btn-outline gap-2" target="_blank" rel="noopener noreferrer">
                    <x-ui.icon name="pdf" class="size-4" />
                    Exportar PDF
                </a>
            </div>
        @endisset
    </div>
</form>
