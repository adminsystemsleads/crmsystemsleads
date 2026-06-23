{{--
  Renderiza inputs para campos personalizados.
  Variables esperadas:
   - $customFields: collection de App\Models\CustomField
   - $customValues: array [field_id => value] (opcional, para edición)
--}}
@php
  $customValues = $customValues ?? [];
@endphp

@if(isset($customFields) && $customFields->isNotEmpty())
  <div class="space-y-3">
    @foreach($customFields as $cf)
      @php
        $name  = "custom_fields[{$cf->id}]";
        $val   = old($name, $customValues[$cf->id] ?? '');
        $req   = $cf->is_required ? 'required' : '';
      @endphp

      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">
          {{ $cf->name }}
          @if($cf->is_required) <span class="text-red-500">*</span> @endif
        </label>

        @switch($cf->field_type)
          @case('text')
            <input type="text" name="{{ $name }}" value="{{ $val }}" maxlength="500" {{ $req }}
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
            @break

          @case('number')
            <input type="number" name="{{ $name }}" value="{{ $val }}" step="any" {{ $req }}
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
            @break

          @case('date')
            <input type="date" name="{{ $name }}" value="{{ $val }}" {{ $req }}
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
            @break

          @case('select')
            <select name="{{ $name }}" {{ $req }}
                    class="w-full rounded-lg border-gray-200 text-sm py-2">
              <option value="">{{ __('— Seleccionar —') }}</option>
              @foreach((array) $cf->options as $opt)
                <option value="{{ $opt }}" {{ (string) $val === (string) $opt ? 'selected' : '' }}>{{ $opt }}</option>
              @endforeach
            </select>
            @break

          @case('multiselect')
            @php
              $selected = is_array($val)
                  ? $val
                  : (is_string($val) && $val !== '' ? (json_decode($val, true) ?: [$val]) : []);
            @endphp
            <div class="ms-dd">
              <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
                <span class="ms-dd-label placeholder" data-placeholder="{{ __('— Seleccionar —') }}" data-count-label="{{ __('seleccionados') }}">{{ __('— Seleccionar —') }}</span>
                <svg class="ms-dd-caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
              </button>
              <div class="ms-dd-panel">
                @forelse((array) $cf->options as $opt)
                  <label class="ms-dd-opt">
                    <input type="checkbox" name="{{ $name }}[]" value="{{ $opt }}" onchange="msChanged(this)"
                           {{ in_array($opt, $selected) ? 'checked' : '' }}>
                    <span>{{ $opt }}</span>
                  </label>
                @empty
                  <span class="px-2 py-1 text-xs text-gray-400">{{ __('Sin opciones') }}</span>
                @endforelse
              </div>
            </div>
            @break

          @default
            <input type="text" name="{{ $name }}" value="{{ $val }}" {{ $req }}
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
        @endswitch
      </div>
    @endforeach
  </div>
@endif
