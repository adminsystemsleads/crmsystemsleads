<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Reporte de Cuentas
      </h2>
      <a href="{{ route('admin.license-codes.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50 transition">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Volver a códigos
      </a>
    </div>
  </x-slot>

  <div class="max-w-7xl mx-auto py-8 px-4 space-y-4">

    @if (session('success'))
      <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-gray-900">Todas las cuentas ({{ $teams->total() }})</h3>
        <p class="text-xs text-gray-500">Estado de licencia, fechas y acciones de administración de cada cuenta.</p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-xs whitespace-nowrap">
          <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
            <tr>
              <th class="text-left px-4 py-3 font-semibold">ID</th>
              <th class="text-left px-4 py-3 font-semibold">Cuenta</th>
              <th class="text-left px-4 py-3 font-semibold">Correo creador</th>
              <th class="text-left px-4 py-3 font-semibold">Creada</th>
              <th class="text-left px-4 py-3 font-semibold">1ª licencia</th>
              <th class="text-left px-4 py-3 font-semibold">Inicio actual</th>
              <th class="text-left px-4 py-3 font-semibold">Vence</th>
              <th class="text-left px-4 py-3 font-semibold">Estado</th>
              <th class="text-left px-4 py-3 font-semibold">Última nota</th>
              <th class="text-right px-4 py-3 font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse ($teams as $team)
              @php
                $lic = $team->license;
                $tz  = $team->effectiveTimezone();
                $fmt = fn ($d) => $d ? $d->copy()->setTimezone($tz)->format('Y-m-d H:i') : '—';

                $inicio = $lic?->active_from ?? $lic?->trial_starts_at;

                if (! $lic) {
                    $estadoLabel = 'Sin licencia'; $estadoStyle = 'background:#f3f4f6;color:#6b7280;';
                } elseif (! $lic->is_active) {
                    $estadoLabel = 'Bloqueada'; $estadoStyle = 'background:#fecaca;color:#7f1d1d;';
                } elseif ($lic->is_expired) {
                    $estadoLabel = 'Vencida'; $estadoStyle = 'background:#dc2626;color:#ffffff;';
                } else {
                    $type = $lic->grant_type ?: ($lic->active_until ? 'license' : 'trial');
                    [$estadoLabel, $estadoStyle] = match ($type) {
                        'trial'    => ['Modo de prueba', 'background:#fef08a;color:#854d0e;'],
                        'prorroga' => ['Prórroga', 'background:#fee2e2;color:#ef4444;'],
                        default    => ['Licencia activa', 'background:#dcfce7;color:#15803d;'],
                    };
                }
                $lastNote = data_get($lic, 'meta.last_note');
              @endphp
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-gray-700">{{ $team->id }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $team->name }}</td>
                <td class="px-4 py-3 text-gray-700">{{ $team->owner?->email ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $fmt($team->created_at) }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $fmt($lic?->first_started_at) }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $fmt($inicio) }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $fmt($lic?->expires_at) }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold" style="{{ $estadoStyle }}">
                    {{ $estadoLabel }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-500 truncate" style="max-width:180px;" title="{{ $lastNote }}">{{ $lastNote ?? '—' }}</td>
                <td class="px-4 py-3">
                  <div class="flex flex-col items-stretch gap-1.5" style="min-width:210px;">
                    {{-- Bloquear / Habilitar (nota obligatoria) --}}
                    <form method="POST" class="flex items-center gap-1.5">
                      @csrf
                      <input type="text" name="note" required placeholder="Nota / motivo"
                             class="flex-1 border border-gray-300 rounded-md px-2 py-1 text-xs">
                      @if ($lic && $lic->is_active)
                        <button type="submit" formaction="{{ route('admin.accounts.block', $team) }}"
                                class="text-xs px-2.5 py-1 rounded-md text-white" style="background-color:#dc2626;">
                          Bloquear
                        </button>
                      @else
                        <button type="submit" formaction="{{ route('admin.accounts.enable', $team) }}"
                                class="text-xs px-2.5 py-1 rounded-md text-white" style="background-color:#16a34a;">
                          Habilitar
                        </button>
                      @endif
                    </form>
                    {{-- Nueva prórroga de 7 días --}}
                    <form method="POST" action="{{ route('admin.prorrogas.store') }}">
                      @csrf
                      <input type="hidden" name="team_id" value="{{ $team->id }}">
                      <input type="hidden" name="days" value="7">
                      <button type="submit" class="w-full text-xs px-2.5 py-1 rounded-md text-white" style="background-color:#4f46e5;">
                        + Prórroga 7 días
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="10" class="px-6 py-10 text-center text-gray-400">No hay cuentas registradas.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($teams->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
          {{ $teams->links() }}
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
