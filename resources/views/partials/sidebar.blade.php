@php
    $navItems = [
        ['route' => 'dashboard', 'pattern' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'section' => 'Principal'],
        ['route' => 'inventory.index', 'pattern' => 'inventory.*', 'label' => 'Inventario', 'icon' => 'box', 'section' => 'Operaciones'],
        ['route' => 'pharmacies.index', 'pattern' => 'pharmacies.*', 'label' => 'Bodegas', 'icon' => 'warehouse'],
        ['route' => 'residents.index', 'pattern' => 'residents.*', 'label' => 'Residentes', 'icon' => 'users'],
        ['route' => 'reports.index', 'pattern' => 'reports.*', 'label' => 'Reportes', 'icon' => 'chart'],
    ];

    if (auth()->user()?->can('users.manage')) {
        $navItems = array_merge($navItems, [
            ['route' => 'users.index', 'pattern' => 'users.*', 'label' => 'Usuarios', 'icon' => 'user-cog', 'section' => 'Administración'],
            ['route' => 'roles.index', 'pattern' => 'roles.*', 'label' => 'Roles', 'icon' => 'shield'],
            ['route' => 'audit.index', 'pattern' => 'audit.*', 'label' => 'Auditoría', 'icon' => 'clipboard'],
        ]);
    }

    $navItems[] = ['route' => 'support', 'pattern' => 'support', 'label' => 'Soporte', 'icon' => 'help', 'section' => 'Ayuda'];

    $currentSection = null;
@endphp

<aside class="vx-sidebar flex min-h-full flex-col">
    <div class="vx-sidebar-header border-b border-[var(--vx-sidebar-border)] px-4 py-4">
        <div class="flex items-center justify-between gap-2">
            <a href="{{ route('dashboard') }}" class="vx-sidebar-brand flex min-w-0 flex-1 items-center gap-3" title="Acalis Pharma">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-content shadow-lg shadow-primary/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <div class="vx-sidebar-brand-text min-w-0 overflow-hidden">
                    <p class="truncate text-base font-bold leading-tight" style="color: var(--vx-sidebar-active-text);">Acalis Pharma</p>
                    <p class="truncate text-xs opacity-70">Gestión farmacéutica</p>
                </div>
            </a>
            <button
                type="button"
                class="vx-sidebar-collapse-btn btn btn-ghost btn-square btn-sm hidden shrink-0 lg:inline-flex"
                @click="$store.sidebar.toggle()"
                :aria-label="$store.sidebar.collapsed ? 'Expandir menú' : 'Contraer menú'"
                :title="$store.sidebar.collapsed ? 'Expandir menú' : 'Contraer menú'"
            >
                <svg x-show="!$store.sidebar.collapsed" xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <svg x-show="$store.sidebar.collapsed" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>

    <nav class="flex-1 overflow-x-hidden overflow-y-auto py-2">
        @foreach ($navItems as $item)
            @if(($item['section'] ?? null) && $item['section'] !== $currentSection)
                @php $currentSection = $item['section']; @endphp
                <p class="vx-nav-section vx-sidebar-label">{{ $currentSection }}</p>
            @endif
            <a href="{{ route($item['route']) }}"
               title="{{ $item['label'] }}"
               :data-tip="$store.sidebar.collapsed ? '{{ $item['label'] }}' : ''"
               :class="{ 'tooltip tooltip-right': $store.sidebar.collapsed }"
               @class(['vx-nav-link', 'active' => request()->routeIs($item['pattern'])])>
                @include('partials.nav-icon', ['name' => $item['icon']])
                <span class="vx-sidebar-label truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="vx-sidebar-footer border-t border-[var(--vx-sidebar-border)] p-3">
        <div class="vx-sidebar-help rounded-xl p-3 text-sm lg:p-4" style="background: var(--vx-sidebar-hover);">
            <p class="vx-sidebar-label font-semibold" style="color: var(--vx-sidebar-active-text);">Centro de ayuda</p>
            <p class="vx-sidebar-label mt-1 text-xs opacity-80">Soporte clínico y técnico 24/7</p>
            <a href="{{ route('support') }}"
               title="Centro de ayuda"
               :data-tip="$store.sidebar.collapsed ? 'Centro de ayuda' : ''"
               :class="{ 'tooltip tooltip-right': $store.sidebar.collapsed }"
               class="btn btn-primary btn-sm vx-sidebar-help-btn mt-3 w-full">
                <span class="vx-sidebar-label">Contactar</span>
                <svg class="vx-sidebar-help-icon hidden size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </a>
        </div>
    </div>
</aside>
