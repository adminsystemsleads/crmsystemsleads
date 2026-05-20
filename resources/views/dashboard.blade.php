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
          No se pudo cargar el dashboard. Asegúrate de tener un equipo seleccionado.
        </div>
      @else

        {{-- ========= KPIs principales ========= --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

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
            <p class="text-xs text-gray-500 mt-1">Contactos totales</p>
            @if($metrics['contacts']['month'] > 0)
              <p class="text-[11px] text-green-600 font-semibold mt-1">+{{ $metrics['contacts']['month'] }} este mes</p>
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
            <p class="text-xs text-gray-500 mt-1">Negociaciones abiertas</p>
            <p class="text-[11px] text-gray-400 mt-1">de {{ number_format($metrics['deals']['total']) }} totales</p>
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
            <p class="text-xs text-gray-500 mt-1">Ganadas</p>
            @if($metrics['deals']['won_by_currency']->isNotEmpty())
              <p class="text-[11px] text-green-600 font-semibold mt-1">
                @foreach($metrics['deals']['won_by_currency'] as $cur => $amt)
                  {{ $cur }} {{ number_format($amt, 0) }}@if(!$loop->last)  · @endif
                @endforeach
                <span class="text-gray-400 font-normal">/ mes</span>
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
            <p class="text-xs text-gray-500 mt-1">Conversaciones abiertas</p>
            <p class="text-[11px] text-gray-400 mt-1">de {{ number_format($metrics['conversations']['total']) }} totales</p>
          </a>
        </div>

        {{-- ========= FUNNELS por pipeline ========= --}}
        @if($metrics['funnels']->isNotEmpty())
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold text-gray-900">Negociaciones por embudo</h3>
              <span class="text-xs text-gray-500">Solo abiertas, agrupadas por fase</span>
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
                      {{ $funnel['total_deals'] }} abiertas
                    </span>
                  </div>

                  @if($funnel['stages']->isEmpty())
                    <p class="text-sm text-gray-400 italic text-center py-3">Pipeline sin fases</p>
                  @else
                    <div class="space-y-2">
                      @foreach($funnel['stages'] as $stage)
                        @php
                          $pct = $funnel['max_count'] > 0
                            ? round(($stage['count'] / $funnel['max_count']) * 100)
                            : 0;
                        @endphp
                        <div>
                          <div class="flex items-center justify-between text-xs mb-1">
                            <div class="flex items-center gap-2 min-w-0">
                              <span class="size-2 rounded-full shrink-0" style="background-color: {{ $stage['color'] }};"></span>
                              <span class="font-medium text-gray-700 truncate">{{ $stage['name'] }}</span>
                              @if($stage['is_won'])
                                <span class="inline-flex rounded-full bg-green-100 text-green-700 px-1.5 py-0 text-[9px] font-semibold">GANADA</span>
                              @elseif($stage['is_lost'])
                                <span class="inline-flex rounded-full bg-red-100 text-red-700 px-1.5 py-0 text-[9px] font-semibold">PERDIDA</span>
                              @endif
                            </div>
                            <div class="text-right shrink-0">
                              <span class="font-bold text-gray-900">{{ $stage['count'] }}</span>
                              @if($stage['total'] > 0)
                                <span class="text-[10px] text-gray-400 ml-1">{{ number_format($stage['total'], 0) }}</span>
                              @endif
                            </div>
                          </div>
                          <div class="h-2 rounded-full overflow-hidden bg-gray-100">
                            <div class="h-full rounded-full transition-all"
                                 style="width: {{ max($pct, $stage['count'] > 0 ? 4 : 0) }}%; background-color: {{ $stage['color'] }};"></div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif

        {{-- ========= 2 columnas: top contactos + recientes ========= --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

          {{-- Top contactos con más negociaciones --}}
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-base font-bold text-gray-900 mb-3">🏆 Top contactos</h3>
            @if($metrics['top_contacts']->isEmpty())
              <p class="text-sm text-gray-400 italic py-4 text-center">Aún no hay contactos.</p>
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
                      {{ $c->deals_count }} {{ $c->deals_count === 1 ? 'deal' : 'deals' }}
                    </span>
                  </a>
                @endforeach
              </div>
            @endif
          </div>

          {{-- Negociaciones recientes --}}
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-base font-bold text-gray-900 mb-3">📋 Negociaciones recientes</h3>
            @if($metrics['recent_deals']->isEmpty())
              <p class="text-sm text-gray-400 italic py-4 text-center">Aún no hay negociaciones.</p>
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
                        {{ $deal->contact?->name ?? 'Sin contacto' }} ·
                        {{ $deal->stage?->name ?? 'Sin fase' }}
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

      @endif
    </div>
  </div>
</x-app-layout>
