<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center gap-3">
      <a href="{{ route('whatsapp.accounts.index') }}" class="text-gray-400 hover:text-gray-600">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Asistente IA') }} — {{ $account->name }}
      </h2>
      @if($assistant?->is_active)
        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700">
          <span class="size-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>
          {{ __('Activo') }}
        </span>
      @endif
    </div>
  </x-slot>

  <div class="py-8">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

      {{-- Alerta de estado --}}
      @if(session('status'))
        <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 flex items-center gap-2">
          <svg class="size-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          {{ session('status') }}
        </div>
      @endif
      @if(session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 flex items-start gap-2">
          <svg class="size-4 shrink-0 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 13l.94 4.7A2 2 0 007.9 19h8.2a2 2 0 001.96-1.3L19 13M19 13H5m14 0L17.1 5.3A2 2 0 0015.14 4H8.86a2 2 0 00-1.96 1.3L5 13"/>
          </svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif
      @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
          <p class="font-semibold mb-1">{{ __('Errores de validación:') }}</p>
          <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Card info --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-start gap-4">
        <div class="size-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
          <svg class="size-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1 1 .03 2.798-1.304 2.628l-1.44-.21m-10.856.21l-1.44.21c-1.332.169-2.304-1.628-1.304-2.628L5 14.5"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-900">{{ __('Asistente IA con ChatGPT') }}</p>
          <p class="text-xs text-gray-500 mt-1 leading-relaxed">
            {{ __('Cuando esté activo, el asistente responderá automáticamente los mensajes de texto entrantes en') }}
            <strong>{{ $account->name }}</strong> {{ __('usando tu API Key de OpenAI. Cada cliente usa su propia clave.') }}
          </p>
        </div>
      </div>

      {{-- Formulario principal --}}
      <form method="POST" action="{{ route('whatsapp.ai.update', $account) }}"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
        @csrf
        @method('PUT')

        {{-- Activar/desactivar --}}
        <div class="px-6 py-5 flex items-center justify-between gap-4">
          <div>
            <p class="text-sm font-semibold text-gray-900">{{ __('Activar asistente') }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ __('Al activar, el bot responderá mensajes automáticamente.') }}</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                   {{ old('is_active', $assistant?->is_active) ? 'checked' : '' }}>
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                        peer-checked:after:translate-x-full peer-checked:after:border-white
                        after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                        after:bg-white after:border-gray-300 after:border after:rounded-full
                        after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
          </label>
        </div>

        {{-- API Key --}}
        <div class="px-6 py-5">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('API Key de OpenAI') }}
            <span class="text-gray-400 font-normal">({{ __('requerida') }})</span>
          </label>
          <input type="password" name="api_key"
                 placeholder="{{ $assistant?->api_key ? __('••••••••••••••••••••••••••• (ya guardada)') : 'sk-...' }}"
                 class="w-full border-gray-300 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                 autocomplete="off">
          @error('api_key')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
          <p class="text-xs text-gray-400 mt-1.5">
            {{ __('Obtén tu clave en') }}
            <a href="https://platform.openai.com/api-keys" target="_blank" class="text-indigo-600 underline">platform.openai.com/api-keys</a>.
            {{ __('Se almacena cifrada. Déjala vacía para no cambiarla.') }}
          </p>
        </div>

        {{-- Modelo --}}
        <div class="px-6 py-5">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Modelo de ChatGPT') }}</label>
          <select name="model" class="w-full border-gray-300 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            @foreach($models as $value => $label)
              <option value="{{ $value }}" {{ old('model', $assistant?->model ?? 'gpt-4o-mini') === $value ? 'selected' : '' }}>
                {{ $label }}
              </option>
            @endforeach
          </select>
          @error('model')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- System Prompt --}}
        <div class="px-6 py-5">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ __('Instrucciones del asistente') }}
            <span class="text-gray-400 font-normal">({{ __('system prompt') }})</span>
          </label>
          <textarea name="system_prompt" rows="5"
                    class="w-full border-gray-300 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="{{ __('Eres un asistente de ventas amable y profesional. Responde siempre en español. Cuando el cliente quiera hablar con un humano, dile que un agente lo atenderá pronto.') }}">{{ old('system_prompt', $assistant?->system_prompt) }}</textarea>
          @error('system_prompt')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
          <p class="text-xs text-gray-400 mt-1">{{ __('Define el comportamiento, tono y límites del asistente. Máx. 4000 caracteres.') }}</p>
        </div>

        {{-- Parámetros avanzados --}}
        <div class="px-6 py-5" x-data="{ open: false }">
          <button type="button" @click="open = !open"
                  class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900">
            <svg class="size-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            {{ __('Parámetros avanzados') }}
          </button>

          <div x-show="open" x-transition class="mt-4 space-y-5">

            {{-- Temperatura --}}
            <div x-data="{ val: {{ old('temperature', $assistant?->temperature ?? 0.7) }} }">
              <div class="flex items-center justify-between mb-1">
                <label class="text-sm font-medium text-gray-700">{{ __('Temperatura (creatividad)') }}</label>
                <span class="text-sm font-semibold text-indigo-600" x-text="val"></span>
              </div>
              <input type="range" name="temperature" min="0" max="2" step="0.1"
                     x-model="val"
                     class="w-full accent-indigo-600">
              <div class="flex justify-between text-[10px] text-gray-400 mt-0.5">
                <span>{{ __('Preciso (0)') }}</span>
                <span>{{ __('Balanceado (0.7)') }}</span>
                <span>{{ __('Creativo (2)') }}</span>
              </div>
              @error('temperature')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Max Tokens --}}
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('Longitud máxima de respuesta') }}
                <span class="text-gray-400 font-normal">({{ __('tokens') }})</span>
              </label>
              <input type="number" name="max_tokens" min="50" max="4000" step="50"
                     value="{{ old('max_tokens', $assistant?->max_tokens ?? 500) }}"
                     class="w-full border-gray-300 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
              <p class="text-xs text-gray-400 mt-1">{{ __('~75 palabras por cada 100 tokens. Recomendado: 300-800.') }}</p>
              @error('max_tokens')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Contexto --}}
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Mensajes de contexto') }}</label>
              <input type="number" name="context_messages" min="1" max="50"
                     value="{{ old('context_messages', $assistant?->context_messages ?? 20) }}"
                     class="w-full border-gray-300 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
              <p class="text-xs text-gray-400 mt-1">{{ __('Cuántos mensajes previos de la conversación se envían como contexto al modelo.') }}</p>
              @error('context_messages')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

          </div>
        </div>

        {{-- ============ FUNCTION CALLING ============ --}}
        <div class="px-6 py-5 border-t border-gray-100">
          <div class="rounded-xl border-2 border-indigo-100 bg-gradient-to-br from-indigo-50/50 to-violet-50/50 p-5">
            <div class="flex items-start gap-3 mb-4">
              <div class="size-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="text-sm font-bold text-gray-900">{{ __('Funciones IA personalizadas') }}</h3>
                <p class="text-xs text-gray-600 mt-0.5">
                  {{ __('Define acciones que la IA ejecutará cuando detecte cierta situación en la conversación.') }}
                  {{ __('Cada función es independiente con su propia descripción de cuándo activarla.') }}
                </p>
              </div>
              <label class="inline-flex items-center cursor-pointer shrink-0" title="{{ __('Activar function calling') }}">
                <input type="hidden" name="function_calling_enabled" value="0">
                <input type="checkbox" name="function_calling_enabled" value="1" class="sr-only peer"
                       {{ old('function_calling_enabled', $assistant?->function_calling_enabled) ? 'checked' : '' }}>
                <span class="relative w-10 h-5 bg-gray-300 peer-checked:bg-indigo-600 rounded-full transition
                            after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4
                            after:bg-white after:rounded-full after:transition peer-checked:after:translate-x-5"></span>
              </label>
            </div>

            @if($assistant)
              <div id="aiFunctionsListBox" class="space-y-2 mb-3"></div>
              <button type="button" onclick="openAiFunctionModal()"
                      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Nueva función') }}
              </button>
              <p class="text-[11px] text-gray-500 mt-3 italic">
                💡 {{ __('Tip: Funciona mejor con GPT-4o / GPT-4o Mini. La IA decide cuándo llamar a cada función leyendo su descripción.') }}
              </p>
            @else
              <p class="text-xs text-gray-500 italic">{{ __('Guarda primero la configuración del asistente para poder crear funciones.') }}</p>
            @endif
          </div>
        </div>

        {{-- ============ BASE DE CONOCIMIENTO ============ --}}
        @if($assistant)
        <div class="px-6 py-5 border-t border-gray-100">
          <div class="rounded-xl border-2 border-emerald-100 bg-gradient-to-br from-emerald-50/40 to-teal-50/40 p-5">
            <div class="flex items-start gap-3 mb-4">
              <div class="size-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="text-sm font-bold text-gray-900">{{ __('Base de conocimiento') }}</h3>
                <p class="text-xs text-gray-600 mt-0.5">
                  {{ __('Sube archivos (TXT, MD, PDF, DOCX) o pega texto. La IA usará esta información para responder con datos oficiales de tu empresa.') }}
                </p>
              </div>
              <span id="kbUsageBadge" class="text-[10px] text-gray-500 shrink-0"></span>
            </div>

            <div id="kbList" class="space-y-2 mb-3"></div>

            <div class="flex flex-wrap gap-2">
              <button type="button" onclick="kbOpenFileModal()"
                      class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                {{ __('Subir archivo') }}
              </button>
              <button type="button" onclick="kbOpenTextModal()"
                      class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m-7-7h14"/>
                </svg>
                {{ __('Pegar texto') }}
              </button>
            </div>

            <p class="text-[11px] text-gray-500 mt-3 italic">
              💡 {{ __('La IA recibe esta info en cada respuesta. Mantén textos concisos para mejores resultados.') }}
            </p>
          </div>
        </div>
        @endif

        {{-- Botones --}}
        <div class="px-6 py-4 flex items-center justify-between gap-3 bg-gray-50 rounded-b-2xl">
          <div>
            @if($assistant)
              {{-- ⚠ NO anidamos otro <form> dentro del form principal: usamos form="aiAssistantDeleteForm" --}}
              <button type="submit" form="aiAssistantDeleteForm"
                      onclick="return confirm('{{ __('¿Eliminar la configuración del asistente IA?') }}')"
                      class="text-xs text-red-500 hover:text-red-700 underline">
                {{ __('Eliminar configuración') }}
              </button>
            @endif
          </div>
          <div class="flex gap-2">
            <a href="{{ route('whatsapp.accounts.index') }}"
               class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-100 transition">
              {{ __('Cancelar') }}
            </a>
            <button type="submit"
                    class="px-5 py-2 text-sm bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium">
              {{ __('Guardar cambios') }}
            </button>
          </div>
        </div>

      </form>

      {{-- Form de eliminación FUERA del form principal — referenciado por el botón vía form="..." --}}
      @if($assistant)
        <form id="aiAssistantDeleteForm" method="POST"
              action="{{ route('whatsapp.ai.destroy', $account) }}" class="hidden">
          @csrf @method('DELETE')
        </form>
      @endif

      {{-- Info adicional --}}
      <div class="rounded-xl bg-amber-50 border border-amber-200 px-5 py-4 text-xs text-amber-800 space-y-1.5">
        <p class="font-semibold text-sm text-amber-900">{{ __('¿Cómo funciona?') }}</p>
        <ul class="space-y-1 list-disc list-inside">
          <li>{{ __('El asistente sólo responde mensajes de') }} <strong>{{ __('texto') }}</strong> {{ __('entrantes.') }}</li>
          <li>{{ __('Si el mensaje es una imagen, audio o video,') }} <strong>{{ __('no responde') }}</strong> {{ __('automáticamente.') }}</li>
          <li>{{ __('Cada respuesta usa tus créditos de OpenAI — revisa tu uso en platform.openai.com.') }}</li>
          <li>{{ __('El asistente ve los últimos') }} <strong>{{ $assistant?->context_messages ?? 20 }} {{ __('mensajes') }}</strong> {{ __('de la conversación como contexto.') }}</li>
          <li>{{ __('Los mensajes enviados por el asistente aparecen en el inbox sin nombre de agente.') }}</li>
        </ul>
      </div>

    </div>
  </div>

