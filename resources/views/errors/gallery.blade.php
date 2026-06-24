@php
    use App\Support\HttpErrorCatalog;
@endphp
<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Galería HTTP — Acalis Pharma</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="vx-app font-sans antialiased" x-data>
    <div class="vx-http-gallery">
        <header class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-primary">Solo desarrollo</p>
                <h1 class="mt-1 text-3xl font-bold text-base-content">Galería de códigos HTTP</h1>
                <p class="mt-2 max-w-2xl text-base-content/60">
                    Previsualice todas las páginas de error del sistema con copy en español,
                    tonos por severidad y acciones de recuperación.
                </p>
            </div>
            <x-ui.theme-toggle />
        </header>

        <div class="mb-6 flex flex-wrap gap-2">
            <span class="badge badge-error badge-outline">5xx Servidor</span>
            <span class="badge badge-warning badge-outline">4xx Cliente</span>
            <span class="badge badge-info badge-outline">Navegación / Auth</span>
            <span class="badge badge-ghost">{{ count($codes) }} códigos</span>
        </div>

        <div class="vx-http-gallery-grid">
            @foreach ($codes as $code)
                @php $meta = $catalog[$code]; @endphp
                <a href="{{ route('dev.http-errors.preview', $code) }}"
                   class="vx-http-gallery-card vx-http-gallery-card--{{ $meta['tone'] }}"
                   target="_blank" rel="noopener">
                    <span class="vx-http-gallery-code">{{ $code }}</span>
                    <span class="font-semibold text-base-content">{{ $meta['title'] }}</span>
                    <span class="text-sm text-base-content/55 line-clamp-2">{{ $meta['headline'] }}</span>
                    <span class="badge badge-ghost badge-sm mt-1 w-fit">{{ $meta['category'] }}</span>
                </a>
            @endforeach
        </div>

        <p class="mt-10 text-center text-xs text-base-content/45">
            Esta ruta solo existe con <kbd class="kbd kbd-xs">APP_ENV=local</kbd>.
            <a href="{{ url('/') }}" class="link link-primary">Volver al inicio</a>
        </p>
    </div>
</body>
</html>
