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
      $langs = [
        'es'=>'Español','es_AR'=>'Español (Argentina)','es_MX'=>'Español (México)','es_ES'=>'Español (España)',
        'en'=>'English','en_US'=>'English (US)','en_GB'=>'English (UK)',
        'pt_BR'=>'Português (Brasil)','pt_PT'=>'Português (Portugal)',
        'af'=>'Afrikaans','sq'=>'Albanés','ar'=>'Árabe','az'=>'Azerí','bn'=>'Bengalí','bg'=>'Búlgaro',
        'ca'=>'Catalán','zh_CN'=>'Chino (CHN)','zh_HK'=>'Chino (HKG)','zh_TW'=>'Chino (TAI)','hr'=>'Croata',
        'cs'=>'Checo','da'=>'Danés','nl'=>'Neerlandés','et'=>'Estonio','fil'=>'Filipino','fi'=>'Finés',
        'fr'=>'Francés','ka'=>'Georgiano','de'=>'Alemán','el'=>'Griego','gu'=>'Gujarati','ha'=>'Hausa',
        'he'=>'Hebreo','hi'=>'Hindi','hu'=>'Húngaro','id'=>'Indonesio','ga'=>'Irlandés','it'=>'Italiano',
        'ja'=>'Japonés','kn'=>'Canarés','kk'=>'Kazajo','ko'=>'Coreano','lo'=>'Lao','lv'=>'Letón',
        'lt'=>'Lituano','mk'=>'Macedonio','ms'=>'Malayo','ml'=>'Malabar','mr'=>'Maratí','nb'=>'Noruego',
        'fa'=>'Persa','pl'=>'Polaco','pa'=>'Panyabí','ro'=>'Rumano','ru'=>'Ruso','sr'=>'Serbio',
        'sk'=>'Eslovaco','sl'=>'Esloveno','sw'=>'Suajili','sv'=>'Sueco','ta'=>'Tamil','te'=>'Telugu',
        'th'=>'Tailandés','tr'=>'Turco','uk'=>'Ucraniano','ur'=>'Urdu','uz'=>'Uzbeko','vi'=>'Vietnamita','zu'=>'Zulú',
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
        @php
          $catMap = ['UTILITY'=>__('Servicio'),'MARKETING'=>__('Marketing'),'AUTHENTICATION'=>__('Autenticación')];
          $stLabel = ['APPROVED'=>__('Aprobada'),'PENDING'=>__('Pendiente'),'IN_APPEAL'=>__('En apelación'),'PENDING_DELETION'=>__('Pend. eliminación'),'REJECTED'=>__('Rechazada'),'DISABLED'=>__('Deshabilitada'),'PAUSED'=>__('Pausada')];
        @endphp
        <div class="overflow-x-auto" x-data="{ open: null }">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-xs font-semibold text-gray-500 border-b border-gray-100">
                <th class="px-5 py-3">{{ __('Nombre de la plantilla') }}</th>
                <th class="px-5 py-3">{{ __('Categoría') }}</th>
                <th class="px-5 py-3">{{ __('Idioma') }}</th>
                <th class="px-5 py-3">{{ __('Estado') }}</th>
                <th class="px-5 py-3 text-right">{{ __('Acciones') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($templates as $i => $t)
                @php
                  $st = strtoupper($t['status'] ?? '');
                  [$bg, $fg] = $statusMap[$st] ?? ['#f3f4f6', '#4b5563'];
                  $comps  = collect($t['components'] ?? []);
                  $header = $comps->firstWhere('type', 'HEADER');
                  $body   = $comps->firstWhere('type', 'BODY');
                  $footer = $comps->firstWhere('type', 'FOOTER');
                  $btns   = $comps->firstWhere('type', 'BUTTONS');
                  $hfmt   = strtoupper($header['format'] ?? '');
                  $langName = $langs[$t['language'] ?? ''] ?? null;
                  $mediaUrl = $header['example']['header_handle'][0] ?? null;
                @endphp
                <tr class="hover:bg-gray-50/60 transition">
                  <td class="px-5 py-3">
                    <button type="button" @click="open = open===@js($i) ? null : @js($i)"
                            class="inline-flex items-center gap-1.5 font-semibold text-indigo-700 hover:text-indigo-900">
                      <svg class="size-3.5 transition-transform" :class="open===@js($i) ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                      {{ $t['name'] ?? '—' }}
                    </button>
                  </td>
                  <td class="px-5 py-3 text-gray-600">{{ $catMap[strtoupper($t['category'] ?? '')] ?? ($t['category'] ?? '—') }}</td>
                  <td class="px-5 py-3 text-gray-600">
                    {{ $langName ? $langName : ($t['language'] ?? '—') }}
                    <span class="text-[10px] text-gray-400">{{ $langName ? '('.$t['language'].')' : '' }}</span>
                  </td>
                  <td class="px-5 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold" style="background:{{ $bg }};color:{{ $fg }};">{{ $stLabel[$st] ?? $st }}</span>
                  </td>
                  <td class="px-5 py-3 text-right">
                    <form method="POST" action="{{ route('whatsapp.templates.destroy', [$account, $t['name']]) }}"
                          onsubmit="return confirm('{{ __('¿Eliminar esta plantilla de Meta?') }}');" class="inline">
                      @csrf @method('DELETE')
                      <button type="submit" class="text-[11px] font-semibold text-red-600 hover:text-red-800">{{ __('Eliminar') }}</button>
                    </form>
                  </td>
                </tr>

                {{-- Fila de previsualización (se abre al hacer clic en el nombre) --}}
                <tr x-show="open===@js($i)" x-cloak>
                  <td colspan="5" class="px-5 py-5 bg-gray-50/70">
                    <div class="flex flex-col md:flex-row md:items-start gap-6">
                      <div class="shrink-0" style="width:300px;max-width:100%;">
                        <p class="text-[11px] font-semibold text-gray-500 mb-2">{{ __('Vista previa') }}</p>
                        <div style="background:#e5ddd5;border-radius:.6rem;padding:.6rem;">
                          <div style="background:#fff;border-radius:.55rem;padding:.55rem .65rem;box-shadow:0 1px 1px rgba(0,0,0,.1);font-size:12.5px;color:#111;">
                            @if($header && $hfmt === 'TEXT')
                              <div style="font-weight:700;margin-bottom:.25rem;">{{ $header['text'] ?? '' }}</div>
                            @elseif($hfmt === 'IMAGE' && $mediaUrl)
                              <img src="{{ $mediaUrl }}" alt="" style="width:100%;max-height:160px;object-fit:cover;border-radius:.4rem;display:block;margin-bottom:.4rem;">
                            @elseif($hfmt === 'VIDEO' && $mediaUrl)
                              <video src="{{ $mediaUrl }}" controls style="width:100%;max-height:180px;border-radius:.4rem;display:block;background:#000;margin-bottom:.4rem;"></video>
                            @elseif($hfmt === 'DOCUMENT' && $mediaUrl)
                              <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:.5rem;background:#f0f2f5;border-radius:.4rem;padding:.6rem;color:#54656f;font-size:12px;margin-bottom:.4rem;text-decoration:none;">
                                <span style="font-size:20px;">📄</span> {{ __('Ver documento') }}
                              </a>
                            @elseif($header && in_array($hfmt, ['IMAGE','VIDEO','DOCUMENT','LOCATION']))
                              <div style="background:#cfd8dc;border-radius:.4rem;height:120px;display:flex;align-items:center;justify-content:center;color:#607d8b;font-size:30px;margin-bottom:.4rem;">
                                {!! $hfmt==='IMAGE' ? '🖼️' : ($hfmt==='VIDEO' ? '🎬' : ($hfmt==='DOCUMENT' ? '📄' : '📍')) !!}
                              </div>
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

                      <div class="flex-1 min-w-0 text-xs text-gray-600 space-y-1">
                        <div><span class="font-semibold text-gray-500">ID:</span> {{ $t['id'] ?? '—' }}</div>
                        <div><span class="font-semibold text-gray-500">{{ __('Idioma') }}:</span> {{ $t['language'] ?? '—' }}</div>
                        <div><span class="font-semibold text-gray-500">{{ __('Categoría') }}:</span> {{ $t['category'] ?? '—' }}</div>
                        @if($st === 'REJECTED' && !empty($t['rejected_reason']))
                          <div class="text-red-500"><span class="font-semibold">{{ __('Motivo del rechazo') }}:</span> {{ $t['rejected_reason'] }}</div>
                        @endif
                      </div>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="px-5 py-3 text-[11px] text-gray-400 border-t border-gray-100">
          {{ __('Se muestran') }} {{ count($templates) }} {{ __('plantilla(s). Haz clic en el nombre para ver la previsualización.') }}
        </div>
      @endif
    </div>

    {{-- ===== Crear nueva plantilla ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 ring-1 ring-indigo-50 p-5"
         x-data="{
           name:'{{ old('name') }}', category:'{{ old('category','MARKETING') }}', language:'{{ old('language','es') }}',
           headerType:'{{ old('header_type','NONE') }}',
           header:{{ \Illuminate\Support\Js::from(old('header_text','')) }}, body:{{ \Illuminate\Support\Js::from(old('body','')) }},
           footer:{{ \Illuminate\Support\Js::from(old('footer_text','')) }}, buttons:[], sampleName:'', sampleUrl:'', submitting:false,
           onSample(e){ var f = e.target.files[0]; if(this.sampleUrl){ URL.revokeObjectURL(this.sampleUrl); } if(f){ this.sampleName=f.name; this.sampleUrl=URL.createObjectURL(f); } else { this.sampleName=''; this.sampleUrl=''; } },
           sanitizeName(){ this.name = (this.name||'').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,'').replace(/ñ/g,'n').replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,''); },
           get titleDisabled(){ return this.headerType !== 'NONE'; },
           get vars(){ var s=new Set(); (this.body.match(/\{\{\s*(\d+)\s*\}\}/g)||[]).forEach(function(x){ s.add(parseInt(x.replace(/[^0-9]/g,''),10)); }); return Array.from(s).sort(function(a,b){return a-b;}); },
           addButton(t){ if(this.buttons.length<3) this.buttons.push({type:t,text:'',value:''}); },
           removeButton(i){ this.buttons.splice(i,1); }
         }">
      <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
        <svg class="size-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        {{ __('Crear nueva plantilla') }}
      </h3>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Formulario --}}
        <form method="POST" action="{{ route('whatsapp.templates.store', $account) }}" class="space-y-3" enctype="multipart/form-data" @submit="submitting = true">
          @csrf
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Nombre') }} *</label>
              <input type="text" name="name" x-model="name" @input="sanitizeName()" required pattern="[a-z0-9_]+"
                     placeholder="bienvenida_cliente" class="w-full rounded-lg border-gray-200 text-sm py-2">
              <p class="text-[10px] text-gray-400 mt-1">{{ __('Los espacios se convierten en guion bajo; sin tildes ni ñ (se reemplaza por n).') }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Idioma') }} *</label>
              <select name="language" x-model="language" class="w-full rounded-lg border-gray-200 text-sm py-2">
                @foreach($langs as $code=>$lbl)
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
            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Muestra de contenido multimedia') }} <span class="text-gray-400 font-normal">· {{ __('Opcional') }}</span></label>
            <select name="header_type" x-model="headerType" class="w-full rounded-lg border-gray-200 text-sm py-2">
              <option value="NONE">{{ __('Ninguno') }}</option>
              <option value="IMAGE">{{ __('Imagen') }}</option>
              <option value="VIDEO">{{ __('Vídeo') }}</option>
              <option value="DOCUMENT">{{ __('Documento') }}</option>
              <option value="LOCATION">{{ __('Ubicación') }}</option>
            </select>
            {{-- Zona de carga de archivo para Imagen / Vídeo / Documento --}}
            <div x-show="headerType==='IMAGE' || headerType==='VIDEO' || headerType==='DOCUMENT'" x-cloak class="mt-2">
              @if($account->app_id)
                <label class="block cursor-pointer rounded-lg border-2 border-dashed border-gray-300 hover:border-indigo-400 bg-gray-50 px-4 py-6 text-center transition">
                  <input type="file" name="header_sample" class="hidden"
                         @change="onSample($event)"
                         :accept="headerType==='IMAGE' ? 'image/jpeg,image/png' : (headerType==='VIDEO' ? 'video/mp4,video/3gpp' : 'application/pdf')">
                  <template x-if="!sampleName">
                    <div class="text-sm text-gray-500">
                      {{ __('Arrastra y suelta para subir el archivo') }}<br>
                      <span class="text-indigo-600 font-medium">{{ __('O elige archivos de tu dispositivo') }}</span>
                    </div>
                  </template>
                  <template x-if="sampleName">
                    <div class="text-sm text-indigo-700 font-medium" x-text="'📎 ' + sampleName"></div>
                  </template>
                </label>
                <p class="text-[10px] text-gray-400 mt-1"
                   x-text="headerType==='IMAGE' ? '{{ __('Formatos: JPG, PNG (máx. 5 MB).') }}' : (headerType==='VIDEO' ? '{{ __('Formato: MP4 (máx. 16 MB).') }}' : '{{ __('Formato: PDF (máx. 100 MB).') }}')"></p>
                @error('header_sample') <p class="text-[10px] text-red-600 mt-1">{{ $message }}</p> @enderror
              @else
                <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                  {{ __('Para subir muestras de Imagen/Vídeo/Documento primero configura el App ID de Meta en') }}
                  <a href="{{ route('whatsapp.accounts.edit', $account) }}" class="underline font-medium">{{ __('Editar cuenta') }}</a>.
                  {{ __('Por ahora funcionan Ninguno (texto) y Ubicación.') }}
                </p>
              @endif
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Título (encabezado de texto)') }} <span class="text-gray-400 font-normal">· {{ __('Opcional') }}</span></label>
            <input type="text" name="header_text" x-model="header" :disabled="titleDisabled" maxlength="60"
                   class="w-full rounded-lg border-gray-200 text-sm py-2"
                   :style="titleDisabled ? 'background:#f3f4f6;color:#9ca3af;' : ''"
                   :placeholder="titleDisabled ? '{{ __('Bloqueado: el encabezado es multimedia') }}' : ''">
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
            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Botones') }} <span class="text-gray-400 font-normal">· {{ __('Opcional, hasta 3') }}</span></label>
            <div class="space-y-2">
              <template x-for="(b, i) in buttons" :key="i">
                <div class="rounded-lg border border-gray-200 p-2 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-gray-500"
                          x-text="b.type==='QUICK_REPLY' ? '{{ __('Respuesta rápida') }}' : (b.type==='URL' ? '{{ __('Enlace (URL)') }}' : '{{ __('Teléfono') }}')"></span>
                    <button type="button" @click="removeButton(i)" class="text-[11px] text-red-500 hover:text-red-700">{{ __('Quitar') }}</button>
                  </div>
                  <input type="hidden" :name="'buttons[' + i + '][type]'" :value="b.type">
                  <input type="text" :name="'buttons[' + i + '][text]'" x-model="b.text" maxlength="25"
                         placeholder="{{ __('Texto del botón') }}" class="w-full rounded-lg border-gray-200 text-xs py-1.5">
                  <template x-if="b.type==='URL'">
                    <input type="url" :name="'buttons[' + i + '][value]'" x-model="b.value"
                           placeholder="https://..." class="w-full rounded-lg border-gray-200 text-xs py-1.5">
                  </template>
                  <template x-if="b.type==='PHONE_NUMBER'">
                    <input type="text" :name="'buttons[' + i + '][value]'" x-model="b.value"
                           placeholder="{{ __('Número con código de país, ej. +51999...') }}" class="w-full rounded-lg border-gray-200 text-xs py-1.5">
                  </template>
                </div>
              </template>
            </div>
            <div class="mt-3 relative" x-data="{ open: false }" @click.away="open = false" x-show="buttons.length < 3">
              <button type="button" @click="open = !open"
                      class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg border-2 border-dashed border-indigo-300 text-indigo-600 hover:border-indigo-400 hover:bg-indigo-50 text-sm font-bold transition">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Añadir botón') }}
              </button>
              <div x-show="open" x-cloak class="absolute z-10 mt-1 w-full bg-white rounded-lg shadow-xl ring-1 ring-black/5 py-1 text-sm">
                <button type="button" @click="addButton('QUICK_REPLY'); open = false" class="block w-full text-left px-3 py-1.5 hover:bg-gray-100">{{ __('Respuesta rápida') }}</button>
                <button type="button" @click="addButton('URL'); open = false" class="block w-full text-left px-3 py-1.5 hover:bg-gray-100">{{ __('Ir al sitio web (enlace)') }}</button>
                <button type="button" @click="addButton('PHONE_NUMBER'); open = false" class="block w-full text-left px-3 py-1.5 hover:bg-gray-100">{{ __('Llamar al número de teléfono') }}</button>
              </div>
            </div>
          </div>

          <div class="pt-1 flex justify-end">
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition shadow-sm disabled:opacity-70 disabled:cursor-wait">
              <svg x-show="submitting" x-cloak class="animate-spin size-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path>
              </svg>
              <span x-show="!submitting">{{ __('Enviar a Meta para revisión') }}</span>
              <span x-show="submitting" x-cloak>{{ __('Enviando a Meta…') }}</span>
            </button>
          </div>
        </form>

        {{-- Previsualización en vivo --}}
        <div>
          <p class="text-xs font-semibold text-gray-500 mb-2">{{ __('Previsualización') }}</p>
          <div style="background:#e5ddd5;border-radius:.75rem;padding:1rem;min-height:180px;">
            <div style="background:#fff;border-radius:.6rem;padding:.6rem .7rem;box-shadow:0 1px 1px rgba(0,0,0,.1);font-size:13px;color:#111;max-width:280px;">
              <template x-if="headerType !== 'NONE'">
                <div style="margin-bottom:.4rem;">
                  {{-- Imagen real cargada --}}
                  <template x-if="headerType==='IMAGE' && sampleUrl">
                    <img :src="sampleUrl" alt="" style="width:100%;max-height:170px;object-fit:cover;border-radius:.4rem;display:block;">
                  </template>
                  {{-- Vídeo real cargado --}}
                  <template x-if="headerType==='VIDEO' && sampleUrl">
                    <video :src="sampleUrl" controls style="width:100%;max-height:190px;border-radius:.4rem;display:block;background:#000;"></video>
                  </template>
                  {{-- Documento real cargado --}}
                  <template x-if="headerType==='DOCUMENT' && sampleUrl">
                    <div style="background:#f0f2f5;border-radius:.4rem;padding:.6rem .7rem;display:flex;align-items:center;gap:.5rem;color:#54656f;font-size:12px;">
                      <span style="font-size:20px;">📄</span>
                      <span x-text="sampleName" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                    </div>
                  </template>
                  {{-- Placeholder: sin archivo o ubicación --}}
                  <template x-if="headerType==='LOCATION' || ((headerType==='IMAGE' || headerType==='VIDEO' || headerType==='DOCUMENT') && !sampleUrl)">
                    <div style="background:#f0f2f5;border-radius:.4rem;padding:1.1rem;text-align:center;color:#8696a0;font-size:11px;"
                         x-text="headerType==='IMAGE' ? '🖼️ {{ __('Imagen') }}' : (headerType==='VIDEO' ? '🎬 {{ __('Vídeo') }}' : (headerType==='DOCUMENT' ? '📄 {{ __('Documento') }}' : '📍 {{ __('Ubicación') }}'))"></div>
                  </template>
                </div>
              </template>
              <div x-show="headerType === 'NONE' && header" x-cloak style="font-weight:700;margin-bottom:.25rem;" x-text="header"></div>
              <div style="white-space:pre-line;" x-text="body || '{{ __('Escribe el cuerpo del mensaje…') }}'"></div>
              <div x-show="footer" x-cloak style="color:#667781;font-size:11px;margin-top:.35rem;" x-text="footer"></div>
            </div>
            <div style="margin-top:.4rem;max-width:280px;display:flex;flex-direction:column;gap:.3rem;">
              <template x-for="(b, i) in buttons" :key="i">
                <div x-show="b.text" x-cloak style="background:#fff;border-radius:.6rem;text-align:center;padding:.45rem;color:#1ea0e6;font-size:13px;font-weight:600;" x-text="b.text"></div>
              </template>
            </div>
          </div>
          <p class="text-[11px] text-gray-400 mt-2">{{ __('Las plantillas pasan por revisión de Meta. Pueden tardar de minutos a horas en aprobarse.') }}</p>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