@if($assistant)
{{-- ════════════════════════════════════════
     MODAL CREAR / EDITAR FUNCIÓN IA
     ════════════════════════════════════════ --}}
<div id="aiFunctionModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
      <h3 class="text-base font-bold text-gray-900" id="aiFnModalTitle">{{ __('Crear función IA') }}</h3>
      <button type="button" onclick="closeAiFunctionModal()"
              class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div id="aiFnError" class="hidden mx-5 mt-3 px-3 py-2 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700"></div>

    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
      <input type="hidden" id="aiFnId" value="">

      {{-- Modo --}}
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Modo') }} *</label>
        <select id="aiFnMode" class="w-full rounded-lg border-gray-200 text-sm py-2"
                onchange="aiFnUpdateModeUI()">
          <option value="update_crm">{{ __('Actualizar CRM (capturar campos)') }}</option>
          <option value="change_stage">{{ __('Cambiar fase de la negociación') }}</option>
          <option value="info">{{ __('Solo información (responder al cliente)') }}</option>
        </select>
      </div>

      {{-- Nombre --}}
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Nombre interno (snake_case)') }} *</label>
        <input type="text" id="aiFnName" maxlength="60" placeholder="{{ __('ej: save_lead_data') }}"
               class="w-full rounded-lg border-gray-200 text-sm py-2 font-mono">
        <p class="text-[10px] text-gray-400 mt-1">{{ __('Solo letras minúsculas, números y guiones bajos. Sin espacios.') }}</p>
      </div>

      {{-- Descripción --}}
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('¿Cuándo se activa esta función?') }} *</label>
        <textarea id="aiFnDescription" rows="3" maxlength="2000"
                  placeholder="{{ __('Ej: Llama esta función cuando el cliente comparta su nombre completo, RUC o datos de contacto.') }}"
                  class="w-full rounded-lg border-gray-200 text-sm py-2"></textarea>
        <p class="text-[10px] text-gray-400 mt-1">{{ __('La IA leerá este texto para decidir cuándo ejecutar la función.') }}</p>
      </div>

      {{-- Properties (solo update_crm) --}}
      <div id="aiFnPropertiesBox">
        <div class="flex items-center justify-between mb-1">
          <label class="block text-xs font-semibold text-gray-600">{{ __('Campos del CRM a capturar') }}</label>
          <button type="button" onclick="aiFnLoadFields()"
                  class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold">
            ↻ {{ __('Recargar campos') }}
          </button>
        </div>
        <div class="ms-dd" id="aiFnPropertiesDD">
          <button type="button" class="ms-dd-btn" onclick="msToggle(this)">
            <span class="ms-dd-label placeholder" data-placeholder="{{ __('— Seleccionar —') }}" data-count-label="{{ __('campos') }}">{{ __('— Seleccionar —') }}</span>
            <svg class="ms-dd-caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div class="ms-dd-panel" id="aiFnProperties">
            <span class="px-2 py-1 text-xs text-gray-400">{{ __('Cargando campos…') }}</span>
          </div>
        </div>
      </div>

      {{-- Stage (solo change_stage) --}}
      <div id="aiFnStageBox" class="hidden">
        <div class="flex items-center justify-between mb-1">
          <label class="block text-xs font-semibold text-gray-600">{{ __('Fase destino') }}</label>
          <button type="button" onclick="aiFnLoadStages()"
                  class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold">
            ↻ {{ __('Cargar fases') }}
          </button>
        </div>
        <select id="aiFnTargetStage" class="w-full rounded-lg border-gray-200 text-sm py-2">
          <option value="">{{ __('Cargando fases…') }}</option>
        </select>
      </div>

      {{-- Response --}}
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Respuesta del bot al ejecutar (opcional)') }}</label>
        <textarea id="aiFnResponse" rows="2" maxlength="2000"
                  placeholder="{{ __('Ej: ¡Gracias! He registrado tus datos. Te contactaré pronto.') }}"
                  class="w-full rounded-lg border-gray-200 text-sm py-2"></textarea>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" id="aiFnActive" checked class="rounded border-gray-300 text-indigo-600">
        <label for="aiFnActive" class="text-sm text-gray-700">{{ __('Función activa') }}</label>
      </div>
    </div>

    <div class="flex gap-2 justify-end px-5 py-3 border-t border-gray-200 bg-gray-50">
      <button type="button" onclick="closeAiFunctionModal()"
              class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">{{ __('Cancelar') }}</button>
      <button type="button" id="aiFnSaveBtn" onclick="aiFnSave()"
              class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
        {{ __('Guardar') }}
      </button>
    </div>
  </div>
