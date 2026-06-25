<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-semibold text-gray-800">{{ __('Contactos') }}</h2>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('contacts.export', request()->query()) }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
          <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          {{ __('Exportar CSV') }}
        </a>
        <a href="{{ route('contacts.import.form') }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
          <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          {{ __('Importar CSV') }}
        </a>
        <a href="{{ route('contacts.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
          <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ __('Nuevo contacto') }}
        </a>
      </div>
    </div>
  </x-slot>

  <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

    @if(session('status'))
      <div class="mb-4 flex items-center gap-2 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        <svg class="size-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('status') }}
      </div>
    @endif

    {{-- Barra de búsqueda y filtros avanzados --}}
    @php
      $caret = '<svg class="ms-dd-caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>';
      $hasAdv = $filters['createdFrom'] || $filters['createdTo'] || $filters['months'] || $filters['responsibles']
                || $filters['stages'] || $filters['pipelines'] || collect($filters['cf'])->flatten()->filter()->isNotEmpty();
      $anyFilter = $q || $status || $hasAdv;
    @endphp
    <div class="mb-8" x-data="contactsPage({{ $hasAdv ? 'true' : 'false' }})">
      <form method="GET" action="{{ route('contacts.index') }}">
        {{-- Línea principal --}}
        <div class="flex flex-wrap items-center gap-2">
          <div class="relative flex-1 min-w-0" style="max-width:30rem;">
            <svg class="absolute size-4 text-gray-400" style="left:12px;top:50%;transform:translateY(-50%);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="q" value="{{ $q }}"
                   placeholder="{{ __('Buscar por nombre, negociación, email, teléfono…') }}"
                   class="w-full py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-indigo-400 focus:border-indigo-400"
                   style="padding-left:38px;padding-right:12px;">
          </div>
          <select name="status" class="text-sm border border-gray-200 rounded-lg py-2 bg-white text-gray-700" style="padding-left:12px;padding-right:32px;">
            <option value="">{{ __('Todos los estados') }}</option>
            @foreach(['nuevo' => __('Nuevo'), 'activo' => __('Activo'), 'cliente' => __('Cliente'), 'inactivo' => __('Inactivo'), 'perdido' => __('Perdido')] as $val => $label)
              <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <button type="button" @click="adv = !adv"
                  class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4"/></svg>
            {{ __('Filtros') }}@if($hasAdv)<span class="ml-0.5 inline-block size-2 rounded-full bg-indigo-500"></span>@endif
          </button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm hover:bg-gray-700 transition">{{ __('Buscar') }}</button>
          @if($anyFilter)
            <a href="{{ route('contacts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('Limpiar') }}</a>
          @endif
          @if($waAccounts->isNotEmpty())
            <button type="button" @click="waOpen = !waOpen"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-white text-sm font-medium transition"
                    style="background:#16a34a;">
              <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.6.1-1.5-.7-2.5-1.3-3.5-3-.3-.5.3-.4.7-1.4.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.3.3-1 1-1 2.3s1 2.7 1.1 2.8c.1.2 2 3 4.8 4.2 1.8.7 2.5.8 3.3.7.5-.1 1.7-.7 1.9-1.4.2-.6.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3M12 2a10 10 0 00-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1012 2z"/></svg>
              {{ __('Enviar plantilla WhatsApp') }}
            </button>
          @endif
        </div>

        {{-- Panel avanzado --}}
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

            {{-- Mes de creación (múltiple) --}}
            <div style="width:12rem;">
              <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Mes de creación') }}</label>
              <div class="ms-dd month-dd" id="ddMonths" data-field="months[]" data-selected="{{ json_encode(array_values($filters['months'])) }}">
                <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                  <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('meses') }}">{{ __('Todos') }}</span>{!! $caret !!}
                </button>
                <div class="ms-dd-panel"></div>
              </div>
            </div>

            {{-- Responsable (múltiple) --}}
            <div style="width:12rem;">
              <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Responsable') }}</label>
              <div class="ms-dd" id="ddResp">
                <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                  <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('seleccionados') }}">{{ __('Todos') }}</span>{!! $caret !!}
                </button>
                <div class="ms-dd-panel">
                  @forelse($teamMembers as $m)
                    <label class="ms-dd-opt"><input type="checkbox" name="responsibles[]" value="{{ $m['id'] }}" onchange="msChanged(this)" {{ in_array((string)$m['id'], array_map('strval', $filters['responsibles'])) ? 'checked' : '' }}><span>{{ $m['name'] }}</span></label>
                  @empty
                    <span class="px-2 py-1 text-xs text-gray-400">{{ __('Sin opciones') }}</span>
                  @endforelse
                </div>
              </div>
            </div>

            {{-- Embudo (múltiple) --}}
            <div style="width:12rem;">
              <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Embudo') }}</label>
              <div class="ms-dd" id="ddPipes">
                <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                  <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('seleccionados') }}">{{ __('Todos') }}</span>{!! $caret !!}
                </button>
                <div class="ms-dd-panel">
                  @forelse($pipelinesList as $p)
                    <label class="ms-dd-opt"><input type="checkbox" name="pipelines[]" value="{{ $p->id }}" onchange="msChanged(this)" {{ in_array((string)$p->id, array_map('strval', $filters['pipelines'])) ? 'checked' : '' }}><span>{{ $p->name }}</span></label>
                  @empty
                    <span class="px-2 py-1 text-xs text-gray-400">{{ __('Sin opciones') }}</span>
                  @endforelse
                </div>
              </div>
            </div>

            {{-- Etapa (múltiple) --}}
            <div style="width:12rem;">
              <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Etapa') }}</label>
              <div class="ms-dd" id="ddStages">
                <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                  <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('seleccionados') }}">{{ __('Todos') }}</span>{!! $caret !!}
                </button>
                <div class="ms-dd-panel">
                  @forelse($stagesList as $s)
                    <label class="ms-dd-opt"><input type="checkbox" name="stages[]" value="{{ $s->id }}" onchange="msChanged(this)" {{ in_array((string)$s->id, array_map('strval', $filters['stages'])) ? 'checked' : '' }}><span>{{ $s->name }}</span></label>
                  @empty
                    <span class="px-2 py-1 text-xs text-gray-400">{{ __('Sin opciones') }}</span>
                  @endforelse
                </div>
              </div>
            </div>

            {{-- Campos personalizados --}}
            @foreach($contactFields as $field)
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
            <a href="{{ route('contacts.index') }}" class="px-3 py-2 rounded-lg border border-gray-300 text-xs text-gray-600 hover:bg-gray-50">{{ __('Limpiar filtros') }}</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">{{ __('Aplicar') }}</button>
          </div>
        </div>
      </form>

      {{-- ===== Panel: enviar plantilla de WhatsApp masiva ===== --}}
      @if($waAccounts->isNotEmpty())
      <div x-show="waOpen" x-cloak class="mt-3 p-4 bg-white border-2 border-green-200 rounded-xl shadow-sm">
        <h4 class="text-sm font-bold text-gray-800 mb-1 flex items-center gap-2">
          <svg class="size-4 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.6.1-.2.3-.7.9-.8 1-.2.2-.3.2-.6.1-1.5-.7-2.5-1.3-3.5-3-.3-.5.3-.4.7-1.4.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.3.3-1 1-1 2.3s1 2.7 1.1 2.8c.1.2 2 3 4.8 4.2 1.8.7 2.5.8 3.3.7.5-.1 1.7-.7 1.9-1.4.2-.6.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3M12 2a10 10 0 00-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1012 2z"/></svg>
          {{ __('Enviar plantilla de WhatsApp masiva') }}
        </h4>
        <p class="text-[11px] text-gray-500 mb-3">{{ __('Se enviará a los contactos del resultado actual (con la búsqueda y filtros aplicados) que tengan teléfono.') }}</p>

        <div class="flex flex-wrap gap-6">
          <div class="flex flex-wrap gap-4">
            {{-- Número --}}
            <div style="width:15rem;">
              <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Número (cuenta de WhatsApp)') }}</label>
              <select x-model="accountId" @change="loadTemplates()" class="w-full border-gray-300 rounded-lg text-sm py-2">
                <option value="">{{ __('Selecciona un número…') }}</option>
                @foreach($waAccounts as $acc)
                  <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                @endforeach
              </select>
            </div>

            {{-- Plantilla --}}
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

          {{-- Previsualización de la plantilla seleccionada --}}
          <div x-show="currentTpl" x-cloak style="width:18rem;">
            <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ __('Vista previa') }}</label>
            <div style="background:#e5ddd5;border-radius:.6rem;padding:.6rem;">
              <div style="background:#fff;border-radius:.55rem;padding:.55rem .65rem;box-shadow:0 1px 1px rgba(0,0,0,.1);font-size:12.5px;color:#111;">
                {{-- Encabezado --}}
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
                {{-- Cuerpo --}}
                <div style="white-space:pre-line;" x-text="currentTpl ? currentTpl.body : ''"></div>
                {{-- Pie --}}
                <template x-if="currentTpl && currentTpl.footer">
                  <div style="color:#667781;font-size:11px;margin-top:.3rem;" x-text="currentTpl.footer"></div>
                </template>
              </div>
              {{-- Botones --}}
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

        {{-- Variables del cuerpo --}}
        <div x-show="varCount > 0" x-cloak class="mt-3">
          <p class="text-[11px] font-medium text-gray-500 mb-1">{{ __('Valores de las variables del cuerpo (escribe {nombre} para insertar el nombre del contacto)') }}</p>
          <div class="flex flex-wrap gap-2">
            <template x-for="(v, idx) in vars" :key="idx">
              <input type="text" x-model="vars[idx]" :placeholder="'{{ __('Valor variable') }} ' + (idx + 1)"
                     class="border-gray-300 rounded-lg text-xs py-1.5" style="width:12rem;">
            </template>
          </div>
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

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Contacto') }}</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hidden md:table-cell">{{ __('Teléfono') }}</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hidden lg:table-cell">{{ __('Empresa') }}</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hidden lg:table-cell">{{ __('Origen') }}</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Estado') }}</th>
            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 hidden sm:table-cell">{{ __('Negocios') }}</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          @forelse($contacts as $contact)
            @php
              $colors = ['bg-indigo-100 text-indigo-700','bg-green-100 text-green-700','bg-amber-100 text-amber-700',
                         'bg-rose-100 text-rose-700','bg-purple-100 text-purple-700','bg-blue-100 text-blue-700'];
              $avatarCls = $colors[abs(crc32($contact->name)) % count($colors)];
              $initials  = strtoupper(mb_substr($contact->name, 0, 1) . (mb_substr($contact->name, 1, 1) ?: ''));
            @endphp
            <tr class="hover:bg-gray-50 transition">
              {{-- Contacto --}}
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="size-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0 {{ $avatarCls }}">
                    {{ $initials }}
                  </div>
                  <div class="min-w-0">
                    <a href="{{ route('contacts.edit', $contact) }}"
                       class="font-semibold text-gray-900 hover:text-indigo-600 truncate block">
                      {{ $contact->name }}
                    </a>
                    @if($contact->email)
                      <span class="text-xs text-gray-500 truncate block">{{ $contact->email }}</span>
                    @endif
                  </div>
                </div>
              </td>
              {{-- Teléfono --}}
              <td class="px-4 py-3 hidden md:table-cell text-gray-700">{{ $contact->phone ?? '—' }}</td>
              {{-- Empresa --}}
              <td class="px-4 py-3 hidden lg:table-cell">
                <div class="text-gray-700">{{ $contact->company ?? '—' }}</div>
                @if($contact->position)
                  <div class="text-xs text-gray-400">{{ $contact->position }}</div>
                @endif
              </td>
              {{-- Origen --}}
              <td class="px-4 py-3 hidden lg:table-cell text-gray-500 text-xs capitalize">{{ $contact->source ?? '—' }}</td>
              {{-- Estado --}}
              <td class="px-4 py-3">
                @php
                  $statusCls = match($contact->status) {
                    'activo'   => 'bg-green-100 text-green-700',
                    'cliente'  => 'bg-blue-100 text-blue-700',
                    'inactivo' => 'bg-gray-100 text-gray-500',
                    'perdido'  => 'bg-red-100 text-red-600',
                    default    => 'bg-amber-100 text-amber-700',
                  };
                @endphp
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusCls }} capitalize">
                  {{ $contact->status ?? 'nuevo' }}
                </span>
              </td>
              {{-- Negocios --}}
              <td class="px-4 py-3 text-center hidden sm:table-cell">
                @if($contact->deals_count > 0)
                  <span class="inline-flex items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-xs font-semibold w-6 h-6">
                    {{ $contact->deals_count }}
                  </span>
                @else
                  <span class="text-gray-300">—</span>
                @endif
              </td>
              {{-- Acciones --}}
              <td class="px-4 py-3 text-right">
                <a href="{{ route('contacts.edit', $contact) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:border-indigo-300 hover:text-indigo-600 transition">
                  <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  {{ __('Editar') }}
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-12 text-center">
                <svg class="size-10 mx-auto text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-sm text-gray-400">{{ __('No se encontraron contactos.') }}</p>
                <a href="{{ route('contacts.create') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">
                  {{ __('Crear el primero →') }}
                </a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
      <span>
        {{ __('Total de contactos') }}:
        <span class="font-semibold text-gray-800">{{ number_format($total) }}</span>
        @if($anyFilter || $q || $status)
          <span class="text-gray-400">· {{ __('con búsqueda/filtros aplicados') }}</span>
        @endif
      </span>
      @if($contacts->hasPages())
        <span class="text-gray-400">{{ __('Página') }} {{ $contacts->currentPage() }} / {{ $contacts->lastPage() }}</span>
      @endif
    </div>

    @if($contacts->hasPages())
      <div class="mt-3">{{ $contacts->withQueryString()->links() }}</div>
    @endif

  </div>

  <script>
    function contactsPage(adv) {
      return {
        adv: adv,
        waOpen: false,
        accountId: '',
        templates: [],
        loadingTpl: false,
        tplError: '',
        selectedTpl: '',
        vars: [],
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
          this.finished = false; this.progress = 0;
        },
        async send() {
          const tpl = this.currentTpl;
          if (!this.accountId || !tpl) return;
          if (!confirm('{{ __('¿Enviar esta plantilla a todos los contactos filtrados?') }}')) return;

          this.sending = true; this.finished = false;
          this.sentCount = 0; this.failedCount = 0; this.progress = 0;
          this.totalCount = 0; this.resultErrors = []; this.tplError = '';

          const url = '{{ route('contacts.bulk-template') }}' + window.location.search;
          let offset = 0; const limit = 20; let total = 0;

          try {
            do {
              const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ account_id: this.accountId, template: tpl.name, language: tpl.language, vars: this.vars, offset: offset, limit: limit }),
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
