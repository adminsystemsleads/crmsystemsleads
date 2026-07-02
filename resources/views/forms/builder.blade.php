<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center gap-2">
      <a href="{{ route('formularios.index') }}" class="text-gray-400 hover:text-gray-600">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </a>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $form ? __('Editar formulario') : __('Nuevo formulario') }}
      </h2>
    </div>
  </x-slot>

  @php
    // Si hubo error de validación, recupera los campos que el usuario tenía sin guardar.
    $oldDecoded = old('fields_json') ? json_decode(old('fields_json'), true) : null;
    $initialFields = (is_array($oldDecoded) && count($oldDecoded))
      ? $oldDecoded
      : (count($fields) ? $fields : [
          ['source' => 'core', 'core_key' => 'name',  'label' => null, 'placeholder' => null, 'is_required' => true],
          ['source' => 'core', 'core_key' => 'email', 'label' => null, 'placeholder' => null, 'is_required' => false],
          ['source' => 'core', 'core_key' => 'phone', 'label' => null, 'placeholder' => null, 'is_required' => false],
        ]);
  @endphp

  <div class="max-w-6xl mx-auto px-4 py-8"
       x-data="formBuilder({
         pipelines: @js($pipelines),
         users: @js($users),
         customFields: @js($customFields),
         initialFields: @js($initialFields),
         initial: {
           pipeline_id: '{{ $form?->pipeline_id }}',
           stage_id: '{{ $form?->stage_id }}',
           move_stage_id: '{{ $form?->move_stage_id }}',
           deal_dedup_mode: '{{ $form?->deal_dedup_mode ?? 'always_create' }}',
         }
       })">

    @if(session('success'))
      <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-0.5">
          @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ $form ? route('formularios.update', $form) : route('formularios.store') }}">
      @csrf
      @if($form) @method('PUT') @endif
      <input type="hidden" name="fields_json" :value="JSON.stringify(fields)">

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ================= COLUMNA IZQUIERDA: CONFIGURACIÓN ================= --}}
        <div class="space-y-5">

          {{-- Datos generales --}}
          <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Contenido') }}</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Nombre interno') }} *</label>
                <input type="text" name="name" value="{{ old('name', $form?->name ?? '') }}" required
                       placeholder="{{ __('Ej: Contáctanos') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="text-[11px] text-gray-400 mt-1">{{ __('Solo lo ves tú; no aparece en el formulario.') }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Título') }}</label>
                <input type="text" name="title" x-model="f.title"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Subtítulo') }}</label>
                <textarea name="subtitle" x-model="f.subtitle" rows="2"
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Texto del botón') }}</label>
                <input type="text" name="button_text" x-model="f.button_text"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Mensaje de éxito') }}</label>
                <textarea name="success_message" rows="2" placeholder="{{ __('¡Gracias! Hemos recibido tus datos.') }}"
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('success_message', $form?->success_message ?? '') }}</textarea>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('URL de redirección (opcional)') }}</label>
                <input type="url" name="redirect_url" value="{{ old('redirect_url', $form?->redirect_url ?? '') }}"
                       placeholder="https://…"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="text-[11px] text-gray-400 mt-1">{{ __('Si se define, al enviar se redirige a esta página en vez de mostrar el mensaje.') }}</p>
              </div>
            </div>
          </div>

          {{-- Diseño --}}
          <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Diseño') }}</h3>
            <div class="grid grid-cols-2 gap-3">
              <label class="flex items-center justify-between gap-2 text-xs font-medium text-gray-600">
                {{ __('Fondo de página') }}
                <input type="color" name="bg_color" x-model="f.bg_color" class="h-8 w-12 rounded border border-gray-200 cursor-pointer">
              </label>
              <label class="flex items-center justify-between gap-2 text-xs font-medium text-gray-600">
                {{ __('Tarjeta') }}
                <input type="color" name="card_color" x-model="f.card_color" class="h-8 w-12 rounded border border-gray-200 cursor-pointer">
              </label>
              <label class="flex items-center justify-between gap-2 text-xs font-medium text-gray-600">
                {{ __('Texto') }}
                <input type="color" name="text_color" x-model="f.text_color" class="h-8 w-12 rounded border border-gray-200 cursor-pointer">
              </label>
              <label class="flex items-center justify-between gap-2 text-xs font-medium text-gray-600">
                {{ __('Botón / acento') }}
                <input type="color" name="primary_color" x-model="f.primary_color" class="h-8 w-12 rounded border border-gray-200 cursor-pointer">
              </label>
              <label class="flex items-center justify-between gap-2 text-xs font-medium text-gray-600">
                {{ __('Texto del botón') }}
                <input type="color" name="button_text_color" x-model="f.button_text_color" class="h-8 w-12 rounded border border-gray-200 cursor-pointer">
              </label>
            </div>
          </div>

          {{-- Campos --}}
          <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-1">{{ __('Campos del formulario') }}</h3>
            <p class="text-xs text-gray-400 mb-4">{{ __('El campo Nombre es obligatorio y siempre se incluye.') }}</p>

            {{-- Lista de campos seleccionados --}}
            <div class="space-y-2 mb-4">
              <template x-for="(field, i) in fields" :key="i">
                <div class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 bg-gray-50">
                  <div class="flex flex-col">
                    <button type="button" @click="moveUp(i)" :disabled="i===0" class="text-gray-300 hover:text-gray-600 disabled:opacity-30 leading-none">▲</button>
                    <button type="button" @click="moveDown(i)" :disabled="i===fields.length-1" class="text-gray-300 hover:text-gray-600 disabled:opacity-30 leading-none">▼</button>
                  </div>
                  <div class="flex-1 min-w-0">
                    <input type="text" x-model="field.label" :placeholder="defaultLabel(field)"
                           class="w-full rounded border-gray-200 text-xs py-1 focus:border-indigo-500 focus:ring-indigo-500">
                    <div class="mt-1 flex items-center gap-2 text-[10px]">
                      <span class="inline-flex items-center rounded px-1.5 py-0.5 font-semibold"
                            :class="fieldTag(field).cls" x-text="fieldTag(field).text"></span>
                    </div>
                  </div>
                  <label class="flex items-center gap-1 text-[11px] text-gray-500 whitespace-nowrap">
                    <input type="checkbox" x-model="field.is_required"
                           :disabled="field.source==='core' && field.core_key==='name'"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    {{ __('Oblig.') }}
                  </label>
                  <button type="button" @click="removeField(i)"
                          x-show="!(field.source==='core' && field.core_key==='name')"
                          class="text-gray-300 hover:text-red-500">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>
              </template>
            </div>

            {{-- Agregar campos base --}}
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1.5">{{ __('Datos de contacto') }}</p>
            <div class="flex flex-wrap gap-1.5 mb-3">
              <template x-for="c in coreCatalog" :key="c.core_key">
                <button type="button" @click="addCore(c.core_key)" x-show="!coreUsed(c.core_key)"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border border-dashed border-gray-300 text-xs text-gray-600 hover:border-indigo-400 hover:text-indigo-600">
                  <span>+</span><span x-text="c.label"></span>
                </button>
              </template>
            </div>

            {{-- Agregar campos personalizados --}}
            <template x-if="customFields.length">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1.5">{{ __('Campos personalizados') }}</p>
                <div class="flex flex-wrap gap-1.5">
                  <template x-for="cf in customFields" :key="cf.id">
                    <button type="button" @click="addCustom(cf.id)" x-show="!customUsed(cf.id)"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border border-dashed border-gray-300 text-xs text-gray-600 hover:border-indigo-400 hover:text-indigo-600">
                      <span>+</span><span x-text="cf.name"></span>
                      <span class="text-[9px] opacity-60" x-text="cf.entity_type==='deal' ? '{{ __('Negociación') }}' : '{{ __('Contacto') }}'"></span>
                    </button>
                  </template>
                </div>
              </div>
            </template>
            <p x-show="!customFields.length" class="text-[11px] text-gray-400">
              {{ __('No tienes campos personalizados. Créalos en Configuración → Campos personalizados.') }}
            </p>
          </div>

          {{-- Destino: embudo y negociación --}}
          <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Destino de la negociación') }}</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Embudo') }}</label>
                <select name="pipeline_id" x-model="f.pipeline_id"
                        x-init="$nextTick(() => { $el.value = f.pipeline_id })"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">{{ __('— Embudo predeterminado —') }}</option>
                  <template x-for="p in pipelines" :key="p.id">
                    <option :value="String(p.id)" x-text="p.name"></option>
                  </template>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Etapa de ingreso (negociaciones nuevas)') }}</label>
                <select name="stage_id" x-model="f.stage_id"
                        x-init="$nextTick(() => { $el.value = f.stage_id })"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">{{ __('— Primera etapa —') }}</option>
                  <template x-for="s in currentStages()" :key="s.id">
                    <option :value="String(s.id)" x-text="s.name"></option>
                  </template>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Responsable de las negociaciones') }}</label>
                <select name="assigned_user_id" x-model="f.assigned_user_id"
                        x-init="$nextTick(() => { $el.value = f.assigned_user_id })"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">{{ __('— Sin asignar —') }}</option>
                  <template x-for="u in users" :key="u.id">
                    <option :value="String(u.id)" x-text="u.name"></option>
                  </template>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Título de la negociación') }}</label>
                <input type="text" name="deal_title_template" value="{{ old('deal_title_template', $form?->deal_title_template ?? '{form} - {name}') }}"
                       class="w-full rounded-lg border-gray-300 text-sm font-mono focus:border-indigo-500 focus:ring-indigo-500">
                <p class="text-[11px] text-gray-400 mt-1">{{ __('Variables:') }} <code>{form}</code> <code>{name}</code> <code>{email}</code> <code>{phone}</code></p>
              </div>
            </div>
          </div>

          {{-- Duplicados --}}
          <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-1">{{ __('Manejo de duplicados') }}</h3>
            <p class="text-xs text-gray-400 mb-4">{{ __('El contacto nunca se duplica: si el teléfono o correo ya existen, se reutiliza el contacto.') }}</p>

            <p class="text-xs font-medium text-gray-500 mb-2">{{ __('¿Y la negociación?') }}</p>
            <label class="flex items-start gap-2 rounded-lg border px-3 py-2 mb-2 cursor-pointer"
                   :class="f.deal_dedup_mode==='always_create' ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200'">
              <input type="radio" name="deal_dedup_mode" value="always_create" x-model="f.deal_dedup_mode" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
              <span class="text-sm text-gray-700">
                <span class="font-medium">{{ __('Crear siempre una nueva negociación') }}</span>
                <span class="block text-xs text-gray-400">{{ __('Aunque ya exista una negociación activa en ese embudo.') }}</span>
              </span>
            </label>
            <label class="flex items-start gap-2 rounded-lg border px-3 py-2 cursor-pointer"
                   :class="f.deal_dedup_mode==='use_active' ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200'">
              <input type="radio" name="deal_dedup_mode" value="use_active" x-model="f.deal_dedup_mode" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
              <span class="text-sm text-gray-700">
                <span class="font-medium">{{ __('Usar la negociación activa (no duplicar)') }}</span>
                <span class="block text-xs text-gray-400">{{ __('Si hay una negociación abierta del contacto en ese embudo, se reutiliza y se agrega un comentario.') }}</span>
              </span>
            </label>

            {{-- Opción extra: mover a etapa (solo si usa la activa) --}}
            <div x-show="f.deal_dedup_mode==='use_active'" x-cloak class="mt-3 pl-3 border-l-2 border-indigo-200">
              <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Al reutilizar, mover la negociación a la etapa:') }}</label>
              <select name="move_stage_id" x-model="f.move_stage_id"
                      x-init="$nextTick(() => { $el.value = f.move_stage_id })"
                      class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('— No mover (dejar donde está) —') }}</option>
                <template x-for="s in currentStages()" :key="s.id">
                  <option :value="String(s.id)" x-text="s.name"></option>
                </template>
              </select>
            </div>
          </div>

          {{-- Estado --}}
          <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5">
            <label class="flex items-center justify-between gap-3 cursor-pointer">
              <span>
                <span class="font-medium text-gray-800 text-sm">{{ __('Formulario activo') }}</span>
                <span class="block text-xs text-gray-400">{{ __('Si está inactivo, el enlace público no captará datos.') }}</span>
              </span>
              <input type="checkbox" name="is_active" value="1" x-model="f.is_active"
                     class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 size-5">
            </label>
          </div>

          <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
              {{ $form ? __('Guardar cambios') : __('Crear formulario') }}
            </button>
            <a href="{{ route('formularios.index') }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('Cancelar') }}</a>
          </div>
        </div>

        {{-- ================= COLUMNA DERECHA: PREVIEW + COMPARTIR ================= --}}
        <div class="space-y-5">
          <div class="lg:sticky lg:top-6 space-y-5">

            {{-- Vista previa --}}
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">{{ __('Vista previa') }}</p>
              <div class="rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="p-6" :style="`background:${f.bg_color}`">
                  <div class="max-w-sm mx-auto rounded-xl shadow-sm p-6" :style="`background:${f.card_color};color:${f.text_color}`">
                    <h3 class="text-lg font-bold" x-text="f.title || '{{ __('Título del formulario') }}'"></h3>
                    <p class="text-sm opacity-70 mt-1" x-show="f.subtitle" x-text="f.subtitle"></p>
                    <div class="mt-4 space-y-3">
                      <template x-for="(field, i) in fields" :key="i">
                        <div>
                          <label class="block text-xs font-medium mb-1" x-text="displayLabel(field) + (field.is_required ? ' *' : '')"></label>
                          <div class="w-full rounded-lg border px-3 py-2 text-sm opacity-70"
                               style="border-color:rgba(0,0,0,.15)" x-text="previewPlaceholder(field)"></div>
                        </div>
                      </template>
                    </div>
                    <button type="button" class="mt-5 w-full rounded-lg py-2.5 text-sm font-semibold"
                            :style="`background:${f.primary_color};color:${f.button_text_color}`"
                            x-text="f.button_text || '{{ __('Enviar') }}'"></button>
                  </div>
                </div>
              </div>
            </div>

            {{-- Compartir / incrustar (solo al editar) --}}
            @if($form)
              <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-3">{{ __('Compartir') }}</h3>

                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Enlace público') }}</label>
                <div class="flex gap-2 mb-4">
                  <input type="text" readonly value="{{ $form?->public_url }}" id="pubLink"
                         class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-xs text-gray-600">
                  <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('pubLink').value)"
                          class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700">{{ __('Copiar') }}</button>
                  <a href="{{ $form?->public_url }}" target="_blank" rel="noopener"
                     class="px-3 py-2 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50">{{ __('Abrir') }}</a>
                </div>

                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Incrustar en tu web') }}</label>
                <div class="flex gap-2">
                  <input type="text" readonly id="embedCode"
                         value='<script src="{{ route('public.form.embed', $form?->slug) }}" async></script>'
                         class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-xs text-gray-600 font-mono">
                  <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('embedCode').value)"
                          class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700">{{ __('Copiar') }}</button>
                </div>
                <p class="text-[11px] text-gray-400 mt-1.5">{{ __('Pega este código en el HTML de tu sitio donde quieras que aparezca el formulario.') }}</p>
              </div>
            @else
              <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-700">
                {{ __('Guarda el formulario para obtener el enlace público y el código para incrustar.') }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </form>
  </div>

  <script>
    function formBuilder(cfg) {
      return {
        pipelines: cfg.pipelines || [],
        users: cfg.users || [],
        customFields: cfg.customFields || [],
        fields: (cfg.initialFields || []).map(x => ({
          source: x.source,
          core_key: x.core_key ?? null,
          custom_field_id: x.custom_field_id ?? null,
          label: x.label ?? '',
          placeholder: x.placeholder ?? '',
          is_required: !!x.is_required,
        })),
        coreCatalog: [
          { core_key: 'name',    label: '{{ __('Nombre') }}' },
          { core_key: 'email',   label: '{{ __('Correo electrónico') }}' },
          { core_key: 'phone',   label: '{{ __('Teléfono') }}' },
          { core_key: 'company', label: '{{ __('Empresa') }}' },
        ],
        f: {
          title: @js(old('title', $form?->title ?? '')),
          subtitle: @js(old('subtitle', $form?->subtitle ?? '')),
          button_text: @js(old('button_text', $form?->button_text ?? 'Enviar')),
          bg_color: '{{ old('bg_color', $form?->bg_color ?? '#f3f4f6') }}',
          card_color: '{{ old('card_color', $form?->card_color ?? '#ffffff') }}',
          text_color: '{{ old('text_color', $form?->text_color ?? '#1f2937') }}',
          primary_color: '{{ old('primary_color', $form?->primary_color ?? '#4f46e5') }}',
          button_text_color: '{{ old('button_text_color', $form?->button_text_color ?? '#ffffff') }}',
          pipeline_id: cfg.initial.pipeline_id || '',
          stage_id: cfg.initial.stage_id || '',
          move_stage_id: cfg.initial.move_stage_id || '',
          assigned_user_id: '{{ old('assigned_user_id', $form?->assigned_user_id ?? '') }}',
          deal_dedup_mode: cfg.initial.deal_dedup_mode || 'always_create',
          is_active: {{ old('is_active', $form?->is_active ?? true) ? 'true' : 'false' }},
        },

        coreUsed(k) { return this.fields.some(f => f.source === 'core' && f.core_key === k); },
        customUsed(id) { return this.fields.some(f => f.source === 'custom' && f.custom_field_id === id); },

        addCore(k) {
          if (this.coreUsed(k)) return;
          this.fields.push({ source: 'core', core_key: k, custom_field_id: null, label: '', placeholder: '', is_required: k === 'name' });
        },
        addCustom(id) {
          if (this.customUsed(id)) return;
          this.fields.push({ source: 'custom', core_key: null, custom_field_id: id, label: '', placeholder: '', is_required: false });
        },
        removeField(i) {
          const f = this.fields[i];
          if (f.source === 'core' && f.core_key === 'name') return;
          this.fields.splice(i, 1);
        },
        moveUp(i)   { if (i > 0) this.fields.splice(i - 1, 0, this.fields.splice(i, 1)[0]); },
        moveDown(i) { if (i < this.fields.length - 1) this.fields.splice(i + 1, 0, this.fields.splice(i, 1)[0]); },

        customById(id) { return this.customFields.find(c => c.id === id); },
        defaultLabel(field) {
          if (field.source === 'custom') return this.customById(field.custom_field_id)?.name || 'Campo';
          const c = this.coreCatalog.find(x => x.core_key === field.core_key);
          return c ? c.label : field.core_key;
        },
        displayLabel(field) { return field.label || this.defaultLabel(field); },
        fieldTag(field) {
          if (field.source === 'custom') {
            const cf = this.customById(field.custom_field_id);
            const isDeal = cf && cf.entity_type === 'deal';
            return {
              text: (isDeal ? '{{ __('Negociación') }}' : '{{ __('Contacto') }}') + ' · ' + (cf ? cf.field_type : ''),
              cls: isDeal ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700',
            };
          }
          return { text: '{{ __('Contacto') }}', cls: 'bg-blue-100 text-blue-700' };
        },
        previewPlaceholder(field) {
          if (field.placeholder) return field.placeholder;
          if (field.source === 'custom') {
            const t = this.customById(field.custom_field_id)?.field_type;
            if (t === 'select' || t === 'multiselect') return '{{ __('Selecciona una opción') }}';
            if (t === 'date') return 'dd/mm/aaaa';
          }
          return '{{ __('Escribe aquí…') }}';
        },

        selectedPipeline() { return this.pipelines.find(p => String(p.id) === String(this.f.pipeline_id)); },
        currentStages() {
          const p = this.selectedPipeline();
          return p ? p.stages : [];
        },
      };
    }
  </script>
</x-app-layout>
