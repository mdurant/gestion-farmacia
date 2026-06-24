@props(['excelRoute', 'pdfRoute'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
    <a href="{{ $excelRoute }}" class="btn btn-sm btn-success btn-outline gap-2" target="_blank" rel="noopener noreferrer">
        <x-ui.icon name="excel" class="size-4" />
        Exportar Excel
    </a>
    <a href="{{ $pdfRoute }}" class="btn btn-sm btn-error btn-outline gap-2" target="_blank" rel="noopener noreferrer">
        <x-ui.icon name="pdf" class="size-4" />
        Exportar PDF
    </a>
</div>
