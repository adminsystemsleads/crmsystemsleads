<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>{{ $form->title ?: $form->name }}</title>
  <style>
    :root{
      --bg: {{ $form->bg_color ?: '#f3f4f6' }};
      --card: {{ $form->card_color ?: '#ffffff' }};
      --txt: {{ $form->text_color ?: '#1f2937' }};
      --pri: {{ $form->primary_color ?: '#4f46e5' }};
      --btntxt: {{ $form->button_text_color ?: '#ffffff' }};
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
      background:{{ $embed ? 'transparent' : 'var(--bg)' }};color:var(--txt);
      display:flex;align-items:flex-start;justify-content:center;min-height:100vh;padding:{{ $embed ? '8px' : '32px 16px' }};}
    .card{background:var(--card);width:100%;max-width:460px;border-radius:16px;
      box-shadow:0 10px 30px rgba(15,23,42,.10);padding:28px 26px;}
    h1{font-size:1.35rem;margin:0 0 6px;font-weight:800;line-height:1.25}
    .sub{opacity:.72;font-size:.9rem;margin:0 0 18px;line-height:1.5}
    .field{margin-bottom:14px}
    label{display:block;font-size:.78rem;font-weight:600;margin-bottom:5px}
    .req{color:#dc2626}
    input[type=text],input[type=email],input[type=tel],input[type=number],input[type=date],select,textarea{
      width:100%;padding:10px 12px;border:1px solid rgba(0,0,0,.15);border-radius:10px;font-size:.9rem;
      background:#fff;color:#111827;}
    input:focus,select:focus,textarea:focus{outline:none;border-color:var(--pri);box-shadow:0 0 0 3px color-mix(in srgb, var(--pri) 20%, transparent);}
    .opts{display:flex;flex-direction:column;gap:6px;margin-top:2px}
    .opt{display:flex;align-items:center;gap:8px;font-size:.85rem;font-weight:400}
    .err{color:#dc2626;font-size:.75rem;margin-top:4px}
    button{width:100%;margin-top:8px;padding:12px;border:0;border-radius:10px;background:var(--pri);
      color:var(--btntxt);font-size:.95rem;font-weight:700;cursor:pointer;}
    button:hover{filter:brightness(.95)}
    .success{text-align:center;padding:16px 4px}
    .success .ic{width:56px;height:56px;border-radius:999px;background:color-mix(in srgb, var(--pri) 15%, transparent);
      color:var(--pri);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:28px}
    .foot{margin-top:16px;text-align:center;font-size:.68rem;opacity:.5}
  </style>
</head>
<body>
  <div class="card" id="qipuCard">
    @if($success)
      <div class="success">
        <div class="ic">✓</div>
        <h1>{{ __('¡Enviado!') }}</h1>
        <p class="sub" style="margin-bottom:0">{{ $successMessage }}</p>
      </div>
    @else
      @if($form->title)<h1>{{ $form->title }}</h1>@endif
      @if($form->subtitle)<p class="sub">{{ $form->subtitle }}</p>@endif

      <form method="POST" action="{{ route('public.form.submit', $form->slug) }}">
        @if($embed)<input type="hidden" name="embed" value="1">@endif

        @foreach($form->fields as $field)
          @php
            $cf   = $field->source === 'custom' ? $field->customField : null;
            $name = $field->source === 'custom' ? "custom_fields[{$field->custom_field_id}]" : $field->core_key;
            $errKey = $field->source === 'custom' ? "custom_fields.{$field->custom_field_id}" : $field->core_key;
            $oldVal = $field->source === 'custom'
                        ? ($old['custom_fields'][$field->custom_field_id] ?? '')
                        : ($old[$field->core_key] ?? '');
            $type = $cf?->field_type ?? 'text';
          @endphp

          <div class="field">
            <label>{{ $field->displayLabel() }}@if($field->is_required)<span class="req"> *</span>@endif</label>

            @if($field->source === 'core')
              @if($field->core_key === 'email')
                <input type="email" name="email" value="{{ $oldVal }}" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
              @elseif($field->core_key === 'phone')
                <input type="tel" name="phone" value="{{ $oldVal }}" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
              @else
                <input type="text" name="{{ $field->core_key }}" value="{{ $oldVal }}" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
              @endif

            @elseif($type === 'number')
              <input type="number" name="{{ $name }}" value="{{ $oldVal }}" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
            @elseif($type === 'date')
              <input type="date" name="{{ $name }}" value="{{ $oldVal }}" {{ $field->is_required ? 'required' : '' }}>
            @elseif($type === 'multiselect')
              <div class="opts">
                @foreach(($cf->options ?? []) as $opt)
                  <label class="opt"><input type="checkbox" name="custom_fields[{{ $field->custom_field_id }}][]" value="{{ $opt }}"
                    @checked(is_array($oldVal) && in_array($opt, $oldVal))> {{ $opt }}</label>
                @endforeach
              </div>
            @elseif($type === 'select')
              <select name="{{ $name }}" {{ $field->is_required ? 'required' : '' }}>
                <option value="">{{ __('Selecciona una opción') }}</option>
                @foreach(($cf->options ?? []) as $opt)
                  <option value="{{ $opt }}" @selected($oldVal === $opt)>{{ $opt }}</option>
                @endforeach
              </select>
            @else
              <input type="text" name="{{ $name }}" value="{{ $oldVal }}" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
            @endif

            @if(!empty($errorsList[$errKey]))
              <div class="err">{{ $errorsList[$errKey] }}</div>
            @endif
          </div>
        @endforeach

        <button type="submit">{{ $form->button_text ?: __('Enviar') }}</button>
      </form>
    @endif

    <div class="foot">{{ __('Formulario seguro') }}</div>
  </div>

  <script>
    // Reporta la altura al contenedor padre cuando está incrustado (iframe autoajustable).
    (function () {
      function report() {
        var h = document.getElementById('qipuCard');
        var height = (h ? h.offsetHeight : document.body.scrollHeight) + 24;
        try { window.parent.postMessage({ qipuFormHeight: height }, '*'); } catch (e) {}
      }
      window.addEventListener('load', report);
      window.addEventListener('resize', report);
      setTimeout(report, 300);
    })();
  </script>
</body>
</html>
