<x-app-layout>
  <div class="w-full pt-6 pb-8 px-6 space-y-4">

    {{-- Encabezado: botón de despliegue en la misma línea del título --}}
    <div class="flex items-center justify-between" style="min-height:40px;">
      <div class="flex items-center gap-3">
        <button x-show="!$store.sidebar.open" @click="$store.sidebar.toggle()"
                class="shrink-0 p-1.5 rounded-lg text-gray-600 border border-gray-300 hover:bg-gray-100 transition"
                title="Mostrar menú" style="display:none;">
          <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Reporte de Cuentas
        </h2>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.accounts.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition"
           style="background-color:#2563eb;">
          <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Actualizar lista
        </a>
        <a href="{{ route('admin.license-codes.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50 transition">
          <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Volver a códigos
        </a>
      </div>
    </div>

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
        <table class="w-full text-xs">
          <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
            <tr>
              <th onclick="sortTable(this)" class="text-left px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">ID<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-left px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">Cuenta<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-left px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">Correo creador<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-left px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">Creada<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-left px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">1ª licencia<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-left px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">Inicio actual<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-left px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">Vence<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-left px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">Estado<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-left px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">Última nota<span class="sort-arrow"></span></th>
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
                  <span class="inline-flex items-center rounded-full font-bold whitespace-nowrap"
                        style="{{ $estadoStyle }}padding:5px 12px;font-size:11.5px;line-height:1;">
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
                      <button type="submit" class="w-full text-xs px-2.5 py-1 rounded-md text-white" style="background-color:#2563eb;">
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

  <script>
    function sortTable(th) {
      const table = th.closest('table');
      const tbody = table.tBodies[0];
      if (!tbody) return;
      const idx = th.cellIndex;
      const dir = th.getAttribute('data-dir') === 'asc' ? 'desc' : 'asc';

      // Limpia indicadores de las demás columnas
      table.querySelectorAll('thead th').forEach(h => {
        if (h !== th) { h.removeAttribute('data-dir'); const a = h.querySelector('.sort-arrow'); if (a) a.textContent = ''; }
      });
      th.setAttribute('data-dir', dir);
      const arrow = th.querySelector('.sort-arrow');
      if (arrow) arrow.textContent = dir === 'asc' ? ' ▲' : ' ▼';

      const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelectorAll('td').length > 1);
      const val = (r) => { const c = r.children[idx]; return c ? c.innerText.trim() : ''; };
      const isNum = (s) => /^-?\d+(\.\d+)?$/.test(s);

      rows.sort((a, b) => {
        const x = val(a), y = val(b);
        const ex = (x === '' || x === '—'), ey = (y === '' || y === '—');
        if (ex && ey) return 0;
        if (ex) return 1;   // vacíos siempre al final
        if (ey) return -1;
        let cmp;
        if (isNum(x) && isNum(y)) cmp = parseFloat(x) - parseFloat(y);
        else cmp = x.localeCompare(y, 'es', { numeric: true });
        return dir === 'asc' ? cmp : -cmp;
      });

      rows.forEach(r => tbody.appendChild(r));
    }
  </script>
</x-app-layout>
