<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <title>@yield('title', 'Dashboard') — Acalis Pharma</title>
    @include('partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="vx-app font-sans antialiased" x-data :class="{ 'vx-sidebar-is-collapsed': $store.sidebar.collapsed }">
    <div class="drawer lg:drawer-open">
        <input id="main-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex min-h-screen flex-col">
            <header class="vx-header navbar sticky top-0 z-30 min-h-[4.25rem] px-4 lg:px-6">
                <div class="flex-none hidden lg:block">
                    <button
                        type="button"
                        class="btn btn-ghost btn-square"
                        @click="$store.sidebar.toggle()"
                        :aria-label="$store.sidebar.collapsed ? 'Expandir menú lateral' : 'Contraer menú lateral'"
                        :title="$store.sidebar.collapsed ? 'Expandir menú' : 'Contraer menú'"
                    >
                        <svg x-show="!$store.sidebar.collapsed" xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <svg x-show="$store.sidebar.collapsed" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-none lg:hidden">
                    <label for="main-drawer" class="btn btn-square btn-ghost" aria-label="Abrir menú">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </label>
                </div>

                <div class="flex-1">
                    <h1 class="text-lg font-semibold text-base-content">@yield('page-title', 'Acalis Pharma')</h1>
                    <p class="text-xs text-base-content/55">@yield('page-subtitle', 'Gestión farmacéutica para residencias')</p>
                </div>

                <div class="flex flex-none items-center gap-2 sm:gap-3">
                    <x-ui.theme-toggle />

                    @auth
                        <livewire:notification-bell />
                    @endauth

                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="flex items-center gap-2 rounded-xl px-2 py-1 hover:bg-base-200">
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-medium leading-tight">{{ auth()->user()->display_name }}</p>
                                <p class="text-xs text-base-content/55">{{ auth()->user()->role?->label() ?? 'Personal' }}</p>
                            </div>
                            <x-ui.avatar :name="auth()->user()->display_name" size="md" ring />
                        </div>
                        <ul tabindex="0" class="menu dropdown-content z-50 mt-3 w-56 rounded-box border border-base-300 bg-base-100 p-2 shadow-xl">
                            <li class="menu-title">{{ auth()->user()->display_name }}</li>
                            <li><a href="{{ route('profile.edit') }}">Mi perfil</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left">Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="vx-main flex-1 p-4 md:p-6 lg:p-8">
                @if (session('status'))
                    <div class="alert alert-success mb-5 shadow-sm">{{ session('status') }}</div>
                @endif
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>

        <div class="drawer-side z-40">
            <label for="main-drawer" class="drawer-overlay" aria-label="Cerrar menú"></label>
            @include('partials.sidebar')
        </div>
    </div>

    @livewireScripts
    @stack('scripts')

    @auth
        <x-session-idle-modal />
    @endauth
</body>
</html>