</div>

@verbatim
<script>
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const accountId = @endverbatim {{ $account->id }} @verbatim;
  const baseUrl = @endverbatim '{{ route("ai-functions.index", $account) }}' @verbatim;

  let availableFieldGroups = null;
  let availableStageGroups = null;

  /* ============ Lista de funciones ============ */
  function loadFunctions() {
    const box = document.getElementById('aiFunctionsListBox');
    if (!box) return;
    fetch(baseUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(r => r.json())
      .then(d => renderFunctions(d.functions || []));
  }

  const modeBadges = {
    update_crm:   { label: 'Actualizar CRM',   cls: 'bg-blue-100 text-blue-700' },
    change_stage: { label: 'Cambiar fase',     cls: 'bg-purple-100 text-purple-700' },
    info:         { label: 'Información',      cls: 'bg-gray-100 text-gray-600' },
  };

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function renderFunctions(fns) {
    const box = document.getElementById('aiFunctionsListBox');
    if (!fns.length) {
      box.innerHTML = '<p class="text-xs text-gray-400 italic">Sin funciones creadas. Haz clic en "Nueva función" para crear la primera.</p>';
      return;
    }

    box.innerHTML = fns.map(fn => {
      const badge = modeBadges[fn.mode] || modeBadges.info;
      const propsCount = Array.isArray(fn.properties) ? fn.properties.length : 0;
      const desc = fn.description.length > 130 ? fn.description.substring(0, 130) + '…' : fn.description;
      return `
        <div class="bg-white rounded-lg border border-gray-200 p-3 flex items-start gap-3 hover:border-indigo-300 transition">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-mono text-sm font-bold text-gray-900">${escapeHtml(fn.name)}</span>
              <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ${badge.cls}">${badge.label}</span>
              ${fn.is_active ? '' : '<span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-500">Inactiva</span>'}
              ${propsCount > 0 ? `<span class="text-[10px] text-gray-400">${propsCount} campo${propsCount > 1 ? 's' : ''}</span>` : ''}
            </div>
            <p class="text-xs text-gray-600 mt-1 line-clamp-2">${escapeHtml(desc)}</p>
          </div>
          <div class="flex gap-1 shrink-0">
            <button type="button" onclick='aiFnEdit(${JSON.stringify(fn).replace(/'/g, "&apos;")})'
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold px-2">Editar</button>
            <button type="button" onclick="aiFnDelete(${fn.id})"
                    class="text-xs text-red-500 hover:text-red-700 font-medium px-2">✕</button>
          </div>
        </div>`;
    }).join('');
  }

  /* ============ Cargar campos / fases para los selects ============ */
  window.aiFnLoadFields = function () {
    fetch('/ai-functions/available-fields', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(r => r.json())
      .then(d => {
        availableFieldGroups = d.groups || [];
        renderPropertiesSelect();
      });
  };

  window.aiFnLoadStages = function () {
    fetch('/ai-functions/available-stages', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(r => r.json())
      .then(d => {
        availableStageGroups = d.groups || [];
        renderStagesSelect();
      });
  };

  function renderPropertiesSelect(selected) {
    selected = selected || [];
    const panel = document.getElementById('aiFnProperties');
    if (!availableFieldGroups) {
      panel.innerHTML = '<span class="px-2 py-1 text-xs text-gray-400">Cargando…</span>';
      return;
    }
    let html = '';
    availableFieldGroups.forEach(g => {
      if (!g.options || !g.options.length) return;
      html += `<div class="px-2 pt-1 pb-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-400">${escapeHtml(g.label)}</div>`;
      g.options.forEach(o => {
        const ck = selected.includes(o.key) ? 'checked' : '';
        html += `<label class="ms-dd-opt"><input type="checkbox" value="${escapeHtml(o.key)}" ${ck} onchange="msChanged(this)"><span>${escapeHtml(o.label)}</span></label>`;
      });
    });
    panel.innerHTML = html || '<span class="px-2 py-1 text-xs text-gray-400">Sin campos disponibles</span>';
    if (window.msUpdateLabel) msUpdateLabel(document.getElementById('aiFnPropertiesDD'));
  }

  function renderStagesSelect(selectedId) {
    const sel = document.getElementById('aiFnTargetStage');
    if (!availableStageGroups) {
      sel.innerHTML = '<option value="">Cargando…</option>';
      return;
    }
    let html = '<option value="">— Selecciona fase —</option>';
    availableStageGroups.forEach(g => {
      html += `<optgroup label="${escapeHtml(g.label)}">`;
      g.options.forEach(o => {
        const s = String(selectedId) === String(o.id) ? 'selected' : '';
        html += `<option value="${o.id}" ${s}>${escapeHtml(o.label)}</option>`;
      });
      html += '</optgroup>';
    });
    sel.innerHTML = html;
  }

  /* ============ Modo: muestra/oculta secciones según mode ============ */
  window.aiFnUpdateModeUI = function () {
    const mode = document.getElementById('aiFnMode').value;
    document.getElementById('aiFnPropertiesBox').classList.toggle('hidden', mode !== 'update_crm');
    document.getElementById('aiFnStageBox').classList.toggle('hidden', mode !== 'change_stage');

    if (mode === 'update_crm' && availableFieldGroups === null) aiFnLoadFields();
    if (mode === 'change_stage' && availableStageGroups === null) aiFnLoadStages();
  };

  /* ============ Abrir/cerrar modal ============ */
  window.openAiFunctionModal = function () {
    document.getElementById('aiFnModalTitle').textContent = 'Crear función IA';
    document.getElementById('aiFnId').value = '';
    document.getElementById('aiFnMode').value = 'update_crm';
    document.getElementById('aiFnName').value = '';
    document.getElementById('aiFnDescription').value = '';
    document.getElementById('aiFnTargetStage').value = '';
    document.getElementById('aiFnResponse').value = '';
    document.getElementById('aiFnActive').checked = true;
    document.getElementById('aiFnError').classList.add('hidden');
    aiFnUpdateModeUI();
    renderPropertiesSelect([]);
    document.getElementById('aiFunctionModal').classList.remove('hidden');
  };

  window.closeAiFunctionModal = function () {
    document.getElementById('aiFunctionModal').classList.add('hidden');
  };

  document.getElementById('aiFunctionModal').addEventListener('click', e => {
    if (e.target.id === 'aiFunctionModal') closeAiFunctionModal();
  });

  /* ============ Editar ============ */
  window.aiFnEdit = function (fn) {
    document.getElementById('aiFnModalTitle').textContent = 'Editar función IA';
    document.getElementById('aiFnId').value = fn.id;
    document.getElementById('aiFnMode').value = fn.mode;
    document.getElementById('aiFnName').value = fn.name;
    document.getElementById('aiFnDescription').value = fn.description;
    document.getElementById('aiFnResponse').value = fn.response_template || '';
    document.getElementById('aiFnActive').checked = !!fn.is_active;
    document.getElementById('aiFnError').classList.add('hidden');

    if (fn.mode === 'change_stage') {
      if (availableStageGroups === null) {
        aiFnLoadStages();
        // Esperar a que cargue (poll simple)
        const wait = setInterval(() => {
          if (availableStageGroups !== null) {
            clearInterval(wait);
            renderStagesSelect(fn.target_stage_id);
          }
        }, 100);
      } else {
        renderStagesSelect(fn.target_stage_id);
      }
    } else if (fn.mode === 'update_crm') {
      const props = Array.isArray(fn.properties) ? fn.properties : [];
      if (availableFieldGroups === null) {
        aiFnLoadFields();
        const wait = setInterval(() => {
          if (availableFieldGroups !== null) {
            clearInterval(wait);
            renderPropertiesSelect(props);
          }
        }, 100);
      } else {
        renderPropertiesSelect(props);
      }
    }

    aiFnUpdateModeUI();
    document.getElementById('aiFunctionModal').classList.remove('hidden');
  };

  /* ============ Guardar ============ */
  window.aiFnSave = function () {
    const id          = document.getElementById('aiFnId').value;
    const mode        = document.getElementById('aiFnMode').value;
    const name        = document.getElementById('aiFnName').value.trim();
    const description = document.getElementById('aiFnDescription').value.trim();
    const response    = document.getElementById('aiFnResponse').value.trim();
    const active      = document.getElementById('aiFnActive').checked;

    const errBox = document.getElementById('aiFnError');
    errBox.classList.add('hidden');

    if (!name || !description) {
      errBox.textContent = 'Nombre y descripción son obligatorios.';
      errBox.classList.remove('hidden');
      return;
    }

    if (!/^[a-z][a-z0-9_]{1,58}[a-z0-9]$/i.test(name)) {
      errBox.textContent = 'El nombre debe ser snake_case (letras, números, _) sin espacios.';
      errBox.classList.remove('hidden');
      return;
    }

    let properties = [];
    let target_stage_id = null;

    if (mode === 'update_crm') {
      const panel = document.getElementById('aiFnProperties');
      properties = Array.from(panel.querySelectorAll('input[type=checkbox]:checked')).map(c => c.value);
    } else if (mode === 'change_stage') {
      target_stage_id = document.getElementById('aiFnTargetStage').value || null;
      if (!target_stage_id) {
        errBox.textContent = 'Selecciona una fase destino.';
        errBox.classList.remove('hidden');
        return;
      }
    }

    const url = id ? '/ai-functions/' + id : baseUrl;
    const method = id ? 'PUT' : 'POST';
    const btn = document.getElementById('aiFnSaveBtn');
    btn.disabled = true;
    btn.textContent = 'Guardando…';

    fetch(url, {
      method, credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({
        mode, name, description,
        properties, target_stage_id,
        response_template: response,
        is_active: active ? 1 : 0,
      }),
    })
    .then(r => r.json().then(d => ({ status: r.status, data: d })))
    .then(({ status, data }) => {
      btn.disabled = false; btn.textContent = 'Guardar';
      if (status === 200 && data.ok) {
        closeAiFunctionModal();
        loadFunctions();
      } else {
        const msgs = [];
        if (data.errors) Object.values(data.errors).forEach(arr => arr.forEach(m => msgs.push(m)));
        else if (data.message) msgs.push(data.message);
        errBox.innerHTML = msgs.length ? msgs.join('<br>') : 'Error al guardar';
        errBox.classList.remove('hidden');
      }
    })
    .catch(err => {
      btn.disabled = false; btn.textContent = 'Guardar';
      errBox.textContent = 'Error: ' + err.message;
      errBox.classList.remove('hidden');
    });
  };

  /* ============ Eliminar ============ */
  window.aiFnDelete = function (id) {
    if (!confirm('¿Eliminar esta función IA?')) return;
    fetch('/ai-functions/' + id, {
      method: 'DELETE', credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.ok) loadFunctions(); });
  };

  // Cargar lista al iniciar
  loadFunctions();
})();
</script>
@endverbatim

