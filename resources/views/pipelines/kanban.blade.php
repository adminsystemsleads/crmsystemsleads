<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kanban') }} – {{ $pipeline->name }}</h2>
    </x-slot>
    <div class="pt-6 pb-6">
        <div class="w-full px-4 sm:px-6">

            {{-- Toggle de vista --}}
            <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 text-sm">
                <a href="{{ route('pipelines.kanban', array_merge(['pipeline' => $pipeline], request()->except('view'))) }}"
                   class="px-3 py-1.5 rounded-lg {{ ($viewMode ?? 'kanban') !== 'table' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                    {{ __('Kanban') }}
                </a>
                <a href="{{ route('pipelines.kanban', array_merge(['pipeline' => $pipeline, 'view' => 'table'], request()->except('view'))) }}"
                   class="px-3 py-1.5 rounded-lg {{ ($viewMode ?? 'kanban') === 'table' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                    {{ __('Tabla') }}
                </a>
            </div>
            
            <div class="page-head mb-4 flex justify-between items-center gap-3">

                <p class="text-sm text-gray-600">
                    {{ __('Vista de negociaciones por fases del pipeline.') }}
                </p>

                <div class="page-head-actions flex items-center gap-2">
                    {{-- Botón exportar con dropdown --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button type="button" @click="open = !open"
                                class="inline-flex items-center gap-1.5 text-xs px-3 py-1 rounded-full border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            {{ __('Exportar') }}
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-1 w-56 bg-white rounded-lg shadow-xl ring-1 ring-black/5 py-1 z-50"
                             style="display: none;">

                            <a href="{{ route('deals.export', $pipeline) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                                📋 {{ __('Todas las negociaciones') }}
                            </a>
                            <div class="border-t my-1"></div>
                            <p class="px-4 py-1 text-[10px] uppercase tracking-wide text-gray-400 font-semibold">{{ __('Por fase') }}</p>
                            @foreach($stages as $stage)
                                <a href="{{ route('deals.export', [$pipeline, 'stage_id' => $stage->id]) }}"
                                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                                    <span class="size-2 rounded-full shrink-0"
                                          style="background-color: {{ $stage->color ?? '#6366f1' }};"></span>
                                    <span class="truncate">{{ $stage->name }}</span>
                                </a>
                            @endforeach
                            <div class="border-t my-1"></div>
                            <p class="px-4 py-1 text-[10px] uppercase tracking-wide text-gray-400 font-semibold">{{ __('Por estado') }}</p>
                            <a href="{{ route('deals.export', [$pipeline, 'status' => 'open']) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                                🟢 {{ __('Solo abiertas') }}
                            </a>
                            <a href="{{ route('deals.export', [$pipeline, 'status' => 'won']) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                                🏆 {{ __('Solo ganadas') }}
                            </a>
                            <a href="{{ route('deals.export', [$pipeline, 'status' => 'lost']) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                                ❌ {{ __('Solo perdidas') }}
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('pipelines.edit', $pipeline) }}"
                       class="text-indigo-100 text-xs px-3 py-1 rounded-full bg-indigo-600/80 hover:bg-indigo-700">
                        {{ __('Configurar fases') }}
                    </a>
                </div>
            </div>

            {{-- ===== Búsqueda y filtros avanzados ===== --}}
            @php
              $caret = '<svg class="ms-dd-caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>';
              $hasAdv = $filters['createdFrom'] || $filters['createdTo'] || $filters['months'] || $filters['responsibles']
                        || $filters['stages'] || collect($filters['cf'])->flatten()->filter()->isNotEmpty();
              $anyFilter = $q || $hasAdv;
            @endphp
            <div class="mb-4" x-data="dealsPage({{ $hasAdv ? 'true' : 'false' }})">
              <form method="GET" action="{{ route('pipelines.kanban', $pipeline) }}">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <div class="flex flex-wrap items-center gap-2">
                  <div class="relative flex-1 min-w-0" style="max-width:30rem;">
                    <svg class="absolute size-4 text-gray-400" style="left:12px;top:50%;transform:translateY(-50%);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" value="{{ $q }}"
                           placeholder="{{ __('Buscar por negociación, contacto, email, teléfono…') }}"
                           class="w-full py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-indigo-400 focus:border-indigo-400"
                           style="padding-left:38px;padding-right:12px;">
                  </div>
                  <button type="button" @click="adv = !adv"
                          class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4"/></svg>
                    {{ __('Filtros') }}@if($hasAdv)<span class="ml-0.5 inline-block size-2 rounded-full bg-indigo-500"></span>@endif
                  </button>
                  <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm hover:bg-gray-700 transition">{{ __('Buscar') }}</button>
                  @if($anyFilter)
                    <a href="{{ route('pipelines.kanban', ['pipeline' => $pipeline, 'view' => $viewMode]) }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('Limpiar') }}</a>
                  @endif
                  @if(($viewMode ?? 'kanban') === 'table' && $waAccounts->isNotEmpty())
                    <button type="button" @click="waOpen = !waOpen"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-white text-sm font-medium transition"
                            style="background:#16a34a;">
                      <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.6.1-1.5-.7-2.5-1.3-3.5-3-.3-.5.3-.4.7-1.4.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.3.3-1 1-1 2.3s1 2.7 1.1 2.8c.1.2 2 3 4.8 4.2 1.8.7 2.5.8 3.3.7.5-.1 1.7-.7 1.9-1.4.2-.6.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3M12 2a10 10 0 00-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1012 2z"/></svg>
                      {{ __('Enviar plantilla WhatsApp') }}
                    </button>
                  @endif
                </div>

                <div x-show="adv" x-cloak class="mt-3 p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                  <div class="flex flex-wrap gap-4">
                    <div style="width:11rem;">
                      <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Creado desde') }}</label>
                      <input type="date" name="created_from" value="{{ $filters['createdFrom'] }}" class="w-full border-gray-300 rounded-lg text-xs">
                    </div>
                    <div style="width:11rem;">
                      <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Creado hasta') }}</label>
                      <input type="date" name="created_to" value="{{ $filters['createdTo'] }}" class="w-full border-gray-300 rounded-lg text-xs">
                    </div>

                    <div style="width:12rem;">
                      <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Mes de creación') }}</label>
                      <div class="ms-dd month-dd" id="ddMonths" data-field="months[]" data-selected="{{ json_encode(array_values($filters['months'])) }}">
                        <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                          <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('meses') }}">{{ __('Todos') }}</span>{!! $caret !!}
                        </button>
                        <div class="ms-dd-panel"></div>
                      </div>
                    </div>

                    <div style="width:12rem;">
                      <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Responsable') }}</label>
                      <div class="ms-dd" id="ddResp">
                        <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                          <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('seleccionados') }}">{{ __('Todos') }}</span>{!! $caret !!}
                        </button>
                        <div class="ms-dd-panel">
                          @forelse($teamMembers as $tm)
                            <label class="ms-dd-opt"><input type="checkbox" name="responsibles[]" value="{{ $tm['id'] }}" onchange="msChanged(this)" {{ in_array((string)$tm['id'], array_map('strval', $filters['responsibles'])) ? 'checked' : '' }}><span>{{ $tm['name'] }}</span></label>
                          @empty
                            <span class="px-2 py-1 text-xs text-gray-400">{{ __('Sin opciones') }}</span>
                          @endforelse
                        </div>
                      </div>
                    </div>

                    <div style="width:12rem;">
                      <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Etapa') }}</label>
                      <div class="ms-dd" id="ddStages">
                        <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                          <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todas') }}" data-count-label="{{ __('seleccionados') }}">{{ __('Todas') }}</span>{!! $caret !!}
                        </button>
                        <div class="ms-dd-panel">
                          @foreach($stages as $s)
                            <label class="ms-dd-opt"><input type="checkbox" name="stages[]" value="{{ $s->id }}" onchange="msChanged(this)" {{ in_array((string)$s->id, array_map('strval', $filters['stages'])) ? 'checked' : '' }}><span>{{ $s->name }}</span></label>
                          @endforeach
                        </div>
                      </div>
                    </div>

                    @foreach($dealFields as $field)
                      @php $cfVal = $filters['cf'][$field->id] ?? null; @endphp
                      <div style="width:12rem;">
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ $field->name }}</label>
                        @if($field->field_type === 'multiselect')
                          <div class="ms-dd" id="ddcf{{ $field->id }}">
                            <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                              <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('seleccionados') }}">{{ __('Todos') }}</span>{!! $caret !!}
                            </button>
                            <div class="ms-dd-panel">
                              @foreach((array) $field->options as $opt)
                                <label class="ms-dd-opt"><input type="checkbox" name="cf[{{ $field->id }}][]" value="{{ $opt }}" onchange="msChanged(this)" {{ in_array($opt, (array) $cfVal) ? 'checked' : '' }}><span>{{ $opt }}</span></label>
                              @endforeach
                            </div>
                          </div>
                        @elseif($field->field_type === 'select')
                          <select name="cf[{{ $field->id }}]" class="w-full border-gray-300 rounded-lg text-xs py-2">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach((array) $field->options as $opt)
                              <option value="{{ $opt }}" {{ (string) $cfVal === (string) $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                          </select>
                        @elseif($field->field_type === 'date')
                          <input type="date" name="cf[{{ $field->id }}]" value="{{ is_array($cfVal) ? '' : $cfVal }}" class="w-full border-gray-300 rounded-lg text-xs">
                        @elseif($field->field_type === 'number')
                          <input type="number" step="any" name="cf[{{ $field->id }}]" value="{{ is_array($cfVal) ? '' : $cfVal }}" class="w-full border-gray-300 rounded-lg text-xs py-2">
                        @else
                          <input type="text" name="cf[{{ $field->id }}]" value="{{ is_array($cfVal) ? '' : $cfVal }}" class="w-full border-gray-300 rounded-lg text-xs py-2">
                        @endif
                      </div>
                    @endforeach
                  </div>

                  <div class="mt-4 flex justify-end gap-2">
                    <a href="{{ route('pipelines.kanban', ['pipeline' => $pipeline, 'view' => $viewMode]) }}" class="px-3 py-2 rounded-lg border border-gray-300 text-xs text-gray-600 hover:bg-gray-50">{{ __('Limpiar filtros') }}</a>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">{{ __('Aplicar') }}</button>
                  </div>
                </div>
              </form>

              {{-- ===== Panel: enviar plantilla de WhatsApp masiva (solo vista tabla) ===== --}}
              @if(($viewMode ?? 'kanban') === 'table' && $waAccounts->isNotEmpty())
              <div x-show="waOpen" x-cloak class="mt-3 p-4 bg-white border-2 border-green-200 rounded-xl shadow-sm">
                <h4 class="text-sm font-bold text-gray-800 mb-1 flex items-center gap-2">
                  <svg class="size-4 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.6.1-1.5-.7-2.5-1.3-3.5-3-.3-.5.3-.4.7-1.4.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.3.3-1 1-1 2.3s1 2.7 1.1 2.8c.1.2 2 3 4.8 4.2 1.8.7 2.5.8 3.3.7.5-.1 1.7-.7 1.9-1.4.2-.6.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3M12 2a10 10 0 00-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1012 2z"/></svg>
                  {{ __('Enviar plantilla de WhatsApp masiva') }}
                </h4>
                <p class="text-[11px] text-gray-500 mb-3">{{ __('Se enviará a los contactos de las negociaciones del resultado actual (con la búsqueda y filtros aplicados) que tengan teléfono.') }}</p>

                <div class="flex flex-wrap gap-6">
                  <div class="flex flex-wrap gap-4">
                    <div style="width:15rem;">
                      <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Número (cuenta de WhatsApp)') }}</label>
                      <select x-model="accountId" @change="loadTemplates()" class="w-full border-gray-300 rounded-lg text-sm py-2">
                        <option value="">{{ __('Selecciona un número…') }}</option>
                        @foreach($waAccounts as $acc)
                          <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div style="width:20rem;">
                      <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Plantilla') }}</label>
                      <select x-model="selectedTpl" @change="onTplChange()" :disabled="loadingTpl || !templates.length"
                              class="w-full border-gray-300 rounded-lg text-sm py-2 disabled:bg-gray-100">
                        <option value="">{{ __('Selecciona una plantilla…') }}</option>
                        <template x-for="t in templates" :key="t.name + '|' + t.language">
                          <option :value="t.name + '|' + t.language" x-text="t.name + ' (' + t.language + ')'"></option>
                        </template>
                      </select>
                      <p x-show="loadingTpl" x-cloak class="text-[11px] text-gray-400 mt-1">{{ __('Cargando plantillas…') }}</p>
                      <p x-show="tplError" x-cloak class="text-[11px] text-red-600 mt-1" x-text="tplError"></p>
                      <p x-show="!loadingTpl && accountId && !templates.length && !tplError" x-cloak class="text-[11px] text-gray-400 mt-1">{{ __('Este número no tiene plantillas aprobadas.') }}</p>
                    </div>
                  </div>

                  {{-- Previsualización --}}
                  <div x-show="currentTpl" x-cloak style="width:18rem;">
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Vista previa') }}</label>
                    <div style="background:#e5ddd5;border-radius:.6rem;padding:.6rem;">
                      <div style="background:#fff;border-radius:.55rem;padding:.55rem .65rem;box-shadow:0 1px 1px rgba(0,0,0,.1);font-size:12.5px;color:#111;">
                        <template x-if="currentTpl && currentTpl.header && currentTpl.header.format === 'TEXT' && currentTpl.header.text">
                          <div style="font-weight:700;margin-bottom:.25rem;" x-text="currentTpl.header.text"></div>
                        </template>
                        <template x-if="currentTpl && currentTpl.header && currentTpl.header.format === 'IMAGE' && currentTpl.header.media_url">
                          <img :src="currentTpl.header.media_url" alt="" style="width:100%;max-height:130px;object-fit:cover;border-radius:.4rem;display:block;margin-bottom:.4rem;">
                        </template>
                        <template x-if="currentTpl && currentTpl.header && currentTpl.header.format === 'VIDEO' && currentTpl.header.media_url">
                          <video :src="currentTpl.header.media_url" controls style="width:100%;max-height:150px;border-radius:.4rem;display:block;background:#000;margin-bottom:.4rem;"></video>
                        </template>
                        <template x-if="currentTpl && currentTpl.header && ['IMAGE','VIDEO','DOCUMENT','LOCATION'].includes(currentTpl.header.format) && !currentTpl.header.media_url">
                          <div style="background:#cfd8dc;border-radius:.4rem;height:90px;display:flex;align-items:center;justify-content:center;color:#607d8b;font-size:24px;margin-bottom:.4rem;"
                               x-text="currentTpl.header.format === 'IMAGE' ? '🖼️' : (currentTpl.header.format === 'VIDEO' ? '🎬' : (currentTpl.header.format === 'DOCUMENT' ? '📄' : '📍'))"></div>
                        </template>
                        <div style="white-space:pre-line;" x-text="currentTpl ? currentTpl.body : ''"></div>
                        <template x-if="currentTpl && currentTpl.footer">
                          <div style="color:#667781;font-size:11px;margin-top:.3rem;" x-text="currentTpl.footer"></div>
                        </template>
                      </div>
                      <template x-if="currentTpl && currentTpl.buttons && currentTpl.buttons.length">
                        <div style="margin-top:.35rem;display:flex;flex-direction:column;gap:.3rem;">
                          <template x-for="(b, bi) in currentTpl.buttons" :key="bi">
                            <div style="background:#fff;border-radius:.55rem;text-align:center;padding:.4rem;color:#1ea0e6;font-size:12.5px;font-weight:600;" x-text="b.text"></div>
                          </template>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>

                {{-- Variables --}}
                <div x-show="varCount > 0" x-cloak class="mt-3">
                  <p class="text-[11px] font-medium text-gray-500 mb-1">{{ __('Valores de las variables del cuerpo (escribe {nombre} para insertar el nombre del contacto)') }}</p>
                  <div class="flex flex-wrap gap-2">
                    <template x-for="(v, idx) in vars" :key="idx">
                      <input type="text" x-model="vars[idx]" :placeholder="'{{ __('Valor variable') }} ' + (idx + 1)"
                             class="border-gray-300 rounded-lg text-xs py-1.5" style="width:12rem;">
                    </template>
                  </div>
                </div>

                {{-- URL del archivo del encabezado multimedia --}}
                <div x-show="needsMedia" x-cloak class="mt-3" style="max-width:34rem;">
                  <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('URL del archivo del encabezado (imagen/vídeo/documento)') }}</label>
                  <input type="url" x-model="headerMedia" placeholder="https://…" class="w-full border-gray-300 rounded-lg text-xs py-1.5">
                  <p class="text-[10px] text-gray-400 mt-1">{{ __('Debe ser una URL pública del archivo. Por defecto se usa la muestra de la plantilla.') }}</p>
                </div>

                {{-- Enviar + progreso --}}
                <div class="mt-4 flex items-center gap-3">
                  <button type="button" @click="send()" :disabled="sending || !selectedTpl"
                          class="px-4 py-2 rounded-lg text-white text-sm font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
                          style="background:#16a34a;">
                    <span x-show="!sending">{{ __('Enviar plantilla') }}</span>
                    <span x-show="sending" x-cloak>{{ __('Enviando…') }}</span>
                  </button>
                </div>

                <div x-show="sending || finished" x-cloak class="mt-3" style="max-width:32rem;">
                  <div style="height:10px;background:#e5e7eb;border-radius:9999px;overflow:hidden;">
                    <div :style="'width:' + progress + '%'" style="height:100%;background:#16a34a;transition:width .3s;"></div>
                  </div>
                  <p class="text-[11px] text-gray-600 mt-1">
                    <span x-text="progress"></span>% —
                    <span x-text="sentCount"></span> {{ __('enviados') }},
                    <span x-text="failedCount"></span> {{ __('fallidos') }}
                    <span x-show="totalCount"> {{ __('de') }} <span x-text="totalCount"></span></span>
                  </p>
                  <p x-show="finished" x-cloak class="text-[11px] mt-1" :class="failedCount ? 'text-amber-600' : 'text-green-600'">
                    <span x-show="!failedCount">{{ __('¡Envío completado!') }}</span>
                    <span x-show="failedCount" x-cloak>{{ __('Envío finalizado con algunos fallos.') }}</span>
                  </p>
                  <template x-if="resultErrors.length">
                    <ul class="text-[11px] text-red-500 mt-1 list-disc pl-4">
                      <template x-for="(er, i) in resultErrors" :key="i"><li x-text="er"></li></template>
                    </ul>
                  </template>
                </div>
              </div>
              @endif
            </div>

            {{-- ===============================
                 VISTA TABLA
                 =============================== --}}
            @if(($viewMode ?? 'kanban') === 'table')
                @php
                    // Aplanar deals para tabla
                    $allDeals = collect();
                    foreach ($stages as $stage) {
                        $deals = $dealsByStage[$stage->id] ?? collect();
                        foreach ($deals as $d) {
                            $allDeals->push([
                                'deal'  => $d,
                                'stage' => $stage,
                            ]);
                        }
                    }
                @endphp

                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
                                <tr>
                                    <th class="py-2 pr-4">{{ __('Título') }}</th>
                                    <th class="py-2 pr-4">{{ __('Contacto') }}</th>
                                    <th class="py-2 pr-4">{{ __('Responsable') }}</th>
                                    <th class="py-2 pr-4">{{ __('Fase') }}</th>
                                    <th class="py-2 pr-4">{{ __('Monto') }}</th>
                                    <th class="py-2 pr-4">{{ __('Cierre') }}</th>
                                    <th class="py-2 pr-2 text-right">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y">
                                @forelse($allDeals as $row)
                                    @php
                                        $deal  = $row['deal'];
                                        $stage = $row['stage'];
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 pr-4">
                                            <a href="{{ route('deals.edit', [$pipeline, $deal]) }}"
                                               class="font-semibold text-gray-800 hover:text-indigo-600 hover:underline">
                                                {{ $deal->title }}
                                            </a>
                                            <div class="text-xs text-gray-500">
                                                #{{ $deal->id }}
                                            </div>
                                        </td>

                                        <td class="py-2 pr-4">
                                            @if($deal->contact)
                                                <a href="{{ route('contacts.edit', $deal->contact) }}"
                                                   class="text-indigo-700 hover:text-indigo-900 hover:underline">{{ $deal->contact->name }}</a>
                                                <div class="text-xs text-gray-500">
                                                    {{ $deal->contact->company ?? '' }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="py-2 pr-4">
                                            {{-- Si tienes relación responsible() en Deal --}}
                                            @if(method_exists($deal, 'responsible') && $deal->responsible)
                                                {{ $deal->responsible->name }}
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="py-2 pr-4">
                                            @php $sColor = $stage->color ?? '#6366f1'; @endphp
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold"
                                                  style="background-color: {{ $sColor }}1A; color: {{ $sColor }};">
                                                <span class="size-1.5 rounded-full" style="background-color: {{ $sColor }};"></span>
                                                {{ $stage->name }}
                                            </span>
                                        </td>

                                        <td class="py-2 pr-4">
                                            @if($deal->amount)
                                                <span class="text-gray-800 font-semibold">
                                                    {{ $deal->currency ?? 'PEN' }} {{ number_format($deal->amount, 2) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="py-2 pr-4">
                                            @if($deal->close_date)
                                                <span class="text-gray-700">
                                                    {{ \Carbon\Carbon::parse($deal->close_date)->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="py-2 pr-2 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('deals.edit', [$pipeline, $deal]) }}"
                                                   class="text-gray-500 hover:text-indigo-600 text-sm"
                                                   title="{{ __('Editar') }}">
                                                    ✏
                                                </a>

                                                <form action="{{ route('deals.destroy', [$pipeline, $deal]) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('{{ __('¿Eliminar esta negociación?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="text-gray-500 hover:text-red-600 text-sm"
                                                            title="{{ __('Eliminar') }}">
                                                        🗑
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 text-center text-gray-400">
                                            {{ __('No hay negociaciones en este embudo.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                        {{ __('Total de negociaciones') }}:
                        <span class="font-semibold text-gray-800">{{ number_format($total) }}</span>
                        @if($anyFilter || $q)
                          <span class="text-gray-400">· {{ __('con búsqueda/filtros aplicados') }}</span>
                        @endif
                    </div>
                </div>

            @else
            {{-- ===============================
                 VISTA KANBAN (TU CÓDIGO)
                 =============================== --}}

            {{-- Contenedor principal del Kanban (sin fondo blanco) --}}
            <div class="rounded-3xl p-5">
                <div class="flex space-x-5 overflow-x-auto pb-3">

                    @foreach($stages as $stage)
                        @php
                            $deals = $dealsByStage[$stage->id] ?? collect();
                            $totalAmount = $deals->sum('amount');
                            $currency = $deals->count() ? ($deals->first()->currency ?? 'PEN') : 'PEN';
                        @endphp

                        @php
                            $stageColor = $stage->color ?? '#6366f1';
                        @endphp

                        {{-- Columna --}}
                        <div class="flex-shrink-0 w-72"
                             data-stage-id="{{ $stage->id }}"
                             data-pipeline-id="{{ $pipeline->id }}">
                            <div class="rounded-2xl flex flex-col h-[78vh] border bg-white shadow-sm overflow-hidden"
                                 style="border-color: {{ $stageColor }}40;">

                                {{-- Header con color --}}
                                <div class="px-4 pt-3 pb-2 border-b-4"
                                     style="background-color: {{ $stageColor }}1A; border-bottom-color: {{ $stageColor }};">
                                    <div class="flex items-center gap-2">
                                        <span class="size-3 rounded-full shrink-0" style="background-color: {{ $stageColor }};"></span>
                                        <div class="text-xs font-bold uppercase tracking-wide flex-1" style="color: {{ $stageColor }};">
                                            {{ $stage->name }}
                                            <span class="text-[10px] opacity-80" data-stage-count>
                                                ({{ $deals->count() }})
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-2 text-lg font-bold text-gray-900">
                                        <span data-stage-total-currency>{{ $currency }}</span>
                                        <span data-stage-total>
                                            {{ number_format($totalAmount, 0, '.', ',') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Botón + --}}
                                <div class="px-4 pb-2" style="padding-top: 14px;">
                                    <a href="{{ route('deals.create', [$pipeline, 'stage' => $stage->id]) }}"
                                       class="w-full text-xs py-1.5 rounded-full border border-dashed border-gray-300 text-gray-500 hover:bg-gray-50 flex items-center justify-center">
                                        + {{ __('Nueva negociación') }}
                                    </a>
                                </div>

                                {{-- Cards --}}
                                <div class="flex-1 px-3 pb-3 overflow-y-auto space-y-3" data-stage-body>
                                    @foreach($deals as $deal)
                                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-3 py-3 text-xs space-y-1 kanban-card cursor-pointer
                                                    transition-all duration-150 ease-out
                                                    hover:shadow-lg hover:-translate-y-0.5 hover:border-indigo-300
                                                    active:translate-y-0 active:shadow-md"
                                             draggable="true"
                                             data-deal-id="{{ $deal->id }}"
                                             data-amount="{{ $deal->amount ?? 0 }}"
                                             data-currency="{{ $deal->currency ?? 'PEN' }}"
                                             data-deal-url="{{ route('deals.edit', [$pipeline, $deal]) }}"
                                             onclick="kanbanOpenDeal(event, this)"
                                             title="{{ __('Click para ver / editar') }}">
                                            <div class="flex justify-between items-start">
                                                <div class="font-semibold text-gray-800 text-sm line-clamp-2">
                                                    {{ $deal->title }}
                                                </div>

                                                <div class="flex items-center space-x-1 kanban-card-actions">
                                                    <a href="{{ route('deals.edit', [$pipeline, $deal]) }}"
                                                       class="text-gray-400 hover:text-indigo-600 text-xs"
                                                       title="{{ __('Editar') }}">
                                                        ✏
                                                    </a>
                                                    <form action="{{ route('deals.destroy', [$pipeline, $deal]) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('{{ __('¿Eliminar esta negociación?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="text-gray-400 hover:text-red-600 text-xs"
                                                                title="{{ __('Eliminar') }}">
                                                            🗑
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            @if($deal->contact)
                                                <div class="text-[11px] text-indigo-700">
                                                    {{ $deal->contact->name }}
                                                </div>
                                            @endif

                                            @if($deal->amount)
                                                <div class="text-[11px] text-gray-700">
                                                    {{ $deal->currency }} {{ number_format($deal->amount, 2) }}
                                                </div>
                                            @endif

                                            <div class="flex items-center justify-between mt-1">
                                                @if($deal->close_date)
                                                    <div class="text-[10px] text-gray-500">
                                                        {{ __('Cierre:') }}
                                                        {{ \Carbon\Carbon::parse($deal->close_date)->format('d M Y') }}
                                                    </div>
                                                @endif

                                                <div class="flex items-center space-x-3 text-gray-700 mt-2">
                                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[11px] font-semibold text-gray-700">
                                                        @
                                                    </span>
                                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[11px] font-semibold text-gray-700">
                                                        ☎
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <p data-empty-message
                                       class="text-[1px] text-gray-400 mt-1 italic {{ $deals->isEmpty() ? '' : 'hidden' }}">
                                        {{ __('Sin negociaciones') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            @endif

        </div>
    </div>

    {{-- JS SOLO PARA KANBAN --}}
    @if(($viewMode ?? 'kanban') !== 'table')
    <script>
        // Abrir la negociación al hacer click en la cartilla.
        // Ignora clicks sobre los botones de editar/eliminar y soporta drag.
        let __kanbanDragStarted = false;
        document.addEventListener('dragstart', (e) => {
            if (e.target.closest && e.target.closest('.kanban-card')) {
                __kanbanDragStarted = true;
            }
        });
        document.addEventListener('dragend', () => {
            // Pequeño delay para que no dispare click después de soltar
            setTimeout(() => { __kanbanDragStarted = false; }, 150);
        });

        window.kanbanOpenDeal = function (event, card) {
            // Si el click vino de un botón/enlace/form, no navegar
            if (event.target.closest('.kanban-card-actions, button, a, form')) return;
            // Si acabamos de hacer drag, tampoco
            if (__kanbanDragStarted) return;
            const url = card.dataset.dealUrl;
            if (url) window.location.href = url;
        };

        document.addEventListener('DOMContentLoaded', () => {
            const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : '';

            let draggedCard = null;

            function recalcColumn(column) {
                const body = column.querySelector('[data-stage-body]');
                if (!body) return;

                const cards = body.querySelectorAll('.kanban-card[data-deal-id]');
                const emptyMsg = column.querySelector('[data-empty-message]');
                const countSpan = column.querySelector('[data-stage-count]');
                const totalCurrencySpan = column.querySelector('[data-stage-total-currency]');
                const totalSpan = column.querySelector('[data-stage-total]');

                if (emptyMsg) emptyMsg.classList.toggle('hidden', cards.length > 0);
                if (countSpan) countSpan.textContent = `(${cards.length})`;

                let totalAmount = 0;
                let currency = 'PEN';

                cards.forEach(card => {
                    const amount = parseFloat(card.dataset.amount || '0');
                    if (!isNaN(amount)) totalAmount += amount;
                    if (card.dataset.currency) currency = card.dataset.currency;
                });

                if (totalCurrencySpan) totalCurrencySpan.textContent = currency;
                if (totalSpan) {
                    totalSpan.textContent = totalAmount.toLocaleString('es-PE', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            }

            function recalcAllColumns() {
                document.querySelectorAll('[data-stage-id][data-pipeline-id]').forEach(col => recalcColumn(col));
            }

            function initCards() {
                document.querySelectorAll('.kanban-card[data-deal-id]').forEach(card => {
                    card.addEventListener('dragstart', e => {
                        draggedCard = card;
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', card.dataset.dealId);
                        setTimeout(() => card.classList.add('opacity-50'), 0);
                    });

                    card.addEventListener('dragend', () => {
                        card.classList.remove('opacity-50');
                        draggedCard = null;
                    });
                });
            }

            function initColumns() {
                document.querySelectorAll('[data-stage-id][data-pipeline-id]').forEach(column => {
                    column.addEventListener('dragover', e => {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        column.classList.add('ring-2', 'ring-indigo-400');
                    });

                    column.addEventListener('dragleave', () => {
                        column.classList.remove('ring-2', 'ring-indigo-400');
                    });

                    column.addEventListener('drop', e => {
                        e.preventDefault();
                        column.classList.remove('ring-2', 'ring-indigo-400');

                        const dealId = e.dataTransfer.getData('text/plain');
                        const stageId = column.dataset.stageId;
                        const pipelineId = column.dataset.pipelineId;

                        if (!dealId || !stageId || !pipelineId) return;

                        const body = column.querySelector('[data-stage-body]');
                        if (draggedCard && body) body.prepend(draggedCard);

                        recalcAllColumns();

                        fetch(`/pipelines/${pipelineId}/deals/${dealId}/move`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ stage_id: stageId })
                        }).then(r => {
                            if (!r.ok) throw new Error('Error');
                            return r.json();
                        }).catch(() => window.location.reload());
                    });
                });
            }

            initCards();
            initColumns();
            recalcAllColumns();
        });
    </script>
    @endif

    <script>
      function dealsPage(adv) {
        return {
          adv: adv,
          waOpen: false,
          accountId: '',
          templates: [],
          loadingTpl: false,
          tplError: '',
          selectedTpl: '',
          vars: [],
          headerMedia: '',
          sending: false,
          finished: false,
          progress: 0,
          sentCount: 0,
          failedCount: 0,
          totalCount: 0,
          resultErrors: [],
          get currentTpl() {
            return this.templates.find(t => (t.name + '|' + t.language) === this.selectedTpl) || null;
          },
          get varCount() {
            return this.currentTpl ? (this.currentTpl.var_count || 0) : 0;
          },
          get needsMedia() {
            const f = (this.currentTpl && this.currentTpl.header) ? this.currentTpl.header.format : '';
            return f === 'IMAGE' || f === 'VIDEO' || f === 'DOCUMENT';
          },
          async loadTemplates() {
            this.templates = []; this.selectedTpl = ''; this.vars = []; this.tplError = '';
            this.finished = false; this.progress = 0;
            if (!this.accountId) return;
            this.loadingTpl = true;
            try {
              const res = await fetch('{{ url('/whatsapp/accounts') }}/' + this.accountId + '/templates/list', { headers: { 'Accept': 'application/json' } });
              const data = await res.json();
              if (data.ok) { this.templates = data.templates || []; }
              else { this.tplError = data.message || '{{ __('No se pudieron cargar las plantillas.') }}'; }
            } catch (e) { this.tplError = e.message; }
            this.loadingTpl = false;
          },
          onTplChange() {
            this.vars = Array.from({ length: this.varCount }, () => '');
            this.headerMedia = (this.currentTpl && this.currentTpl.header && this.currentTpl.header.media_url) ? this.currentTpl.header.media_url : '';
            this.finished = false; this.progress = 0;
          },
          async send() {
            const tpl = this.currentTpl;
            if (!this.accountId || !tpl) return;
            if (!confirm('{{ __('¿Enviar esta plantilla a todos los contactos filtrados?') }}')) return;

            this.sending = true; this.finished = false;
            this.sentCount = 0; this.failedCount = 0; this.progress = 0;
            this.totalCount = 0; this.resultErrors = []; this.tplError = '';

            const url = '{{ route('pipelines.bulk-template', $pipeline) }}' + window.location.search;
            let offset = 0; const limit = 20; let total = 0;

            try {
              do {
                const res = await fetch(url, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                  body: JSON.stringify({ account_id: this.accountId, template: tpl.name, language: tpl.language, vars: this.vars, header_format: this.needsMedia ? tpl.header.format : null, header_media: this.needsMedia ? this.headerMedia : null, offset: offset, limit: limit }),
                });
                const data = await res.json();
                if (!data.ok) { this.tplError = data.message || 'Error'; break; }
                total = data.total; this.totalCount = total;
                this.sentCount += data.sent; this.failedCount += data.failed;
                if (data.errors && data.errors.length) { this.resultErrors.push(...data.errors); }
                offset = data.processed;
                this.progress = total > 0 ? Math.round(offset / total * 100) : 100;
                if (data.done) break;
              } while (offset < total);
            } catch (e) { this.tplError = e.message; }

            this.sending = false; this.finished = true;
          },
        };
      }
    </script>
</x-app-layout>
