<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso') — Acalis Pharma</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="vx-app font-sans antialiased" x-data>
    <div class="vx-auth-shell">
        {{-- Panel visual (referencia Vuesax) --}}
        <div class="vx-auth-visual relative hidden lg:flex">
            <div class="absolute right-6 top-6 z-10">
                <x-ui.theme-toggle />
            </div>

            <div class="relative z-[1] mx-auto max-w-lg">
                <div class="mb-8 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Acalis Pharma</h1>
                        <p class="text-sm text-white/80">Residencias de larga estadía · Chile</p>
                    </div>
                </div>

                <x-ui.pharma-illustration class="mb-8 w-full max-w-sm" />

                <h2 class="mb-3 text-3xl font-bold leading-tight">Trazabilidad farmacéutica segura</h2>
                <p class="mb-6 text-white/85 leading-relaxed">
                    Inventario, kardex, fármacos controlados y auditoría clínica en una sola plataforma
                    diseñada para equipos de salud.
                </p>

                <ul class="space-y-3 text-sm text-white/90">
                    <li class="flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-xs">✓</span>
                        Cifrado de datos sensibles (RUT, residentes)
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-xs">✓</span>
                        Roles clínicos: TENS, enfermería, dirección médica
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-xs">✓</span>
                        Alertas de stock y trazabilidad por lote
                    </li>
                </ul>
            </div>
        </div>

        {{-- Panel de formulario --}}
        <div class="vx-auth-panel relative">
            <div class="absolute right-4 top-4 lg:hidden">
                <x-ui.theme-toggle />
            </div>

            <div class="w-full max-w-md">
                <div class="mb-6 text-center lg:hidden">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary text-primary-content shadow-lg shadow-primary/25">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <h1 class="text-xl font-bold text-base-content">Acalis Pharma</h1>
                </div>

                <div class="vx-auth-card">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs text-base-content/50">
                    Sistema de gestión farmacéutica · Residencias de larga estadía · Chile
                </p>
            </div>
        </div>
    </div>
</body>
</html>
