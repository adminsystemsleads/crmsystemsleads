<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Reporte de Cuentas') }}</h2>
  </x-slot>
  <div class="w-full pt-6 pb-8 px-6 space-y-4">

    {{-- Acciones --}}
    <div class="flex flex-wrap justify-end items-center gap-2">
        <a href="{{ route('admin.accounts.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition"
           style="background-color:#2563eb;">
          <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          {{ __('Actualizar lista') }}
        </a>
        <a href="{{ route('admin.license-codes.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-gray-700 border border-gray-300 hover:bg-gray-50 transition">
          <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          {{ __('Volver a códigos') }}
        </a>
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

    {{-- Notificación de cuentas eliminadas (solo en esta vista) --}}
    @if ($deletedAccounts->isNotEmpty())
      <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:14px 16px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
          <svg style="width:18px;height:18px;color:#c2410c;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
          <span style="font-weight:800;color:#9a3412;font-size:13.5px;">
            {{ $deletedAccounts->count() }} {{ __('cuenta(s) eliminada(s) — se borrarán por completo de la base a los') }} {{ \App\Models\Team::PURGE_AFTER_DAYS }} {{ __('días de su eliminación.') }}
          </span>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
          @foreach ($deletedAccounts as $da)
            @php $dl = $da->daysUntilPurge(); @endphp
            <span style="background:#ffedd5;color:#9a3412;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:600;">
              #{{ $da->id }} {{ $da->name }} — {{ __('quedan') }} {{ $dl }} {{ $dl === 1 ? __('día') : __('días') }}
            </span>
          @endforeach
        </div>
      </div>
    @endif

    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-gray-900">{{ __('Todas las cuentas') }} ({{ $teams->total() }})</h3>
        <p class="text-xs text-gray-500">{{ __('Estado de licencia, fechas y acciones de administración de cada cuenta.') }}</p>
      </div>

      {{-- Filtros (por estado y correo del creador) --}}
      <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Estado') }}</label>
          <select id="filterEstado" onchange="filterAccounts()" class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs" style="min-width:220px;">
            <option value="">{{ __('Todos') }}</option>
            <option>{{ __('Licencia activa') }}</option>
            <option>{{ __('Modo de prueba') }}</option>
            <option>{{ __('Prórroga') }}</option>
            <option>{{ __('Vencida') }}</option>
            <option>{{ __('Bloqueada') }}</option>
            <option>{{ __('Sin licencia') }}</option>
            <option>{{ __('Eliminada') }}</option>
            <option>{{ __('Eliminada permanentemente') }}</option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ __('Correo') }}</label>
          <input id="filterEmail" type="text" oninput="filterAccounts()" placeholder="{{ __('Buscar por correo…') }}"
                 class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs" style="width:240px;">
        </div>
        <button type="button"
                onclick="document.getElementById('filterEstado').value='';document.getElementById('filterEmail').value='';filterAccounts()"
                class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
          {{ __('Limpiar') }}
        </button>
        <span id="filterCount" class="text-xs text-gray-400"></span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs text-center">
          <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
            <tr>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('ID') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Cuenta') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Correo creador') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Creada') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('1ª licencia') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Inicio actual') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Vence') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Estado') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Última nota') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Fecha eliminada') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Borrado permanente') }}<span class="sort-arrow"></span></th>
              <th onclick="sortTable(this)" class="text-center px-4 py-3 font-semibold cursor-pointer select-none hover:text-gray-700">{{ __('Días p/ borrado') }}<span class="sort-arrow"></span></th>
              <th class="text-center px-4 py-3 font-semibold">{{ __('Acciones') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100" id="accountsTbody">
            @forelse ($teams as $team)
              @php
                $lic = $team->license;
                $tz  = $team->effectiveTimezone();
                $fmt = fn ($d) => $d ? $d->copy()->setTimezone($tz)->format('Y-m-d H:i') : '—';

                $inicio = $lic?->active_from ?? $lic?->trial_starts_at;
                $isDeleted = $team->trashed();
                $isPurged  = $team->isPurged();

                if ($isPurged) {
                    $estadoLabel = __('Eliminada permanentemente'); $estadoStyle = 'background:#111827;color:#ffffff;';
                } elseif ($isDeleted) {
                    $estadoLabel = __('Eliminada'); $estadoStyle = 'background:#374151;color:#ffffff;';
                } elseif (! $lic) {
                    $estadoLabel = __('Sin licencia'); $estadoStyle = 'background:#f3f4f6;color:#6b7280;';
                } elseif (! $lic->is_active) {
                    $estadoLabel = __('Bloqueada'); $estadoStyle = 'background:#fecaca;color:#7f1d1d;';
                } elseif ($lic->is_expired) {
                    $estadoLabel = __('Vencida'); $estadoStyle = 'background:#dc2626;color:#ffffff;';
                } else {
                    $type = $lic->grant_type ?: ($lic->active_until ? 'license' : 'trial');
                    [$estadoLabel, $estadoStyle] = match ($type) {
                        'trial'    => [__('Modo de prueba'), 'background:#fef08a;color:#854d0e;'],
                        'prorroga' => [__('Prórroga'), 'background:#fee2e2;color:#ef4444;'],
                        default    => [__('Licencia activa'), 'background:#dcfce7;color:#15803d;'],
                    };
                }
                $lastNote = data_get($lic, 'meta.last_note');
              @endphp
              <tr class="hover:bg-gray-50 {{ $isDeleted ? 'opacity-70' : '' }}"
                  data-estado="{{ $estadoLabel }}"
                  data-email="{{ strtolower($team->owner?->email ?? '') }}">
                <td class="px-4 py-3 font-mono text-gray-700 text-center">{{ $team->id }}</td>
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
                <td class="px-4 py-3 text-gray-600">{{ $fmt($team->deleted_at) }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $fmt($team->purged_at) }}</td>
                <td class="px-4 py-3">
                  @if ($isPurged)
                    <span class="text-gray-400">{{ __('borrada') }}</span>
                  @elseif ($isDeleted)
                    @php $daysLeft = $team->daysUntilPurge(); @endphp
                    <span class="inline-flex items-center rounded-full font-bold whitespace-nowrap"
                          style="{{ $daysLeft <= 5 ? 'background:#fee2e2;color:#b91c1c;' : 'background:#ffedd5;color:#9a3412;' }}padding:4px 10px;font-size:11px;">
                      {{ $daysLeft }} / {{ \App\Models\Team::PURGE_AFTER_DAYS }} {{ __('días') }}
                    </span>
                  @else
                    <span class="text-gray-400">—</span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  @if ($isPurged)
                    <div class="text-xs text-gray-400 italic" style="min-width:210px;">{{ __('Eliminada permanentemente — sin acciones') }}</div>
                  @elseif ($isDeleted)
                    <div class="flex flex-col items-stretch gap-1.5" style="min-width:210px;">
                      <form method="POST" action="{{ route('admin.accounts.restore', $team->id) }}">
                        @csrf
                        <button type="submit" class="w-full text-xs px-2.5 py-1 rounded-md text-white" style="background-color:#16a34a;"
                                title="{{ __('Restaurar la cuenta y habilitar 7 días de prórroga') }}">
                          ↺ {{ __('Restaurar cuenta') }}
                        </button>
                      </form>
                      <button type="button"
                              @click="$dispatch('fd-open', { action: @js(route('admin.accounts.force-delete', $team->id)), label: @js('#'.$team->id.' '.$team->name) })"
                              class="w-full text-xs px-2.5 py-1 rounded-md text-white" style="background-color:#991b1b;">
                        ✕ {{ __('Eliminar por completo') }}
                      </button>
                    </div>
                  @else
                  <div class="flex flex-col items-stretch gap-1.5" style="min-width:210px;">
                    {{-- Bloquear / Habilitar (nota obligatoria) --}}
                    <form method="POST" class="flex items-center gap-1.5">
                      @csrf
                      <input type="text" name="note" required placeholder="{{ __('Nota / motivo') }}"
                             class="flex-1 border border-gray-300 rounded-md px-2 py-1 text-xs">
                      @if ($lic && $lic->is_active)
                        <button type="submit" formaction="{{ route('admin.accounts.block', $team) }}"
                                class="text-xs px-2.5 py-1 rounded-md text-white" style="background-color:#dc2626;">
                          {{ __('Bloquear') }}
                        </button>
                      @else
                        <button type="submit" formaction="{{ route('admin.accounts.enable', $team) }}"
                                class="text-xs px-2.5 py-1 rounded-md text-white" style="background-color:#16a34a;">
                          {{ __('Habilitar') }}
                        </button>
                      @endif
                    </form>
                    {{-- Nueva prórroga de 7 días --}}
                    <form method="POST" action="{{ route('admin.prorrogas.store') }}">
                      @csrf
                      <input type="hidden" name="team_id" value="{{ $team->id }}">
                      <input type="hidden" name="days" value="7">
                      <button type="submit" class="w-full text-xs px-2.5 py-1 rounded-md text-white" style="background-color:#2563eb;">
                        + {{ __('Prórroga 7 días') }}
                      </button>
                    </form>
                  </div>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="13" class="px-6 py-10 text-center text-gray-400">{{ __('No hay cuentas registradas.') }}</td></tr>
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

  {{-- Modal de confirmación: eliminar por completo --}}
  <div x-data="{ open:false, action:'', label:'' }"
       @fd-open.window="open=true; action=$event.detail.action; label=$event.detail.label"
       @keydown.escape.window="open=false"
       x-show="open" x-cloak
       class="fixed inset-0 flex items-center justify-center"
       style="z-index:80;background:rgba(15,23,42,.55);padding:16px;">
    <div @click.outside="open=false"
         style="background:#fff;border-radius:16px;max-width:440px;width:100%;padding:28px;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,.45);">
      <div style="width:60px;height:60px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
        <svg style="width:30px;height:30px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
      </div>
      <h3 style="font-size:18px;font-weight:800;color:#111827;margin-bottom:8px;">{{ __('¿Eliminar permanentemente?') }}</h3>
      <p style="font-size:14px;color:#4b5563;line-height:1.5;margin-bottom:6px;">
        {{ __('Vas a borrar por completo los datos de la cuenta') }} <b x-text="label"></b>.
      </p>
      <p style="font-size:13px;color:#b91c1c;font-weight:700;margin-bottom:22px;">{{ __('Esta acción NO se puede deshacer.') }}</p>
      <div style="display:flex;gap:10px;">
        <button type="button" @click="open=false"
                style="flex:1;padding:11px;border-radius:10px;border:1px solid #d1d5db;background:#fff;color:#374151;font-weight:600;cursor:pointer;">
          {{ __('Cancelar') }}
        </button>
        <form :action="action" method="POST" style="flex:1;">
          @csrf @method('DELETE')
          <button type="submit"
                  style="width:100%;padding:11px;border-radius:10px;border:none;background:#991b1b;color:#fff;font-weight:700;cursor:pointer;">
            {{ __('Sí, eliminar') }}
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function filterAccounts() {
      const estado = (document.getElementById('filterEstado').value || '').toLowerCase();
      const email  = (document.getElementById('filterEmail').value || '').trim().toLowerCase();
      let visibles = 0;
      document.querySelectorAll('#accountsTbody tr[data-estado]').forEach(tr => {
        const e = (tr.getAttribute('data-estado') || '').toLowerCase();
        const m = (tr.getAttribute('data-email') || '').toLowerCase();
        const okEstado = !estado || e === estado;
        const okEmail  = !email || m.includes(email);
        const show = okEstado && okEmail;
        tr.style.display = show ? '' : 'none';
        if (show) visibles++;
      });
      const cnt = document.getElementById('filterCount');
      if (cnt) cnt.textContent = (estado || email) ? (visibles + ' resultado(s)') : '';
    }

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
