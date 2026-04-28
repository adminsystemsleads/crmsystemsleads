{{-- Drawer lateral desplegable con un solo botón hamburguesa --}}
<div x-data="{ open:false }"
     x-init="$watch('open', v => document.documentElement.classList.toggle('overflow-hidden', v))"
     class="relative">

  {{-- Fondo oscuro al abrir --}}
  <div x-cloak x-show="open" x-transition.opacity
       class="fixed inset-0 z-40 bg-black/45"
       @click="open=false"></div>

  {{-- Drawer --}}
  <aside x-cloak
         x-show="open"
         x-transition:enter="transform transition ease-out duration-200"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in duration-150"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed z-50 inset-y-0 left-0
                w-[90vw] max-w-[340px] lg:max-w-[380px]
                bg-white text-gray-800 border-r border-gray-200
                shadow-2xl rounded-r-2xl flex flex-col">

    {{-- Header --}}
    <div class="h-14 px-3 flex items-center gap-3 border-b border-gray-200 bg-white/95">
      <button @click="open=false"
              class="p-2 rounded-md hover:bg-gray-100"
              aria-label="Cerrar menú">
        <svg class="w-7 h-7" stroke="currentColor" fill="none" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      @php
        $teamName = (Laravel\Jetstream\Jetstream::hasTeamFeatures() && Auth::user()?->currentTeam?->name)
          ? Auth::user()->currentTeam->name
          : config('app.name', 'Laravel');
      @endphp
      <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0" title="{{ $teamName }}">
        <div class="h-8 w-8 rounded-full bg-indigo-500/90 ring-2 ring-indigo-100"></div>
        <span class="text-sm font-semibold tracking-wide truncate">{{ $teamName }}</span>
      </a>
    </div>

    {{-- Menú principal --}}
    @php
      $links = [
        [
  'name'   => __('Mi Perfil de Unidad'),
  'route'  => 'perfil-unidad.edit',
  'active' => request()->routeIs('perfil-unidad.*'),
  'icon'   => '<svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A7 7 0 1118.88 6.196 7 7 0 015.12 17.804zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
],
          [
              'name'   => __('Mis Finanzas'),
              'route'  => 'finanzas.index',
              'active' => request()->routeIs('finanzas.index'),
              'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v5h6v-5c0-1.657-1.343-3-3-3zM4 21h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
          ],
          [
              'name'   => __('Pagos'),
              'route'  => 'pagos',
              'active' => request()->routeIs('pagos'),
              'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m4-5l-2 2m0 0l-2-2m2 2V3"/></svg>'
          ],
          [
              'name'   => __('Transparencia IA'),
              'route'  => 'transparencia.ia.index',
              'active' => request()->routeIs('transparencia.ia.index'),
              'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
          ],
          
      ];
    @endphp

    <nav class="p-3 space-y-1 overflow-y-auto grow">

    {{-- Solo visible para administradores --}}
    @if (Auth::user()->hasTeamRole(Auth::user()->currentTeam, 'admin'))
        <a href="{{ route('gastos.import.create') }}"
           class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm transition select-none
                  {{ request()->routeIs('gastos.import.create') ? 'bg-gray-100 text-gray-900' : 'hover:bg-gray-100 text-gray-700' }}">
            {{-- Icono --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" class="size-5 text-gray-500">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v16h16V4H4zm4 8h8m-4-4v8"/>
            </svg>
            <span class="truncate">Importar Reporte Mensual</span>
        </a>

        <a href="{{ route('gastos.index') }}"
           class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm transition select-none
                  {{ request()->routeIs('gastos.index') ? 'bg-gray-100 text-gray-900' : 'hover:bg-gray-100 text-gray-700' }}">
            {{-- Icono --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
     stroke-width="2" stroke="currentColor" class="size-5 text-gray-500">
  <path stroke-linecap="round" stroke-linejoin="round"
        d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
</svg>

            <span class="truncate">Lista Gastos</span>
        </a>

        <a href="{{ route('team.perfiles.index') }}"
           class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm transition select-none
                  {{ request()->routeIs('team.perfiles.index') ? 'bg-gray-100 text-gray-900' : 'hover:bg-gray-100 text-gray-700' }}">
            {{-- Icono --}}
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" class="size-5 text-gray-500">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4.5 19.5a7.5 7.5 0 0 1 15 0" />
    </svg>
            <span class="truncate">Perfiles</span>
        </a>

        <a href="{{ route('categorias.index') }}"
           class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm transition select-none
                  {{ request()->routeIs('categorias.index') ? 'bg-gray-100 text-gray-900' : 'hover:bg-gray-100 text-gray-700' }}">
            {{-- Icono --}}
           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span class="truncate">Categorías de Pago</span>
        </a>


        @php
            $teamId = Auth::user()?->currentTeam?->id;
        @endphp

        @if ($teamId)
            <a href="{{ route('team.license.form', ['team' => Auth::user()->currentTeam->id]) }}" 
              class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm transition select-none
                      {{ request()->routeIs('team.license.form') ? 'bg-gray-100 text-gray-900' : 'hover:bg-gray-100 text-gray-700' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="size-5">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M12 2l8 4v5c0 5.25-3.25 10-8 11-4.75-1-8-5.75-8-11V6l8-4zM9 12l2 2 4-4"/>
                </svg>

                <span class="truncate">Licencia</span>
            </a>

        @endif

        
    @endif

    {{-- Menú general --}}
    @foreach ($links as $link)
        <a href="{{ route($link['route']) }}"
           class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm transition select-none
                  {{ $link['active'] ? 'bg-gray-100 text-gray-900' : 'hover:bg-gray-100 text-gray-700' }}">
            {!! $link['icon'] !!}
            <span class="truncate">{{ $link['name'] }}</span>
        </a>
    @endforeach

</nav>

    {{-- Manage Team --}}
    @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
      <div class="px-3 pt-3 border-t border-gray-200">
        <div class="text-[11px] uppercase tracking-wide text-gray-500 mb-2">Condominio</div>
        <x-dropdown align="left" width="60">
          <x-slot name="trigger">
            <button type="button"
                    class="w-full inline-flex items-center gap-2 px-3 py-2 text-sm rounded-md hover:bg-gray-100 text-gray-700">
              <svg class="size-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5V4H2v16h5M7 20V10h10v10"/>
              </svg>
              <span class="truncate">{{ Auth::user()->currentTeam->name }}</span>
            </button>
          </x-slot>
          <x-slot name="content">
            <div class="w-60">
              <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                {{ __('Team Settings') }}
              </x-dropdown-link>
              @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                <x-dropdown-link href="{{ route('teams.create') }}">
                  {{ __('Create New Team') }}
                </x-dropdown-link>
              @endcan
              @if (Auth::user()->allTeams()->count() > 1)
                <div class="border-t border-gray-200 my-2"></div>
                <div class="block px-4 py-2 text-xs text-gray-500">{{ __('Switch Teams') }}</div>
                @foreach (Auth::user()->allTeams() as $team)
                  <x-switchable-team :team="$team" />
                @endforeach
              @endif
            </div>
          </x-slot>
        </x-dropdown>
      </div>
    @endif

    {{-- Perfil / Logout --}}
    <div class="p-3 space-y-1 border-t border-gray-200">
      <a href="{{ route('profile.show') }}"
         class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
          <img class="size-7 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
        @else
          <div class="size-7 rounded-full bg-gray-200"></div>
        @endif
        <div class="min-w-0">
          <div class="font-medium text-gray-900 truncate">{{ Auth::user()->name }}</div>
          <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
        </div>
      </a>
      <form method="POST" action="{{ route('logout') }}" x-data>
        @csrf
        <button class="w-full flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-100 text-sm text-gray-700"
                @click.prevent="$root.submit();">
          <svg class="size-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/>
          </svg>
          <span>Log Out</span>
        </button>
      </form>
    </div>
  </aside>

  {{-- Botón hamburguesa para abrir --}}
  <div class="fixed top-4 left-4 z-50"
       x-cloak
       x-show="!open"
       x-transition.opacity>
    <button @click="open = true"
            class="p-3 rounded-lg bg-white text-gray-700 border border-gray-300 shadow-md hover:bg-gray-50"
            aria-label="Abrir menú">
      <svg class="w-8 h-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>
</div>
