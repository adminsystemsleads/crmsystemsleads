{{-- Sidebar responsive: fija en desktop, drawer en mobile --}}
@php
  $team = Auth::user()->currentTeam;

  $teamName = (Laravel\Jetstream\Jetstream::hasTeamFeatures() && $team?->name)
    ? $team->name
    : config('app.name', 'Laravel');

  $isAdmin = $team && Auth::user()->hasTeamRole($team, 'admin');

  // Pipelines marcados como acceso rápido que el usuario puede ver
  $navPipelines = $team
    ? \App\Models\Pipeline::where('team_id', $team->id)
        ->where('show_in_nav', true)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get()
        ->filter(fn($p) => $p->userCan(Auth::user(), 'view'))
    : collect();

  $links = [
    [
      'key'    => 'perfil_unidad',
      'name'   => __('Mi Perfil de Unidad'),
      'route'  => 'perfil-unidad.edit',
      'active' => request()->routeIs('perfil-unidad.*'),
      'icon'   => '<svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A7 7 0 1118.88 6.196 7 7 0 015.12 17.804zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    ],
    [
      'key'    => 'finanzas',
      'name'   => __('Mis Finanzas'),
      'route'  => 'finanzas.index',
      'active' => request()->routeIs('finanzas.index'),
      'icon'   => '<svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v5h6v-5c0-1.657-1.343-3-3-3zM4 21h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
    ],
    [
      'key'    => 'pagos',
      'name'   => __('Pagos'),
      'route'  => 'pagos',
      'active' => request()->routeIs('pagos'),
      'icon'   => '<svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m4-5l-2 2m0 0l-2-2m2 2V3"/></svg>',
    ],
    [
      'key'    => 'transparencia_ia',
      'name'   => __('Transparencia IA'),
      'route'  => 'transparencia.ia.index',
      'active' => request()->routeIs('transparencia.ia.index'),
      'icon'   => '<svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    ],
    [
      'key'    => 'crm',
      'name'   => __('CRM'),
      'route'  => 'pipelines.index',
      'active' => request()->routeIs('pipelines.*') || request()->routeIs('deals.*') || request()->routeIs('products.*') || request()->routeIs('invoices.*') || request()->routeIs('invoice-config.*'),
      'icon'   => '<svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
    ],
    [
      'key'    => 'contactos',
      'name'   => __('Contactos'),
      'route'  => 'contacts.index',
      'active' => request()->routeIs('contacts.*'),
      'icon'   => '<svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    ],
    [
      'key'    => 'whatsapp_inbox',
      'name'   => __('WhatsApp'),
      'route'  => 'whatsapp.inbox.index',
      'active' => request()->routeIs('whatsapp.inbox.*'),
      'icon'   => '<svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>',
    ],
  ];
@endphp

<div x-data="{ open: false }"
     x-init="$watch('open', v => document.documentElement.classList.toggle('overflow-hidden', v))">

  {{-- Overlay solo mobile --}}
  <div x-show="open"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       @click="open = false"
       class="fixed inset-0 z-40 bg-black/45 lg:hidden"
       style="display:none;"></div>

  {{-- Sidebar --}}
  <aside :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
         class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col
                bg-white border-r border-gray-200 shadow-lg
                transition-transform duration-200 ease-in-out">

    {{-- Cabecera --}}
    <div class="h-16 px-4 flex items-center justify-between border-b border-gray-200 bg-white shrink-0">
      <a href="{{ route('dashboard') }}"
         class="flex items-center gap-3 min-w-0"
         title="{{ $teamName }}">
        <div class="size-8 rounded-full bg-indigo-500 ring-2 ring-indigo-100 shrink-0"></div>
        <span class="text-sm font-semibold tracking-wide truncate text-gray-900">{{ $teamName }}</span>
      </a>
      <button @click="open = false"
              class="lg:hidden p-1.5 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition"
              aria-label="Cerrar menú">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    {{-- Navegación --}}
    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

      {{-- Sección Admin --}}
      @if ($isAdmin)
        <p class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">Administración</p>

        @if ($team->moduleEnabled('gastos_import'))
          <a href="{{ route('gastos.import.create') }}"
             class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition select-none
                    {{ request()->routeIs('gastos.import.create') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="size-5 shrink-0 {{ request()->routeIs('gastos.import.create') ? 'text-indigo-500' : 'text-gray-400' }}"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span class="truncate">Importar Reporte</span>
          </a>
        @endif

        @if ($team->moduleEnabled('gastos'))
          <a href="{{ route('gastos.index') }}"
             class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition select-none
                    {{ request()->routeIs('gastos.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="size-5 shrink-0 {{ request()->routeIs('gastos.index') ? 'text-indigo-500' : 'text-gray-400' }}"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
            </svg>
            <span class="truncate">Lista Gastos</span>
          </a>
        @endif

        @if ($team->moduleEnabled('perfiles'))
          <a href="{{ route('team.perfiles.index') }}"
             class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition select-none
                    {{ request()->routeIs('team.perfiles.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="size-5 shrink-0 {{ request()->routeIs('team.perfiles.index') ? 'text-indigo-500' : 'text-gray-400' }}"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4.5 19.5a7.5 7.5 0 0 1 15 0"/>
            </svg>
            <span class="truncate">Perfiles</span>
          </a>
        @endif

        @if ($team->moduleEnabled('categorias'))
          <a href="{{ route('categorias.index') }}"
             class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition select-none
                    {{ request()->routeIs('categorias.index') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="size-5 shrink-0 {{ request()->routeIs('categorias.index') ? 'text-indigo-500' : 'text-gray-400' }}"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <span class="truncate">Categorías de Pago</span>
          </a>
        @endif

        @if ($team->moduleEnabled('whatsapp_cuentas'))
          <a href="{{ route('whatsapp.accounts.index') }}"
             class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition select-none
                    {{ request()->routeIs('whatsapp.accounts.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="size-5 shrink-0 {{ request()->routeIs('whatsapp.accounts.*') ? 'text-indigo-500' : 'text-gray-400' }}"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 18h.01M8 21h8a2 2 0 002-2v-1a5 5 0 00-10 0v1a2 2 0 002 2zM12 3a4 4 0 100 8 4 4 0 000-8z"/>
            </svg>
            <span class="truncate">WhatsApp Cuentas</span>
          </a>
        @endif

        @php $teamId = $team?->id; @endphp
        @if ($teamId)
          <a href="{{ route('team.license.form', ['team' => $teamId]) }}"
             class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition select-none
                    {{ request()->routeIs('team.license.form') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="size-5 shrink-0 {{ request()->routeIs('team.license.form') ? 'text-indigo-500' : 'text-gray-400' }}"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 2l8 4v5c0 5.25-3.25 10-8 11-4.75-1-8-5.75-8-11V6l8-4zM9 12l2 2 4-4"/>
            </svg>
            <span class="truncate">Licencia</span>
          </a>

          <a href="{{ route('team.modules.edit', ['team' => $teamId]) }}"
             class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition select-none
                    {{ request()->routeIs('team.modules.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
            <svg class="size-5 shrink-0 {{ request()->routeIs('team.modules.*') ? 'text-indigo-500' : 'text-gray-400' }}"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="truncate">Módulos</span>
          </a>
        @endif

        <div class="my-2 border-t border-gray-100"></div>
        <p class="px-3 pt-1 pb-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">General</p>
      @endif

      {{-- Menú general (filtrado por módulos) --}}
      @foreach ($links as $link)
        @if ($team->moduleEnabled($link['key']))
          <a href="{{ route($link['route']) }}"
             class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition select-none
                    {{ $link['active'] ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
            <span class="{{ $link['active'] ? 'text-indigo-500' : 'text-gray-400' }}">{!! $link['icon'] !!}</span>
            <span class="truncate">{{ $link['name'] }}</span>
          </a>

          {{-- Sub-links bajo CRM --}}
          @if ($link['key'] === 'crm')
            <a href="{{ route('products.index') }}"
               class="flex items-center gap-2 rounded-lg pl-10 pr-3 py-1.5 transition select-none
                      {{ request()->routeIs('products.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700' }}">
              <svg class="shrink-0 {{ request()->routeIs('products.*') ? 'text-indigo-400' : 'text-gray-400' }}"
                   style="width:12px;height:12px;min-width:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
              </svg>
              <span class="truncate text-xs">Productos</span>
            </a>
            <a href="{{ route('invoices.index') }}"
               class="flex items-center gap-2 rounded-lg pl-10 pr-3 py-1.5 transition select-none
                      {{ request()->routeIs('invoices.*') || request()->routeIs('invoice-config.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700' }}">
              <svg class="shrink-0 {{ request()->routeIs('invoices.*') || request()->routeIs('invoice-config.*') ? 'text-indigo-400' : 'text-gray-400' }}"
                   style="width:12px;height:12px;min-width:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
              </svg>
              <span class="truncate text-xs">Facturas</span>
            </a>
          @endif

          {{-- Accesos rápidos al Kanban bajo el módulo CRM --}}
          @if ($link['key'] === 'crm' && $navPipelines->isNotEmpty())
            @foreach ($navPipelines as $navPipeline)
              @php
                $kanbanActive = request()->routeIs('pipelines.kanban') && request()->route('pipeline')?->id === $navPipeline->id;
              @endphp
              <a href="{{ route('pipelines.kanban', $navPipeline) }}"
                 class="flex items-center gap-2 rounded-lg pl-10 pr-3 py-1.5 transition select-none
                        {{ $kanbanActive ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="shrink-0 {{ $kanbanActive ? 'text-indigo-400' : 'text-gray-400' }}"
                     style="width:12px;height:12px;min-width:12px;">
                  <rect x="3" y="3" width="5" height="14" rx="1"/>
                  <rect x="10" y="3" width="5" height="9" rx="1"/>
                  <rect x="17" y="3" width="5" height="11" rx="1"/>
                </svg>
                <span class="truncate text-xs">{{ $navPipeline->name }}</span>
              </a>
            @endforeach
          @endif
        @endif
      @endforeach

    </nav>

    {{-- Condominio / Team switcher --}}
    @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
      <div class="px-3 py-2 border-t border-gray-100 shrink-0 relative"
           x-data="{ openTeam: false }" @click.away="openTeam = false">
        <p class="px-2 py-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">Condominio</p>

        <button type="button" @click="openTeam = !openTeam"
                class="w-full flex items-center gap-2 px-2 py-2 text-sm rounded-lg hover:bg-gray-100 text-gray-700 transition">
          <svg class="size-4 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5V4H2v16h5M7 20V10h10v10"/>
          </svg>
          <span class="truncate text-sm">{{ Auth::user()->currentTeam->name }}</span>
          <svg class="size-4 shrink-0 text-gray-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
          </svg>
        </button>

        {{-- Drop-up: se abre hacia arriba (estilos inline para no depender de Tailwind compile) --}}
        <div x-show="openTeam"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="bg-white rounded-lg shadow-xl ring-1 ring-black/5 overflow-y-auto py-1"
             style="display: none; position: absolute; bottom: calc(100% + 4px); left: 0.75rem; right: 0.75rem; z-index: 60; max-height: 20rem;">

          <a href="{{ route('teams.show', Auth::user()->currentTeam->id) }}"
             class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
            {{ __('Team Settings') }}
          </a>
          @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
            <a href="{{ route('teams.create') }}"
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
              {{ __('Create New Team') }}
            </a>
          @endcan

          @php $allTeams = Auth::user()->allTeams(); @endphp
          @if ($allTeams->count() > 1)
            <div class="border-t border-gray-200 my-1"></div>
            <div class="block px-4 py-1.5 text-[10px] font-semibold uppercase tracking-widest text-gray-400">
              {{ __('Switch Teams') }}
            </div>
            @foreach ($allTeams as $teamItem)
              <form method="POST" action="{{ route('current-team.update') }}">
                @csrf @method('PUT')
                <input type="hidden" name="team_id" value="{{ $teamItem->id }}">
                <button type="submit"
                        class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 transition
                               {{ Auth::user()->isCurrentTeam($teamItem) ? 'text-indigo-700 font-semibold bg-indigo-50' : 'text-gray-700' }}">
                  @if (Auth::user()->isCurrentTeam($teamItem))
                    <svg class="size-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                  @else
                    <span class="size-4 shrink-0"></span>
                  @endif
                  <span class="truncate">{{ $teamItem->name }}</span>
                </button>
              </form>
            @endforeach
          @endif
        </div>
      </div>
    @endif

    {{-- Idioma --}}
    <div class="px-3 py-2 border-t border-gray-100 shrink-0">
      <p class="px-2 py-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Language') }}</p>
      <x-language-switcher variant="sidebar" />
    </div>

    {{-- Perfil / Logout --}}
    <div class="p-3 border-t border-gray-200 shrink-0 space-y-0.5">
      <a href="{{ route('profile.show') }}"
         class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 text-sm text-gray-700 transition">
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
          <img class="size-8 rounded-full object-cover ring-2 ring-gray-200 shrink-0"
               src="{{ Auth::user()->profile_photo_url }}"
               alt="{{ Auth::user()->name }}">
        @else
          <div class="size-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
            <span class="text-indigo-600 text-xs font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
          </div>
        @endif
        <div class="min-w-0">
          <div class="font-medium text-gray-900 truncate text-sm">{{ Auth::user()->name }}</div>
          <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
        </div>
      </a>

      <form method="POST" action="{{ route('logout') }}" x-data>
        @csrf
        <button type="button"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-50 hover:text-red-600 text-sm text-gray-700 transition"
                @click.prevent="$root.submit()">
          <svg class="size-5 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/>
          </svg>
          <span>Cerrar sesión</span>
        </button>
      </form>
    </div>

  </aside>

  {{-- Botón hamburguesa (solo mobile, siempre visible) --}}
  <button @click="open = true"
          class="lg:hidden fixed top-4 left-4 z-30 p-2 bg-white text-gray-700 rounded-lg border border-gray-300 shadow-md hover:bg-gray-50 transition"
          aria-label="Abrir menú">
    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>

</div>
