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
            <div class="space-y-1.5 rounded-lg border border-gray-200 p-2">
              @forelse((array) $cf->options as $opt)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" name="{{ $name }}[]" value="{{ $opt }}"
                         class="rounded border-gray-300 text-indigo-600"
                         {{ in_array($opt, $selected) ? 'checked' : '' }}>
                  <span>{{ $opt }}</span>
                </label>
              @empty
                <span class="text-xs text-gray-400">{{ __('Sin opciones') }}</span>
              @endforelse
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