{{-- ════════════════════════════════════════
     MODAL SUBIR ARCHIVO A BASE DE CONOCIMIENTO
     ════════════════════════════════════════ --}}
<div id="kbFileModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base font-bold text-gray-900">📚 {{ __('Subir archivo') }}</h3>
      <button type="button" onclick="kbCloseFileModal()" class="text-gray-400 hover:text-red-500">✕</button>
    </div>
    <div id="kbFileError" class="hidden mb-3 px-3 py-2 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700"></div>

    <form id="kbFileForm" enctype="multipart/form-data" class="space-y-3">
      @csrf
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Título (opcional)') }}</label>
        <input type="text" name="title" maxlength="200" placeholder="{{ __('Ej: Catálogo de servicios') }}"
               class="w-full rounded-lg border-gray-200 text-sm py-2">
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Archivo') }} *</label>
        <input type="file" name="file" required accept=".txt,.md,.csv,.log,.pdf,.docx,text/plain,application/pdf"
               class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0
                      file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
        <p class="text-[10px] text-gray-400 mt-1">{{ __('Formatos: TXT, MD, CSV, PDF, DOCX. Máx 5 MB.') }}</p>
      </div>
      <div class="flex gap-2 justify-end pt-2">
        <button type="button" onclick="kbCloseFileModal()"
                class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">{{ __('Cancelar') }}</button>
        <button type="submit" id="kbFileSubmit"
                class="px-5 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
          {{ __('Subir') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ════════════════════════════════════════
     MODAL PEGAR TEXTO
     ════════════════════════════════════════ --}}
