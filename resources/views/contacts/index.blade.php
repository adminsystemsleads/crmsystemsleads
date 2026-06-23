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
    <div class="mb-5" x-data="{ adv: {{ $hasAdv ? 'true' : 'false' }} }">
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
              <div class="ms-dd" id="ddMonths">
                <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                  <span class="ms-dd-label placeholder" data-placeholder="{{ __('Todos') }}" data-count-label="{{ __('seleccionados') }}">{{ __('Todos') }}</span>{!! $caret !!}
                </button>
                <div class="ms-dd-panel">
                  @forelse($monthsList as $m)
                    <label class="ms-dd-opt"><input type="checkbox" name="months[]" value="{{ $m }}" onchange="msChanged(this)" {{ in_array($m, $filters['months']) ? 'checked' : '' }}><span>{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('F Y') }}</span></label>
                  @empty
                    <span class="px-2 py-1 text-xs text-gray-400">{{ __('Sin opciones') }}</span>
                  @endforelse
                </div>
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

    @if($contacts->hasPages())
      <div class="mt-4">{{ $contacts->withQueryString()->links() }}</div>
    @endif

  </div>
</x-app-layout>
