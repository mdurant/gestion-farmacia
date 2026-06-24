@php
    use App\Support\HttpErrorCatalog;

    $code = (int) ($code ?? 500);
    $meta = HttpErrorCatalog::resolve($code);
    $categoryLabels = [
        'auth' => 'Autenticación',
        'navigation' => 'Navegación',
        'client' => 'Cliente',
        'server' => 'Servidor',
        'network' => 'Red',
        'rate' => 'Límite de uso',
        'legal' => 'Cumplimiento',
        'easter' => 'Curiosidad HTTP',
    ];
    $categoryLabel = $categoryLabels[$meta['category']] ?? 'Sistema';
    $ref = strtoupper(substr(md5($code . request()->path() . now()->format('YmdH')), 0, 8));
    $toneClass = match ($meta['tone']) {
        'error' => 'vx-http-error--error',
        'warning' => 'vx-http-error--warning',
        'info' => 'vx-http-error--info',
        'accent' => 'vx-http-error--accent',
        default => 'vx-http-error--warning',
    };
@endphp

@extends('errors.layout')

@section('content')
<div class="vx-http-error {{ $toneClass }}">
    <div class="vx-http-error-bg" aria-hidden="true">
        <div class="vx-http-error-orb vx-http-error-orb--1"></div>
        <div class="vx-http-error-orb vx-http-error-orb--2"></div>
        <div class="vx-http-error-grid"></div>
    </div>

    <header class="vx-http-error-top">
        <a href="{{ url('/') }}" class="vx-http-error-brand">
            <span class="vx-http-error-brand-icon" aria-hidden="true">&#9879;</span>
            <span>Acalis Pharma</span>
        </a>
        <x-ui.theme-toggle />
    </header>

    <main class="vx-http-error-main">
        <div class="vx-http-error-card">
            <div class="vx-http-error-code-wrap">
                <p class="vx-http-error-eyebrow">{{ $categoryLabel }}</p>
                <div class="vx-http-error-code" aria-label="Código HTTP {{ $code }}">
                    <span class="vx-http-error-code-digit">{{ $code }}</span>
                </div>
                <div class="vx-http-error-badges">
                    <span class="badge badge-outline vx-http-error-badge">{{ $meta['title'] }}</span>
                    <span class="badge badge-ghost font-mono text-xs">HTTP/{{ $code }}</span>
                </div>
            </div>

            <div class="vx-http-error-copy">
                <h1 class="vx-http-error-headline">{{ $meta['headline'] }}</h1>
                <p class="vx-http-error-description">{{ $meta['description'] }}</p>

                @if (! empty($exception?->getMessage()) && ! in_array($exception->getMessage(), ['', 'Forbidden', 'Not Found', 'Unauthorized', 'Too Many Requests'], true) && config('app.debug'))
                    <div class="vx-http-error-debug alert alert-warning text-sm">
                        <span class="font-semibold">Detalle técnico:</span>
                        {{ $exception->getMessage() }}
                    </div>
                @endif

                <div class="vx-http-error-hint">
                    <span class="vx-http-error-hint-icon" aria-hidden="true">&#9432;</span>
                    <p><strong>¿Qué puede hacer?</strong> {{ $meta['hint'] }}</p>
                </div>

                <div class="vx-http-error-actions">
                    <button type="button" onclick="history.back()" class="btn btn-outline">
                        Volver atrás
                    </button>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir al panel</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Iniciar sesión</a>
                    @endauth
                    @if (Route::has('support'))
                        <a href="{{ route('support') }}" class="btn btn-ghost">Soporte</a>
                    @endif
                </div>

                <p class="vx-http-error-ref">
                    Referencia <kbd class="kbd kbd-xs">{{ $ref }}</kbd>
                    · {{ now()->timezone('America/Santiago')->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>

        <aside class="vx-http-error-aside" aria-label="Códigos HTTP relacionados">
            <p class="vx-http-error-aside-title">Familia de respuestas</p>
            <div class="vx-http-error-family">
                @foreach ([4 => '4xx Cliente', 5 => '5xx Servidor'] as $family => $label)
                    @php $active = intdiv($code, 100) === $family; @endphp
                    <div @class(['vx-http-error-family-item', 'is-active' => $active])>
                        <span class="font-mono font-bold">{{ $family }}xx</span>
                        <span>{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </aside>
    </main>

    <footer class="vx-http-error-footer">
        <p>Gestión farmacéutica · Residencias de larga estadía · Chile</p>
        @if (app()->environment('local') && Route::has('dev.http-errors.index'))
            <a href="{{ route('dev.http-errors.index') }}" class="link link-primary text-xs">Ver galería de códigos HTTP</a>
        @endif
    </footer>
</div>
@endsection