<div id="kbTextModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
      <h3 class="text-base font-bold text-gray-900" id="kbTextTitle">📝 {{ __('Pegar texto') }}</h3>
      <button type="button" onclick="kbCloseTextModal()" class="text-gray-400 hover:text-red-500">✕</button>
    </div>
    <div id="kbTextError" class="hidden mx-5 mt-3 px-3 py-2 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700"></div>

    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
      <input type="hidden" id="kbTextId" value="">
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Título') }} *</label>
        <input type="text" id="kbTextTitleInput" maxlength="200"
               placeholder="{{ __('Ej: Política de devoluciones') }}"
               class="w-full rounded-lg border-gray-200 text-sm py-2">
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Contenido') }} *</label>
        <textarea id="kbTextContent" rows="14" maxlength="200000"
                  placeholder="{{ __('Pega aquí información de tu empresa, productos, políticas, FAQs…') }}"
                  class="w-full rounded-lg border-gray-200 text-sm py-2 font-mono"></textarea>
        <p class="text-[10px] text-gray-400 mt-1">{{ __('Caracteres:') }} <span id="kbTextCharCount">0</span></p>
      </div>
      <div class="flex items-center gap-2">
        <input type="checkbox" id="kbTextActive" checked class="rounded border-gray-300 text-emerald-600">
        <label for="kbTextActive" class="text-sm text-gray-700">{{ __('Activo (la IA lo usará)') }}</label>
      </div>
    </div>

    <div class="flex gap-2 justify-end px-5 py-3 border-t border-gray-200 bg-gray-50">
      <button type="button" onclick="kbCloseTextModal()"
              class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">{{ __('Cancelar') }}</button>
      <button type="button" id="kbTextSaveBtn" onclick="kbTextSave()"
              class="px-5 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
        {{ __('Guardar') }}
      </button>
    </div>
  </div>
