{{-- Menú de Secretaría: grupo CALIFICACIONES (Inicial) — `niveles.id` = 1. Ítems futuros (sin secundario). --}}

@if (\App\Support\Navegacion\MenuSecretariaPerfil::muestraCalificacionesInicial())
    <div class="mt-4"></div>
    <button type="button"
            class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[11px] font-semibold uppercase tracking-wide rounded-md transition-colors"
            :class="(groups.calificacionesInicial && !sidebarCollapsed) ? 'is-open' : ''"
            @click="toggleGroup('calificacionesInicial')"
            title="Calificaciones (inicial)">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">CALIFICACIONES (Inicial)</span>
        <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
             :class="groups.calificacionesInicial ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div class="mt-1 space-y-0.5 se-sidebar-group-items"
         x-show="groups.calificacionesInicial && !sidebarCollapsed"
         x-collapse
         x-cloak>
        {{-- Ítems de menú inicial: agregar aquí (rutas distintas de calificacionesSecundario.*). --}}
    </div>
@endif
