<x-app-layout>
  <x-slot name="header">
    <h2 class="text-lg font-semibold text-gray-800">Módulos activos</h2>
  </x-slot>

  <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">

    @if (session('status') === 'modules-updated')
      <div class="mb-6 flex items-center gap-3 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        <svg class="size-5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Módulos actualizados correctamente.
      </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-5 border-b border-gray-100">
        <p class="text-sm text-gray-500">
          Activa o desactiva las secciones que aparecen en el menú lateral.
          Los cambios aplican a todos los miembros del equipo.
        </p>
      </div>

      <form method="POST" action="{{ route('team.modules.update', $team) }}">
        @csrf
        @method('PUT')

        <ul class="divide-y divide-gray-100">
          @foreach ($modules as $module)
            <li class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-gray-50 transition">
              <div class="min-w-0">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-gray-900">{{ $module['label'] }}</span>
                  @if ($module['admin_only'])
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-600 ring-1 ring-inset ring-indigo-200">
                      Admin
                    </span>
                  @endif
                </div>
                <p class="mt-0.5 text-xs text-gray-500">{{ $module['desc'] }}</p>
              </div>

              {{-- Toggle switch --}}
              <label class="relative inline-flex items-center cursor-pointer shrink-0"
                     title="{{ $module['label'] }}">
                <input type="checkbox"
                       name="{{ $module['key'] }}"
                       value="1"
                       class="sr-only peer"
                       {{ $team->moduleEnabled($module['key']) ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-200 rounded-full peer
                            peer-checked:bg-indigo-500
                            peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:rounded-full after:h-5 after:w-5
                            after:transition-all
                            peer-checked:after:translate-x-full">
                </div>
              </label>
            </li>
          @endforeach
        </ul>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
          <button type="submit"
                  class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Guardar cambios
          </button>
        </div>
      </form>
    </div>

  </div>
</x-app-layout>
