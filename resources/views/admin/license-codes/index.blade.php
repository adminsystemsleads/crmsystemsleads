<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Generar Códigos de Licencia
    </h2>
  </x-slot>

  <div class="max-w-5xl mx-auto py-8 px-4 space-y-8">

    @if (session('success'))
      <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        {{ session('success') }}
      </div>
    @endif
    @if (session('error'))
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        {{ session('error') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Formulario de generación --}}
    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
      <h3 class="text-sm font-bold text-gray-900 mb-1">Crear un nuevo código</h3>
      <p class="text-xs text-gray-500 mb-4">
        Los códigos de <b>meses</b> activan una <b>licencia</b>; los de <b>semanas</b> activan un <b>modo de prueba</b>.
      </p>

      <form method="POST" action="{{ route('admin.license-codes.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @csrf

        <div class="lg:col-span-2">
          <label class="block text-xs font-medium text-gray-700 mb-1">Tipo y duración</label>
          <select name="preset" required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <optgroup label="Licencia (meses)">
              <option value="license_1">Licencia · 1 mes</option>
              <option value="license_3">Licencia · 3 meses</option>
              <option value="license_6">Licencia · 6 meses</option>
              <option value="license_12" selected>Licencia · 12 meses</option>
            </optgroup>
            <optgroup label="Modo de prueba (semanas)">
              <option value="trial_1w">Prueba · 1 semana</option>
              <option value="trial_2w">Prueba · 2 semanas</option>
            </optgroup>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Cantidad</label>
          <input type="number" name="quantity" min="1" max="50" value="1"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Nota (opcional)</label>
          <input type="text" name="label" maxlength="255" placeholder="Cliente / referencia"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="sm:col-span-2 lg:col-span-4">
          <button class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Generar código
          </button>
        </div>
      </form>
    </div>

    {{-- Listado de códigos --}}
    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-gray-900">Códigos generados</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
            <tr>
              <th class="text-left px-6 py-3 font-semibold">Código</th>
              <th class="text-left px-6 py-3 font-semibold">Tipo</th>
              <th class="text-left px-6 py-3 font-semibold">Duración</th>
              <th class="text-left px-6 py-3 font-semibold">Estado</th>
              <th class="text-left px-6 py-3 font-semibold">Nota</th>
              <th class="text-right px-6 py-3 font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse ($codes as $code)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 font-mono font-semibold text-gray-900">{{ $code->code }}</td>
                <td class="px-6 py-3">
                  @if ($code->is_trial)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Prueba</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Licencia</span>
                  @endif
                </td>
                <td class="px-6 py-3 text-gray-700">{{ $code->duration_label }}</td>
                <td class="px-6 py-3">
                  @if (! $code->is_active)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">Desactivado</span>
                  @elseif (! $code->is_available)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                      Canjeado{{ $code->redeemedTeam ? ' · '.$code->redeemedTeam->name : '' }}
                    </span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Disponible</span>
                  @endif
                </td>
                <td class="px-6 py-3 text-gray-500">{{ $code->label ?: '—' }}</td>
                <td class="px-6 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <form method="POST" action="{{ route('admin.license-codes.toggle', $code) }}">
                      @csrf @method('PATCH')
                      <button class="text-xs px-2.5 py-1 rounded-md border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
                        {{ $code->is_active ? 'Desactivar' : 'Activar' }}
                      </button>
                    </form>
                    <form method="POST" action="{{ route('admin.license-codes.destroy', $code) }}"
                          onsubmit="return confirm('¿Eliminar este código?');">
                      @csrf @method('DELETE')
                      <button class="text-xs px-2.5 py-1 rounded-md border border-red-200 text-red-600 hover:bg-red-50 transition">
                        Eliminar
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">
                  Aún no has generado ningún código.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($codes->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
          {{ $codes->links() }}
        </div>
      @endif
    </div>

  </div>
</x-app-layout>
