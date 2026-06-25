<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Dashboard') }}
    </h2>
  </x-slot>

  <div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

      @if(!($metrics ?? null))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
          {{ __('No se pudo cargar el dashboard. Asegúrate de tener un equipo seleccionado.') }}
        </div>
      @else
        {{-- Barra de edición del tablero --}}
        <div class="flex items-center justify-end gap-2" x-data="{ editing: false }">
          <button type="button" x-show="!editing" @click="window.dashEdit && window.dashEdit(true); editing = true"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs text-gray-700 hover:bg-gray-50">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            {{ __('Editar tablero') }}
          </button>
          <span x-show="editing" x-cloak class="flex items-center gap-2">
            <button type="button" @click="window.dashReset && window.dashReset()" class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-600 hover:bg-gray-50">{{ __('Restablecer') }}</button>
            <button type="button" @click="window.dashSave && window.dashSave(); editing = false" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">{{ __('Listo') }}</button>
          </span>
        </div>

        <div id="dashGrid" class="space-y-6"
             data-order="{{ json_encode($dashPrefs['order']) }}" data-hidden="{{ json_encode($dashPrefs['hidden']) }}">

        {{-- ========= KPIs principales ========= --}}
        <div class="dash-block grid grid-cols-2 md:grid-cols-4 gap-4" data-block="kpis">

          {{-- Contactos --}}
          <a href="{{ route('contacts.index') }}"
             class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:border-indigo-300 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
              <div class="size-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="size-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </div>
              <svg class="size-4 text-gray-300 group-hover:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
              </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['contacts']['total']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ __('Contactos totales') }}</p>
            @if($metrics['contacts']['month'] > 0)
              <p class="text-[11px] text-green-600 font-semibold mt-1">+{{ $metrics['contacts']['month'] }} {{ __('este mes') }}</p>
            @endif
          </a>

          {{-- Negociaciones abiertas --}}
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="size-10 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
              <svg class="size-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
              </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['deals']['open']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ __('Negociaciones abiertas') }}</p>
            <p class="text-[11px] text-gray-400 mt-1">{{ __('de') }} {{ number_format($metrics['deals']['total']) }} {{ __('totales') }}</p>
          </div>

          {{-- Ganadas --}}
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="size-10 rounded-xl bg-green-50 flex items-center justify-center mb-3">
              <svg class="size-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['deals']['won']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ __('Ganadas') }}</p>
            @if($metrics['deals']['won_by_currency']->isNotEmpty())
              <p class="text-[11px] text-green-600 font-semibold mt-1">
                @foreach($metrics['deals']['won_by_currency'] as $cur => $amt)
                  {{ $cur }} {{ number_format($amt, 0) }}@if(!$loop->last)  · @endif
                @endforeach
                <span class="text-gray-400 font-normal">/ {{ __('mes') }}</span>
              </p>
            @endif
          </div>

          {{-- Conversaciones WhatsApp --}}
          <a href="{{ route('whatsapp.inbox.index') }}"
             class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:border-green-300 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
              <div class="size-10 rounded-xl bg-green-50 flex items-center justify-center">
                <svg class="size-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
              </div>
              <svg class="size-4 text-gray-300 group-hover:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
              </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['conversations']['open']) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ __('Conversaciones abiertas') }}</p>
            <p class="text-[11px] text-gray-400 mt-1">{{ __('de') }} {{ number_format($metrics['conversations']['total']) }} {{ __('totales') }}</p>
          </a>
        </div>

        {{-- ========= REPORTES: estado, conversión y valor ========= --}}
        @php
          $dOpen = $metrics['deals']['open']; $dWon = $metrics['deals']['won']; $dLost = $metrics['deals']['lost'];
          $realTot = $dOpen + $dWon + $dLost; $tot = max(1, $realTot);
          $sOpen = round($dOpen / $tot * 100, 2);
          $sWon  = round(($dOpen + $dWon) / $tot * 100, 2);
          $conv  = $metrics['deals']['conversion_rate'];
        @endphp
        <div class="dash-block grid grid-cols-1 md:grid-cols-3 gap-4" data-block="reports">

          {{-- Oportunidades por estado (dona) --}}
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">{{ __('Oportunidades por estado') }}</h3>
            <div class="flex items-center gap-4">
              <div style="position:relative;width:120px;height:120px;border-radius:50%;flex-shrink:0;
                          background:conic-gradient(#3b82f6 0 {{ $sOpen }}%, #22c55e {{ $sOpen }}% {{ $sWon }}%, #ef4444 {{ $sWon }}% 100%);">
                <div style="position:absolute;inset:16px;background:#fff;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                  <span class="text-xl font-bold text-gray-900">{{ number_format($realTot) }}</span>
                  <span class="text-[10px] text-gray-400">{{ __('Total') }}</span>
                </div>
              </div>
              <div class="text-xs space-y-1.5 flex-1">
                <div class="flex items-center justify-between gap-2"><span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full" style="background:#3b82f6;"></span>{{ __('Abiertas') }}</span><span class="font-semibold">{{ number_format($dOpen) }}</span></div>
                <div class="flex items-center justify-between gap-2"><span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full" style="background:#22c55e;"></span>{{ __('Ganadas') }}</span><span class="font-semibold">{{ number_format($dWon) }}</span></div>
                <div class="flex items-center justify-between gap-2"><span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full" style="background:#ef4444;"></span>{{ __('Perdidas') }}</span><span class="font-semibold">{{ number_format($dLost) }}</span></div>
              </div>
            </div>
          </div>

          {{-- Tasa de conversión (anillo) --}}
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col">
            <h3 class="text-sm font-bold text-gray-900 mb-3">{{ __('Tasa de conversión') }}</h3>
            <div class="flex-1 flex items-center justify-center">
              <div style="position:relative;width:130px;height:130px;border-radius:50%;
                          background:conic-gradient(#16a34a 0 {{ $conv }}%, #e5e7eb {{ $conv }}% 100%);">
                <div style="position:absolute;inset:15px;background:#fff;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                  <span class="text-2xl font-bold text-gray-900">{{ $conv }}%</span>
                  <span class="text-[10px] text-gray-400">{{ __('Ganadas / Cerradas') }}</span>
                </div>
              </div>
            </div>
            <p class="text-[11px] text-gray-400 text-center mt-2">{{ __('Ganadas') }}: {{ number_format($dWon) }} · {{ __('Perdidas') }}: {{ number_format($dLost) }}</p>
          </div>

          {{-- Valor de oportunidades --}}
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-900 mb-3">{{ __('Valor de oportunidades') }}</h3>
            <div class="space-y-3">
              <div>
                <p class="text-[11px] text-gray-500 mb-1">{{ __('En negociaciones abiertas') }}</p>
                @forelse($metrics['deals']['open_value_by_currency'] as $cur => $amt)
                  <p class="text-base font-bold text-blue-600">{{ $cur }} {{ number_format($amt, 2) }}</p>
                @empty
                  <p class="text-base font-bold text-gray-300">—</p>
                @endforelse
              </div>
              <div class="border-t border-gray-100 pt-2">
                <p class="text-[11px] text-gray-500 mb-1">{{ __('Ganado (histórico)') }}</p>
                @forelse($metrics['deals']['won_all_by_currency'] as $cur => $amt)
                  <p class="text-base font-bold text-green-600">{{ $cur }} {{ number_format($amt, 2) }}</p>
                @empty
                  <p class="text-base font-bold text-gray-300">—</p>
                @endforelse
              </div>
            </div>
          </div>
        </div>

        {{-- ========= FUNNELS por pipeline (gráficos de barras) ========= --}}
        @if($metrics['funnels']->isNotEmpty())
          <div class="dash-block space-y-4" data-block="funnels">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold text-gray-900">{{ __('Negociaciones por embudo') }}</h3>
              <span class="text-xs text-gray-500">{{ __('Solo abiertas, agrupadas por fase') }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              @foreach($metrics['funnels'] as $funnel)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                  <div class="flex items-center justify-between mb-4">
                    <a href="{{ $funnel['kanban_url'] }}"
                       class="text-base font-bold text-gray-900 hover:text-indigo-600 transition">
                      {{ $funnel['name'] }}
                    </a>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 text-indigo-700 px-2.5 py-0.5 text-xs font-semibold">
                      {{ $funnel['total_deals'] }} {{ __('abiertas') }}
                    </span>
                  </div>

                  @if($funnel['stages']->isEmpty())
                    <p class="text-sm text-gray-400 italic text-center py-3">{{ __('Pipeline sin fases') }}</p>
                  @else
                    {{-- Gráfico de barras verticales --}}
                    <div class="relative">
                      {{-- Cuadrícula de fondo --}}
                      <div class="absolute inset-x-0 bottom-8 h-44 flex flex-col justify-between pointer-events-none">
                        <div class="border-t border-dashed border-gray-100"></div>
                        <div class="border-t border-dashed border-gray-100"></div>
                        <div class="border-t border-dashed border-gray-100"></div>
                        <div class="border-t border-dashed border-gray-100"></div>
                        <div class="border-t border-gray-200"></div>
                      </div>

                      {{-- Barras --}}
                      <div class="relative flex items-end justify-around gap-1 h-44 px-1">
                        @foreach($funnel['stages'] as $stage)
                          @php
                            $maxC = max($funnel['max_count'], 1);
                            $pct  = $maxC > 0 ? ($stage['count'] / $maxC) * 100 : 0;
                            // Altura mínima 4% para que se vea algo aunque sea 0; pero si es 0 real, dejar mas chico
                            $hPct = $stage['count'] > 0 ? max($pct, 8) : 2;
                          @endphp
                          <div class="flex-1 flex flex-col items-center justify-end h-full group min-w-0">
                            {{-- Número arriba de la barra --}}
                            <span class="text-xs font-bold text-gray-700 mb-1
                                         {{ $stage['count'] > 0 ? '' : 'text-gray-300' }}">
                              {{ $stage['count'] }}
                            </span>
                            {{-- Barra --}}
                            <div class="w-full rounded-t-lg transition-all duration-300 ease-out hover:opacity-80 cursor-default
                                        relative overflow-hidden shadow-sm"
                                 style="height: {{ $hPct }}%;
                                        background: linear-gradient(180deg, {{ $stage['color'] }} 0%, {{ $stage['color'] }}CC 100%);
                                        min-height: {{ $stage['count'] > 0 ? '8px' : '4px' }};"
                                 title="{{ $stage['name'] }}: {{ $stage['count'] }} {{ __('negociaciones') }}{{ $stage['total'] > 0 ? ' — ' . __('Monto:') . ' ' . number_format($stage['total'], 2) : '' }}">
                              {{-- brillo top sutil --}}
                              <span class="absolute inset-x-0 top-0 h-1/3 bg-white/15 pointer-events-none"></span>
                            </div>
                          </div>
                        @endforeach
                      </div>

                      {{-- Eje X — etiquetas --}}
                      <div class="flex items-start justify-around gap-1 mt-2 px-1">
                        @foreach($funnel['stages'] as $stage)
                          <div class="flex-1 flex flex-col items-center min-w-0 text-center">
                            <span class="size-2 rounded-full mb-1 shrink-0" style="background-color: {{ $stage['color'] }};"></span>
                            <span class="font-medium text-gray-600 truncate w-full leading-tight" style="font-size:10px;">
                              {{ $stage['name'] }}
                            </span>
                            @if($stage['is_won'])
                              <span class="font-bold text-green-600 mt-0.5 truncate w-full leading-tight" style="font-size:8px;">{{ __('GANADA') }}</span>
                            @elseif($stage['is_lost'])
                              <span class="font-bold text-red-600 mt-0.5 truncate w-full leading-tight" style="font-size:8px;">{{ __('PERDIDA') }}</span>
                            @endif
                          </div>
                        @endforeach
                      </div>
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif

        {{-- ========= 2 columnas: top contactos + recientes ========= --}}
        <div class="dash-block grid grid-cols-1 lg:grid-cols-2 gap-4" data-block="toprecent">

          {{-- Top contactos con más negociaciones --}}
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-base font-bold text-gray-900 mb-3">🏆 {{ __('Top contactos') }}</h3>
            @if($metrics['top_contacts']->isEmpty())
              <p class="text-sm text-gray-400 italic py-4 text-center">{{ __('Aún no hay contactos.') }}</p>
            @else
              <div class="divide-y divide-gray-100">
                @foreach($metrics['top_contacts'] as $idx => $c)
                  <a href="{{ route('contacts.edit', $c) }}"
                     class="flex items-center gap-3 py-2.5 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition">
                    <span class="size-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0">
                      {{ $idx + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-semibold text-gray-900 truncate">{{ $c->name }}</p>
                      <p class="text-[11px] text-gray-400 truncate">
                        {{ $c->company ?: $c->phone ?: '—' }}
                      </p>
                    </div>
                    <span class="text-xs font-bold text-indigo-600 shrink-0">
                      {{ $c->deals_count }} {{ $c->deals_count === 1 ? __('deal') : __('deals') }}
                    </span>
                  </a>
                @endforeach
              </div>
            @endif
          </div>

          {{-- Negociaciones recientes --}}
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-base font-bold text-gray-900 mb-3">📋 {{ __('Negociaciones recientes') }}</h3>
            @if($metrics['recent_deals']->isEmpty())
              <p class="text-sm text-gray-400 italic py-4 text-center">{{ __('Aún no hay negociaciones.') }}</p>
            @else
              <div class="divide-y divide-gray-100">
                @foreach($metrics['recent_deals'] as $deal)
                  @php $sColor = $deal->stage?->color ?? '#6366f1'; @endphp
                  <a href="{{ route('deals.edit', [$deal->pipeline_id, $deal]) }}"
                     class="flex items-center gap-3 py-2.5 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition">
                    <span class="size-2 rounded-full shrink-0" style="background-color: {{ $sColor }};"></span>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-semibold text-gray-900 truncate">{{ $deal->title }}</p>
                      <p class="text-[11px] text-gray-400 truncate">
                        {{ $deal->contact?->name ?? __('Sin contacto') }} ·
                        {{ $deal->stage?->name ?? __('Sin fase') }}
                      </p>
                    </div>
                    @if($deal->amount)
                      <span class="text-xs font-bold text-gray-700 shrink-0">
                        {{ $deal->currency }} {{ number_format($deal->amount, 0) }}
                      </span>
                    @endif
                  </a>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        {{-- ========= REPORTE DE ACTIVIDADES ========= --}}
        @php
            $respList = $activities->map(fn ($a) => $a->user->name ?? '—')->unique()->filter()->sort()->values();
            $monthsList = $activities
                ->map(fn ($a) => $a->created_at?->copy()->setTimezone($teamTz)?->format('Y-m'))
                ->filter()->unique()->sortDesc()->values();
        @endphp
        <div class="dash-block bg-white rounded-2xl shadow-sm border border-gray-100 p-5" data-block="activities">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h3 class="text-base font-bold text-gray-900">🗂️ {{ __('Reporte de Actividades') }}</h3>
                <button type="button" onclick="actExport()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white"
                        style="background:#1E2E48;">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    {{ __('Exportar CSV') }}
                </button>
            </div>

            {{-- Filtros --}}
            <div class="flex flex-wrap items-end gap-3 mb-4">
                @php $caret = '<svg class="ms-dd-caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>'; @endphp
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Estado') }}</label>
                    <div class="ms-dd" id="ddEstado" data-onchange="actFilter" style="width:170px;">
                        <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                            <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('seleccionados') }}">{{ __('Todos') }}</span>
                            {!! $caret !!}
                        </button>
                        <div class="ms-dd-panel">
                            <label class="ms-dd-opt"><input type="checkbox" value="open" onchange="msChanged(this)"><span>{{ __('Pendiente') }}</span></label>
                            <label class="ms-dd-opt"><input type="checkbox" value="done" onchange="msChanged(this)"><span>{{ __('Completada') }}</span></label>
                            <label class="ms-dd-opt"><input type="checkbox" value="lost" onchange="msChanged(this)"><span>{{ __('Perdida') }}</span></label>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Responsable') }}</label>
                    <div class="ms-dd" id="ddResp" data-onchange="actFilter" style="width:190px;">
                        <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                            <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('seleccionados') }}">{{ __('Todos') }}</span>
                            {!! $caret !!}
                        </button>
                        <div class="ms-dd-panel">
                            @forelse($respList as $r)
                                <label class="ms-dd-opt"><input type="checkbox" value="{{ $r }}" onchange="msChanged(this)"><span>{{ $r }}</span></label>
                            @empty
                                <span class="px-2 py-1 text-xs text-gray-400">{{ __('Sin opciones') }}</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Mes de creación') }}</label>
                    <div class="ms-dd month-dd" id="ddMes" data-onchange="actFilter" data-field="months[]" data-selected="[]" style="width:190px;">
                        <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                            <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('meses') }}">{{ __('Todos') }}</span>
                            {!! $caret !!}
                        </button>
                        <div class="ms-dd-panel"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Creada desde') }}</label>
                    <input id="fDesde" type="date" onchange="actFilter()" class="border-gray-300 rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Creada hasta') }}</label>
                    <input id="fHasta" type="date" onchange="actFilter()" class="border-gray-300 rounded-lg text-xs">
                </div>
                <button type="button" onclick="actClear()"
                        class="px-3 py-2 rounded-lg border border-gray-300 text-xs text-gray-600 hover:bg-gray-50">{{ __('Limpiar') }}</button>
                <span class="text-[11px] text-gray-400"><span id="actCount">{{ $activities->count() }}</span> {{ __('actividades') }}</span>
            </div>

            <div class="overflow-x-auto">
                <table id="actTable" class="w-full text-xs">
                    <thead class="text-left text-gray-500 border-b">
                        <tr>
                            @php
                                $cols = [__('Asunto'), __('Responsable'), __('Negociación'), __('Tipo'), __('Creada'), __('Vence'), __('Estado')];
                            @endphp
                            @foreach($cols as $col)
                                <th onclick="actSort(this)" data-sortable
                                    class="px-3 py-2 font-semibold cursor-pointer select-none whitespace-nowrap hover:text-gray-700">
                                    {{ $col }}<span class="sa"></span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($activities as $a)
                            @php
                                $st = $a->status;
                                $stLabel = $st === 'done' ? __('Completada') : ($st === 'lost' ? __('Perdida') : __('Pendiente'));
                                $stStyle = $st === 'done' ? 'background:#dcfce7;color:#15803d;'
                                          : ($st === 'lost' ? 'background:#fee2e2;color:#b91c1c;' : 'background:#f3f4f6;color:#4b5563;');
                                $created = $a->created_at?->copy()->setTimezone($teamTz);
                                $due = $a->due_at?->copy()->setTimezone($teamTz);
                                $resp = $a->user->name ?? '—';
                            @endphp
                            <tr data-estado="{{ $st }}" data-resp="{{ $resp }}" data-created="{{ $created?->format('Y-m-d') }}">
                                <td class="px-3 py-2 text-gray-800">{{ $a->subject }}</td>
                                <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $resp }}</td>
                                <td class="px-3 py-2">
                                    @if($a->deal)
                                        <a href="{{ route('deals.edit', [$a->deal->pipeline_id, $a->deal_id]) }}"
                                           class="text-indigo-600 hover:underline">{{ $a->deal->title }}</a>
                                    @else — @endif
                                </td>
                                <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ strtoupper($a->type) }}</td>
                                <td class="px-3 py-2 text-gray-600 whitespace-nowrap" data-sort="{{ $created?->timestamp ?? 0 }}">{{ $created?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-3 py-2 text-gray-600 whitespace-nowrap" data-sort="{{ $due?->timestamp ?? 0 }}">{{ $due?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold" style="{{ $stStyle }}">{{ $stLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-3 py-6 text-center text-gray-400">{{ __('Aún no hay actividades.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            function actSort(th) {
                const table = document.getElementById('actTable');
                const tbody = table.querySelector('tbody');
                const idx = Array.prototype.indexOf.call(th.parentNode.children, th);
                const asc = th.dataset.asc !== 'true';
                table.querySelectorAll('th[data-sortable]').forEach(h => { h.dataset.asc = ''; const s = h.querySelector('.sa'); if (s) s.textContent = ''; });
                th.dataset.asc = asc ? 'true' : 'false';
                const s = th.querySelector('.sa'); if (s) s.textContent = asc ? ' ▲' : ' ▼';
                const rows = Array.prototype.slice.call(tbody.querySelectorAll('tr')).filter(r => !r.querySelector('[colspan]'));
                rows.sort(function (r1, r2) {
                    const c1 = r1.children[idx], c2 = r2.children[idx];
                    let v1 = c1.dataset.sort !== undefined ? c1.dataset.sort : c1.textContent.trim();
                    let v2 = c2.dataset.sort !== undefined ? c2.dataset.sort : c2.textContent.trim();
                    const n1 = parseFloat(v1), n2 = parseFloat(v2);
                    if (!isNaN(n1) && !isNaN(n2)) { v1 = n1; v2 = n2; }
                    else { v1 = String(v1).toLowerCase(); v2 = String(v2).toLowerCase(); }
                    if (v1 < v2) return asc ? -1 : 1;
                    if (v1 > v2) return asc ? 1 : -1;
                    return 0;
                });
                rows.forEach(r => tbody.appendChild(r));
            }

            function actMulti(id) {
                return Array.prototype.map.call(
                    document.querySelectorAll('#' + id + ' .ms-dd-panel input[type=checkbox]:checked'),
                    function (c) { return c.value; }
                );
            }
            function actClear() {
                ['ddEstado', 'ddResp', 'ddMes'].forEach(function (id) {
                    const dd = document.getElementById(id);
                    dd.querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = false; });
                    if (window.msUpdateLabel) msUpdateLabel(dd);
                });
                document.getElementById('fDesde').value = '';
                document.getElementById('fHasta').value = '';
                actFilter();
            }
            function actFilter() {
                const ests = actMulti('ddEstado');
                const resps = actMulti('ddResp');
                const meses = actMulti('ddMes');
                const desde = document.getElementById('fDesde').value;
                const hasta = document.getElementById('fHasta').value;
                let visible = 0;
                document.querySelectorAll('#actTable tbody tr').forEach(function (tr) {
                    if (tr.querySelector('[colspan]')) return;
                    let ok = true;
                    const cr = tr.dataset.created || '';
                    if (ests.length && ests.indexOf(tr.dataset.estado) === -1) ok = false;
                    if (resps.length && resps.indexOf(tr.dataset.resp) === -1) ok = false;
                    if (meses.length && meses.indexOf(cr.slice(0, 7)) === -1) ok = false;
                    if (desde && (!cr || cr < desde)) ok = false;
                    if (hasta && (!cr || cr > hasta)) ok = false;
                    tr.style.display = ok ? '' : 'none';
                    if (ok) visible++;
                });
                const c = document.getElementById('actCount'); if (c) c.textContent = visible;
            }

            function actExport() {
                const header = ['{{ __('Asunto') }}', '{{ __('Responsable') }}', '{{ __('Negociación') }}', '{{ __('Tipo') }}', '{{ __('Creada') }}', '{{ __('Vence') }}', '{{ __('Estado') }}'];
                const rows = [header];
                document.querySelectorAll('#actTable tbody tr').forEach(function (tr) {
                    if (tr.querySelector('[colspan]') || tr.style.display === 'none') return;
                    const c = tr.children;
                    rows.push([c[0], c[1], c[2], c[3], c[4], c[5], c[6]].map(td => td.textContent.trim()));
                });
                const csv = rows.map(r => r.map(v => '"' + String(v).replace(/"/g, '""') + '"').join(',')).join('\n');
                const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'actividades.csv';
                document.body.appendChild(a); a.click(); document.body.removeChild(a);
            }
        </script>

        </div>{{-- #dashGrid --}}

        <style>
          .dash-block { position: relative; }
          .dash-block.dash-dragging { opacity: .45; }
          body.dash-editing .dash-block { outline: 2px dashed #c7d2fe; outline-offset: 5px; border-radius: .75rem; }
          .dash-toolbar { position: absolute; top: -12px; right: 10px; z-index: 6; display: none; gap: 4px; }
          body.dash-editing .dash-block > .dash-toolbar { display: inline-flex; }
          .dash-toolbar button { background: #1E2E48; color: #fff; border: none; border-radius: 6px; padding: 2px 8px; font-size: 12px; cursor: pointer; box-shadow: 0 1px 4px rgba(0,0,0,.25); line-height: 1.4; }
          .dash-toolbar .dash-handle { cursor: grab; }
          .dash-block.dash-hidden { display: none; }
          body.dash-editing .dash-block.dash-hidden { display: block; opacity: .4; }
        </style>
        <script>
          (function () {
            var SAVE_URL = '{{ route('dashboard.prefs') }}';
            var CSRF = '{{ csrf_token() }}';
            var DEFAULT_ORDER = {!! json_encode(\App\Models\User::DASHBOARD_BLOCKS) !!};
            var grid = document.getElementById('dashGrid');
            if (!grid) return;
            var dragEl = null;

            function blocks() { return Array.prototype.slice.call(grid.querySelectorAll('.dash-block')); }

            function dashEdit(on) {
              document.body.classList.toggle('dash-editing', on);
              blocks().forEach(function (b) {
                b.setAttribute('draggable', on ? 'true' : 'false');
                if (on && !b.querySelector(':scope > .dash-toolbar')) {
                  var tb = document.createElement('div'); tb.className = 'dash-toolbar';
                  var h = document.createElement('button'); h.type = 'button'; h.className = 'dash-handle'; h.textContent = '⠿'; h.title = '{{ __('Arrastrar') }}';
                  var e = document.createElement('button'); e.type = 'button'; e.className = 'dash-hide';
                  e.textContent = b.classList.contains('dash-hidden') ? '🚫' : '👁';
                  e.title = '{{ __('Mostrar / ocultar') }}';
                  e.addEventListener('click', function (ev) {
                    ev.stopPropagation();
                    b.classList.toggle('dash-hidden');
                    e.textContent = b.classList.contains('dash-hidden') ? '🚫' : '👁';
                  });
                  tb.appendChild(h); tb.appendChild(e);
                  b.insertBefore(tb, b.firstChild);
                }
              });
            }

            function afterEl(y) {
              var els = blocks().filter(function (b) { return !b.classList.contains('dash-dragging'); });
              var closest = { offset: -Infinity, el: null };
              els.forEach(function (el) {
                var box = el.getBoundingClientRect();
                var offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) closest = { offset: offset, el: el };
              });
              return closest.el;
            }

            grid.addEventListener('dragstart', function (e) {
              var b = e.target.closest('.dash-block');
              if (!b || !document.body.classList.contains('dash-editing')) return;
              dragEl = b; b.classList.add('dash-dragging');
              e.dataTransfer.effectAllowed = 'move';
              try { e.dataTransfer.setData('text/plain', ''); } catch (x) {}
            });
            grid.addEventListener('dragend', function () { if (dragEl) { dragEl.classList.remove('dash-dragging'); dragEl = null; } });
            grid.addEventListener('dragover', function (e) {
              if (!dragEl) return; e.preventDefault();
              var a = afterEl(e.clientY);
              if (a == null) grid.appendChild(dragEl); else grid.insertBefore(dragEl, a);
            });

            function save(payload) {
              fetch(SAVE_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
              }).catch(function () {});
            }

            window.dashEdit = dashEdit;
            window.dashSave = function () {
              var order = blocks().map(function (b) { return b.dataset.block; });
              var hidden = blocks().filter(function (b) { return b.classList.contains('dash-hidden'); }).map(function (b) { return b.dataset.block; });
              dashEdit(false);
              save({ order: order, hidden: hidden });
            };
            window.dashReset = function () {
              blocks().forEach(function (b) { b.classList.remove('dash-hidden'); });
              DEFAULT_ORDER.forEach(function (k) { var b = grid.querySelector('.dash-block[data-block="' + k + '"]'); if (b) grid.appendChild(b); });
              dashEdit(false);
              save({ order: [], hidden: [] });
            };

            // Aplicar orden y ocultos guardados al cargar.
            var order = [], hidden = [];
            try { order = JSON.parse(grid.dataset.order || '[]'); } catch (e) {}
            try { hidden = JSON.parse(grid.dataset.hidden || '[]'); } catch (e) {}
            order.forEach(function (k) { var b = grid.querySelector('.dash-block[data-block="' + k + '"]'); if (b) grid.appendChild(b); });
            hidden.forEach(function (k) { var b = grid.querySelector('.dash-block[data-block="' + k + '"]'); if (b) b.classList.add('dash-hidden'); });
          })();
        </script>

      @endif
    </div>
  </div>
</x-app-layout>