</div>

@verbatim
<script>
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
@endverbatim
  const kbBaseUrl     = '{{ route("ai-knowledge.index", $account) }}';
  const kbStoreFile   = '{{ route("ai-knowledge.store-file", $account) }}';
  const kbStoreText   = '{{ route("ai-knowledge.store-text", $account) }}';
@verbatim

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function loadKb() {
    fetch(kbBaseUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return;
        renderKbList(d.entries || []);
        updateUsage(d.total_chars || 0, d.max_chars || 16000);
      });
  }

  function updateUsage(used, max) {
    const badge = document.getElementById('kbUsageBadge');
    if (!badge) return;
    const pct = Math.min(100, Math.round(used / max * 100));
    const color = pct > 90 ? 'text-red-600' : pct > 70 ? 'text-amber-600' : 'text-gray-500';
    badge.className = 'text-[10px] shrink-0 ' + color;
    badge.textContent = used.toLocaleString() + ' / ' + max.toLocaleString() + ' chars';
  }

  function renderKbList(entries) {
    const box = document.getElementById('kbList');
    if (!entries.length) {
      box.innerHTML = '<p class="text-xs text-gray-500 italic">Sin entradas todavía. Sube un archivo o pega texto.</p>';
      return;
    }
    box.innerHTML = entries.map(e => {
      const icon = e.source === 'file' ? '📎' : '📝';
      const sub = e.source === 'file'
        ? `${escapeHtml(e.original_filename || '')} · ${e.size_kb} KB`
        : 'Texto pegado';
      return `
        <div class="bg-white rounded-lg border border-gray-200 p-3 flex items-start gap-3 ${e.is_active ? '' : 'opacity-60'}">
          <span class="text-xl shrink-0">${icon}</span>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <p class="font-semibold text-sm text-gray-900 truncate">${escapeHtml(e.title)}</p>
              ${e.is_active ? '' : '<span class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-500">Inactivo</span>'}
            </div>
            <p class="text-[11px] text-gray-500 truncate">${escapeHtml(sub)}</p>
            <p class="text-[10px] text-gray-400">${escapeHtml(e.created_at || '')}</p>
          </div>
          <div class="flex gap-1 shrink-0">
            ${e.source === 'text' ? `<button type="button" onclick="kbEditText(${e.id})" class="text-xs text-indigo-600 hover:text-indigo-800 px-2">Editar</button>` : ''}
            <button type="button" onclick="kbToggleActive(${e.id}, ${!e.is_active})"
                    class="text-xs ${e.is_active ? 'text-amber-600' : 'text-green-600'} hover:opacity-75 px-2">
              ${e.is_active ? 'Pausar' : 'Activar'}
            </button>
            <button type="button" onclick="kbDelete(${e.id})" class="text-xs text-red-500 hover:text-red-700 px-2">✕</button>
          </div>
        </div>`;
    }).join('');
  }

  /* ============ Modal subir archivo ============ */
  window.kbOpenFileModal = function () {
    document.getElementById('kbFileForm').reset();
    document.getElementById('kbFileError').classList.add('hidden');
    document.getElementById('kbFileModal').classList.remove('hidden');
  };
  window.kbCloseFileModal = function () { document.getElementById('kbFileModal').classList.add('hidden'); };

  document.getElementById('kbFileForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const errBox = document.getElementById('kbFileError');
    const btn    = document.getElementById('kbFileSubmit');
    errBox.classList.add('hidden');
    btn.disabled = true; btn.textContent = 'Subiendo…';

    const fd = new FormData(this);
    try {
      const res = await fetch(kbStoreFile, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: fd,
      });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.ok) {
        kbCloseFileModal();
        loadKb();
      } else {
        const msgs = [];
        if (data.errors) Object.values(data.errors).forEach(arr => arr.forEach(m => msgs.push(m)));
        else if (data.message) msgs.push(data.message);
        errBox.innerHTML = msgs.length ? msgs.join('<br>') : 'Error al subir';
        errBox.classList.remove('hidden');
      }
    } catch (err) {
      errBox.textContent = 'Error: ' + err.message;
      errBox.classList.remove('hidden');
    } finally {
      btn.disabled = false; btn.textContent = 'Subir';
    }
  });

  /* ============ Modal pegar texto ============ */
  window.kbOpenTextModal = function () {
    document.getElementById('kbTextTitle').textContent = '📝 Pegar texto';
    document.getElementById('kbTextId').value = '';
    document.getElementById('kbTextTitleInput').value = '';
    document.getElementById('kbTextContent').value = '';
    document.getElementById('kbTextActive').checked = true;
    document.getElementById('kbTextCharCount').textContent = '0';
    document.getElementById('kbTextError').classList.add('hidden');
    document.getElementById('kbTextModal').classList.remove('hidden');
  };
  window.kbCloseTextModal = function () { document.getElementById('kbTextModal').classList.add('hidden'); };

  document.getElementById('kbTextContent').addEventListener('input', function () {
    document.getElementById('kbTextCharCount').textContent = this.value.length.toLocaleString();
  });

  window.kbEditText = function (id) {
    fetch('/ai-knowledge/' + id, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return;
        const e = d.entry;
        document.getElementById('kbTextTitle').textContent = '📝 Editar texto';
        document.getElementById('kbTextId').value = e.id;
        document.getElementById('kbTextTitleInput').value = e.title || '';
        document.getElementById('kbTextContent').value = e.content || '';
        document.getElementById('kbTextActive').checked = !!e.is_active;
        document.getElementById('kbTextCharCount').textContent = (e.content || '').length.toLocaleString();
        document.getElementById('kbTextError').classList.add('hidden');
        document.getElementById('kbTextModal').classList.remove('hidden');
      });
  };

  window.kbTextSave = function () {
    const id      = document.getElementById('kbTextId').value;
    const title   = document.getElementById('kbTextTitleInput').value.trim();
    const content = document.getElementById('kbTextContent').value;
    const active  = document.getElementById('kbTextActive').checked;
    const errBox  = document.getElementById('kbTextError');
    const btn     = document.getElementById('kbTextSaveBtn');

    if (!title || !content.trim()) {
      errBox.textContent = 'Título y contenido son obligatorios.';
      errBox.classList.remove('hidden');
      return;
    }

    errBox.classList.add('hidden');
    btn.disabled = true; btn.textContent = 'Guardando…';

    const url = id ? '/ai-knowledge/' + id : kbStoreText;
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
      method, credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ title, content, is_active: active ? 1 : 0 }),
    })
    .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
    .then(({ ok, data }) => {
      btn.disabled = false; btn.textContent = 'Guardar';
      if (ok && data.ok) {
        kbCloseTextModal();
        loadKb();
      } else {
        errBox.textContent = data.message || 'Error al guardar';
        errBox.classList.remove('hidden');
      }
    });
  };

  window.kbToggleActive = function (id, makeActive) {
    fetch('/ai-knowledge/' + id, {
      method: 'PUT', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ is_active: makeActive ? 1 : 0 }),
    }).then(r => r.json()).then(d => { if (d.ok) loadKb(); });
  };

  window.kbDelete = function (id) {
    if (!confirm('¿Eliminar esta entrada de la base de conocimiento?')) return;
    fetch('/ai-knowledge/' + id, {
      method: 'DELETE', credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    }).then(r => r.json()).then(d => { if (d.ok) loadKb(); });
  };

  loadKb();
})();
</script>
@endverbatim
@endif
</x-app-layout>
