<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center gap-3">
      <a href="{{ route('whatsapp.accounts.index') }}" class="text-gray-400 hover:text-gray-600 transition">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </a>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Plantillas de WhatsApp') }} — {{ $account->name }}</h2>
    </div>
  </x-slot>

  <div class="max-w-6xl mx-auto py-8 px-4 space-y-6">

    @if($error)
      <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
        <strong>{{ __('No se pudieron cargar las plantillas') }}:</strong> {{ $error }}
      </div>
    @endif

    @php
      $statusMap = [
        'APPROVED' => ['#dcfce7', '#15803d'],
        'PENDING'  => ['#fef3c7', '#92400e'],
        'IN_APPEAL'=> ['#fef3c7', '#92400e'],
        'PENDING_DELETION' => ['#fef3c7', '#92400e'],
        'REJECTED' => ['#fee2e2', '#b91c1c'],
        'DISABLED' => ['#fee2e2', '#b91c1c'],
        'PAUSED'   => ['#fee2e2', '#b91c1c'],
      ];
    @endphp

    {{-- ===== Lista de plantillas ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-gray-900">{{ __('Plantillas existentes') }} ({{ count($templates) }})</h3>
        <a href="{{ route('whatsapp.templates.index', $account) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">↻ {{ __('Actualizar') }}</a>
      </div>

      @if(empty($templates))
        <div class="px-5 py-10 text-center text-sm text-gray-400">{{ __('Aún no hay plantillas creadas para esta cuenta.') }}</div>
      @else
        <div class="divide-y divide-gray-100">
          @foreach($templates as $t)
            @php
              $st = strtoupper($t['status'] ?? '');
              [$bg, $fg] = $statusMap[$st] ?? ['#f3f4f6', '#4b5563'];
              $comps = collect($t['components'] ?? []);
              $header = $comps->firstWhere('type', 'HEADER');
              $body   = $comps->firstWhere('type', 'BODY');
              $footer = $comps->firstWhere('type', 'FOOTER');
              $btns   = $comps->firstWhere('type', 'BUTTONS');
            @endphp
            <div class="px-5 py-4 flex flex-col md:flex-row md:items-start gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-semibold text-gray-900 text-sm">{{ $t['name'] ?? '—' }}</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background:{{ $bg }};color:{{ $fg }};">{{ $st }}</span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-600">{{ $t['category'] ?? '' }}</span>
                  <span class="text-[10px] text-gray-400">{{ $t['language'] ?? '' }}</span>
                </div>
                @if($st === 'REJECTED' && !empty($t['rejected_reason']))
                  <p class="text-[11px] text-red-500 mt-1">{{ __('Motivo') }}: {{ $t['rejected_reason'] }}</p>
                @endif
                <form method="POST" action="{{ route('whatsapp.templates.destroy', [$account, $t['name']]) }}"
                      onsubmit="return confirm('{{ __('¿Eliminar esta plantilla de Meta?') }}');" class="mt-2">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-[11px] font-semibold text-red-600 hover:text-red-800">{{ __('Eliminar') }}</button>
                </form>
              </div>

              {{-- Previsualización --}}
              <div class="shrink-0" style="width:300px;max-width:100%;">
                <div style="background:#e5ddd5;border-radius:.6rem;padding:.6rem;">
                  <div style="background:#fff;border-radius:.55rem;padding:.55rem .65rem;box-shadow:0 1px 1px rgba(0,0,0,.1);font-size:12.5px;color:#111;">
                    @if($header && ($header['format'] ?? '') === 'TEXT')
                      <div style="font-weight:700;margin-bottom:.25rem;">{{ $header['text'] ?? '' }}</div>
                    @endif
                    <div style="white-space:pre-line;">{{ $body['text'] ?? '' }}</div>
                    @if($footer)
                      <div style="color:#667781;font-size:11px;margin-top:.3rem;">{{ $footer['text'] ?? '' }}</div>
                    @endif
                  </div>
                  @if($btns && !empty($btns['buttons']))
                    <div style="margin-top:.35rem;display:flex;flex-direction:column;gap:.3rem;">
                      @foreach($btns['buttons'] as $b)
                        <div style="background:#fff;border-radius:.55rem;text-align:center;padding:.4rem;color:#1ea0e6;font-size:12.5px;font-weight:600;">{{ $b['text'] ?? '' }}</div>
                      @endforeach
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- ===== Crear nueva plantilla ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 ring-1 ring-indigo-50 p-5"
         x-data="{
           name:'{{ old('name') }}', category:'{{ old('category','MARKETING') }}', language:'{{ old('language','es') }}',
           header:'{{ addslashes(old('header_text')) }}', body:{{ \Illuminate\Support\Js::from(old('body','')) }},
           footer:'{{ addslashes(old('footer_text')) }}', buttons:['','',''],
           get vars(){ var s=new Set(); (this.body.match(/\{\{\s*(\d+)\s*\}\}/g)||[]).forEach(function(x){ s.add(parseInt(x.replace(/[^0-9]/g,''),10)); }); return Array.from(s).sort(function(a,b){return a-b;}); }
         }">
      <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
        <svg class="size-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        {{ __('Crear nueva plantilla') }}
      </h3>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Formulario --}}
        <form method="POST" action="{{ route('whatsapp.templates.store', $account) }}" class="space-y-3">
          @csrf
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Nombre') }} *</label>
              <input type="text" name="name" x-model="name" required pattern="[a-z0-9_]+"
                     placeholder="bienvenida_cliente" class="w-full rounded-lg border-gray-200 text-sm py-2">
              <p class="text-[10px] text-gray-400 mt-1">{{ __('Solo minúsculas, números y guiones bajos.') }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Idioma') }} *</label>
              <select name="language" x-model="language" class="w-full rounded-lg border-gray-200 text-sm py-2">
                @foreach(['es'=>'Español','es_PE'=>'Español (Perú)','es_MX'=>'Español (México)','es_ES'=>'Español (España)','en'=>'English','en_US'=>'English (US)','pt_BR'=>'Português (Brasil)'] as $code=>$lbl)
                  <option value="{{ $code }}">{{ $lbl }} ({{ $code }})</option>
                @endforeach
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Categoría') }} *</label>
            <select name="category" x-model="category" class="w-full rounded-lg border-gray-200 text-sm py-2">
              <option value="MARKETING">{{ __('Marketing (promociones, novedades)') }}</option>
              <option value="UTILITY">{{ __('Utilidad (confirmaciones, actualizaciones)') }}</option>
              <option value="AUTHENTICATION">{{ __('Autenticación (códigos OTP)') }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Encabezado (opcional)') }}</label>
            <input type="text" name="header_text" x-model="header" maxlength="60" class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>

          <div>
            @php $bodyExample = __('Hola !a, tu pedido !b está listo. ¡Gracias!'); $bodyExample = str_replace(['!a','!b'], ['{{1}}','{{2}}'], $bodyExample); @endphp
            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Cuerpo del mensaje') }} *</label>
            <textarea name="body" x-model="body" rows="4" maxlength="1024" required
                      placeholder="{{ $bodyExample }}"
                      class="w-full rounded-lg border-gray-200 text-sm py-2"></textarea>
            <p class="text-[10px] text-gray-400 mt-1">{{ __('Usa') }} <code>&#123;&#123;1&#125;&#125;</code>, <code>&#123;&#123;2&#125;&#125;</code>… {{ __('para variables.') }}</p>
          </div>

          {{-- Ejemplos por variable --}}
          <template x-if="vars.length">
            <div class="space-y-2 rounded-lg border border-gray-100 p-2 bg-gray-50">
              <p class="text-[11px] font-semibold text-gray-500">{{ __('Ejemplos para variables (requerido por Meta)') }}</p>
              <template x-for="n in vars" :key="n">
                <div class="flex items-center gap-2">
                  <span class="text-xs text-gray-500 font-mono" x-text="'@{{' + n + '}}'"></span>
                  <input type="text" :name="'examples[' + n + ']'" class="flex-1 rounded-lg border-gray-200 text-xs py-1.5"
                         :placeholder="'{{ __('Ejemplo para') }} @{{' + n + '}}'">
                </div>
              </template>
            </div>
          </template>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Pie de página (opcional)') }}</label>
            <input type="text" name="footer_text" x-model="footer" maxlength="60" class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Botones de respuesta rápida (opcional, hasta 3)') }}</label>
            <div class="space-y-2">
              <template x-for="(b, i) in buttons" :key="i">
                <input type="text" :name="'buttons[' + i + ']'" x-model="buttons[i]" maxlength="25"
                       :placeholder="'{{ __('Botón') }} ' + (i + 1)" class="w-full rounded-lg border-gray-200 text-xs py-1.5">
              </template>
            </div>
          </div>

          <div class="pt-1">
            <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
              {{ __('Enviar a Meta para revisión') }}
            </button>
          </div>
        </form>

        {{-- Previsualización en vivo --}}
        <div>
          <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('Previsualización') }}</p>
          <div style="background:#e5ddd5;border-radius:.75rem;padding:1rem;min-height:180px;">
            <div style="background:#fff;border-radius:.6rem;padding:.6rem .7rem;box-shadow:0 1px 1px rgba(0,0,0,.1);font-size:13px;color:#111;max-width:280px;">
              <div x-show="header" x-cloak style="font-weight:700;margin-bottom:.25rem;" x-text="header"></div>
              <div style="white-space:pre-line;" x-text="body || '{{ __('Escribe el cuerpo del mensaje…') }}'"></div>
              <div x-show="footer" x-cloak style="color:#667781;font-size:11px;margin-top:.35rem;" x-text="footer"></div>
            </div>
            <div style="margin-top:.4rem;max-width:280px;display:flex;flex-direction:column;gap:.3rem;">
              <template x-for="(b, i) in buttons" :key="i">
                <div x-show="b" x-cloak style="background:#fff;border-radius:.6rem;text-align:center;padding:.45rem;color:#1ea0e6;font-size:13px;font-weight:600;" x-text="b"></div>
              </template>
            </div>
          </div>
          <p class="text-[11px] text-gray-400 mt-2">{{ __('Las plantillas pasan por revisión de Meta. Pueden tardar de minutos a horas en aprobarse.') }}</p>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
