<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Perfiles de Miembros del Condominio') }}
            </h2>

            {{-- Botón Configuración (admin del team) --}}
            <div x-data="{ openCfg: false }" @click.away="openCfg = false" class="relative">
                <button type="button" @click="openCfg = !openCfg"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition"
                        title="Configuración">
                    <svg style="width:18px; height:18px; min-width:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Configuración</span>
                    <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                    </svg>
                </button>

                <div x-show="openCfg"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute right-0 mt-1 w-64 bg-white rounded-lg shadow-xl ring-1 ring-black/5 py-1 z-50"
                     style="display: none;">
                    <a href="{{ route('team.crm-roles.index') }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Permisos de Acceso CRM</span>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="py-8">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow sm:rounded-lg">
        <div class="p-4 border-b">
          <p class="text-sm text-gray-600">Miembros del team actual y sus perfiles.</p>
        </div>

        <div class="divide-y">
          @forelse ($perfiles as $p)
            <div class="p-4 flex items-center justify-between">
              <div>
                <div class="font-medium text-gray-800">{{ $p->user->name }}</div>
                <div class="text-sm text-gray-500">
                  Perfil: <strong>{{ $p->perfil ?? '—' }}</strong> ·
                  Unidad: <strong>{{ $p->unidad ?? '—' }}</strong> ·
                  Correo: <strong>{{ $p->correo ?? '—' }}</strong> ·
                  Tel: <strong>{{ $p->telefono ?? '—' }}</strong>
                </div>
                @if ($p->notas)
                  <div class="text-xs text-gray-500 mt-1">Notas: {{ $p->notas }}</div>
                @endif
              </div>
              <a href="{{ route('perfil-unidad.edit') }}"
                 class="text-indigo-600 hover:text-indigo-700 text-sm">Ver/Editar</a>
            </div>
          @empty
            <div class="p-6 text-gray-500">No hay perfiles aún.</div>
          @endforelse
        </div>

        <div class="p-4">
          {{ $perfiles->links() }}
        </div>
      </div>
    </div>
  </div>
        </div>
    </div>
</x-app-layout>


