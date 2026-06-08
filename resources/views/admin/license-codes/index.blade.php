<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Generar Códigos de Licencia
      </h2>
      <a href="{{ route('admin.accounts.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition"
         style="background-color:#2563eb;">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m4 6V7m4 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        Reporte de cuentas
      </a>
    </div>
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
              <option value="trial_3w">Prueba · 3 semanas</option>
              <option value="trial_4w">Prueba · 4 semanas</option>
            </optgroup>
            <optgroup label="Prórroga (días)">
              <option value="prorroga_7d">Prórroga · 7 días</option>
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
          <button class="inline-flex items-center gap-2 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition" style="background-color:#2563eb;">
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
              <th class="text-left px-6 py-3 font-semibold">ID Cuenta</th>
              <th class="text-left px-6 py-3 font-semibold">Nota</th>
              <th class="text-right px-6 py-3 font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse ($codes as $code)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-3">
                  <div class="flex items-center gap-2" x-data="{ copied: false }">
                    <span class="font-mono font-semibold text-gray-900">{{ $code->code }}</span>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ $code->code }}'); copied = true; setTimeout(() => copied = false, 1500)"
                            class="shrink-0 text-gray-400 hover:text-indigo-600 transition"
                            title="Copiar código">
                      <svg x-show="!copied" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                      </svg>
                      <svg x-show="copied" x-cloak style="width:16px;height:16px;" class="text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                    </button>
                    <span x-show="copied" x-cloak class="text-xs text-green-600">Copiado</span>
                  </div>
                </td>
                <td class="px-6 py-3">
                  @if ($code->is_prorroga)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">Prórroga</span>
                  @elseif ($code->is_trial)
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
                <td class="px-6 py-3 font-mono text-gray-700">
                  {{ $code->redeemed_by_team_id ?? '—' }}
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
                <td colspan="7" class="px-6 py-8 text-center text-gray-400 text-sm">
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

    {{-- ===================== Periodo de Prórrogas ===================== --}}
    <div class="bg-white rounded-xl shadow border border-gray-100">
      <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-gray-900">Periodo de Prórrogas</h3>
        <p class="text-xs text-gray-500">
          Habilita un periodo de prórroga directamente con el ID de la cuenta. Útil para reactivar
          cuentas bloqueadas cuyo periodo venció (para que terminen de exportar su data).
        </p>
      </div>

      {{-- Formulario: habilitar prórroga por ID de cuenta --}}
      <div class="px-6 py-5 border-b border-gray-100">
        <form method="POST" action="{{ route('admin.prorrogas.store') }}"
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
          @csrf
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">ID de la cuenta</label>
            <input type="number" name="team_id" min="1" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-purple-500 focus:border-purple-500"
                   placeholder="Ej: 2">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Días de prórroga</label>
            <input type="number" name="days" min="1" max="60" value="7" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-purple-500 focus:border-purple-500">
          </div>
          <div class="sm:col-span-2 lg:col-span-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition" style="background-color:#2563eb;">
              <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Activar prórroga
            </button>
          </div>
        </form>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">

        {{-- Cuentas actualmente en prórroga --}}
        <div>
          <div class="px-6 py-3 bg-gray-50 flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">En prórroga</span>
            <span class="text-xs text-gray-500">vigentes</span>
          </div>
          <table class="min-w-full text-sm">
            <thead class="text-gray-500 text-xs uppercase tracking-wider">
              <tr>
                <th class="text-left px-6 py-2 font-semibold">ID</th>
                <th class="text-left px-6 py-2 font-semibold">Cuenta</th>
                <th class="text-left px-6 py-2 font-semibold">Vence</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse ($prorrogasActivas as $lic)
                @php $ptz = $lic->team?->effectiveTimezone() ?? \App\Models\Team::DEFAULT_TIMEZONE; @endphp
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-2 font-mono text-gray-700">{{ $lic->team_id }}</td>
                  <td class="px-6 py-2 text-gray-800">{{ ($lic->team?->name ?? 'Sin nombre') . ' - ' . $lic->team_id }}</td>
                  <td class="px-6 py-2 text-gray-700">
                    {{ $lic->trial_ends_at?->copy()->setTimezone($ptz)->format('Y-m-d H:i') ?? '—' }}
                  </td>
                </tr>
              @empty
                <tr><td colspan="3" class="px-6 py-6 text-center text-gray-400 text-sm">Ninguna cuenta en prórroga.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Cuentas con prórroga vencida (bloqueadas) --}}
        <div>
          <div class="px-6 py-3 bg-gray-50 flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Prórroga vencida</span>
            <span class="text-xs text-gray-500">cuenta bloqueada</span>
          </div>
          <table class="min-w-full text-sm">
            <thead class="text-gray-500 text-xs uppercase tracking-wider">
              <tr>
                <th class="text-left px-6 py-2 font-semibold">ID</th>
                <th class="text-left px-6 py-2 font-semibold">Cuenta</th>
                <th class="text-left px-6 py-2 font-semibold">Venció</th>
                <th class="text-right px-6 py-2 font-semibold">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse ($prorrogasVencidas as $lic)
                @php $ptz = $lic->team?->effectiveTimezone() ?? \App\Models\Team::DEFAULT_TIMEZONE; @endphp
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-2 font-mono text-gray-700">{{ $lic->team_id }}</td>
                  <td class="px-6 py-2 text-gray-800">{{ ($lic->team?->name ?? 'Sin nombre') . ' - ' . $lic->team_id }}</td>
                  <td class="px-6 py-2 text-gray-700">
                    {{ $lic->trial_ends_at?->copy()->setTimezone($ptz)->format('Y-m-d H:i') ?? '—' }}
                  </td>
                  <td class="px-6 py-2">
                    <form method="POST" action="{{ route('admin.prorrogas.store') }}" class="flex items-center justify-end gap-1">
                      @csrf
                      <input type="hidden" name="team_id" value="{{ $lic->team_id }}">
                      <input type="number" name="days" min="1" max="60" value="7"
                             class="w-16 border border-gray-300 rounded-md px-2 py-1 text-xs">
                      <button type="submit" class="text-xs px-2.5 py-1 rounded-md text-white transition" style="background-color:#2563eb;">
                        Reactivar
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" class="px-6 py-6 text-center text-gray-400 text-sm">Ninguna cuenta con prórroga vencida.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</x-app-layout>
