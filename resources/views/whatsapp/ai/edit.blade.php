<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center gap-3">
      <a href="{{ route('whatsapp.accounts.index') }}" class="text-gray-400 hover:text-gray-600">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Asistente IA — {{ $account->name }}
      </h2>
      @if($assistant?->is_active)
        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700">
          <span class="size-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>
          Activo
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

      {{-- Card info --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-start gap-4">
        <div class="size-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
          <svg class="size-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1 1 .03 2.798-1.304 2.628l-1.44-.21m-10.856.21l-1.44.21c-1.332.169-2.304-1.628-1.304-2.628L5 14.5"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-semibold text-gray-900">Asistente IA con ChatGPT</p>
          <p class="text-xs text-gray-500 mt-1 leading-relaxed">
            Cuando esté activo, el asistente responderá automáticamente los mensajes de texto entrantes en
            <strong>{{ $account->name }}</strong> usando tu API Key de OpenAI. Cada cliente usa su propia clave.
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
            <p class="text-sm font-semibold text-gray-900">Activar asistente</p>
            <p class="text-xs text-gray-500 mt-0.5">Al activar, el bot responderá mensajes automáticamente.</p>
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
            API Key de OpenAI
            <span class="text-gray-400 font-normal">(requerida)</span>
          </label>
          <input type="password" name="api_key"
                 placeholder="{{ $assistant?->api_key ? '••••••••••••••••••••••••••• (ya guardada)' : 'sk-...' }}"
                 class="w-full border-gray-300 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                 autocomplete="off">
          @error('api_key')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
          <p class="text-xs text-gray-400 mt-1.5">
            Obtén tu clave en
            <a href="https://platform.openai.com/api-keys" target="_blank" class="text-indigo-600 underline">platform.openai.com/api-keys</a>.
            Se almacena cifrada. Déjala vacía para no cambiarla.
          </p>
        </div>

        {{-- Modelo --}}
        <div class="px-6 py-5">
          <label class="block text-sm font-medium text-gray-700 mb-1">Modelo de ChatGPT</label>
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
            Instrucciones del asistente
            <span class="text-gray-400 font-normal">(system prompt)</span>
          </label>
          <textarea name="system_prompt" rows="5"
                    class="w-full border-gray-300 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Eres un asistente de ventas amable y profesional. Responde siempre en español. Cuando el cliente quiera hablar con un humano, dile que un agente lo atenderá pronto.">{{ old('system_prompt', $assistant?->system_prompt) }}</textarea>
          @error('system_prompt')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
          <p class="text-xs text-gray-400 mt-1">Define el comportamiento, tono y límites del asistente. Máx. 4000 caracteres.</p>
        </div>

        {{-- Parámetros avanzados --}}
        <div class="px-6 py-5" x-data="{ open: false }">
          <button type="button" @click="open = !open"
                  class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900">
            <svg class="size-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            Parámetros avanzados
          </button>

          <div x-show="open" x-transition class="mt-4 space-y-5">

            {{-- Temperatura --}}
            <div x-data="{ val: {{ old('temperature', $assistant?->temperature ?? 0.7) }} }">
              <div class="flex items-center justify-between mb-1">
                <label class="text-sm font-medium text-gray-700">Temperatura (creatividad)</label>
                <span class="text-sm font-semibold text-indigo-600" x-text="val"></span>
              </div>
              <input type="range" name="temperature" min="0" max="2" step="0.1"
                     x-model="val"
                     class="w-full accent-indigo-600">
              <div class="flex justify-between text-[10px] text-gray-400 mt-0.5">
                <span>Preciso (0)</span>
                <span>Balanceado (0.7)</span>
                <span>Creativo (2)</span>
              </div>
              @error('temperature')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Max Tokens --}}
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Longitud máxima de respuesta
                <span class="text-gray-400 font-normal">(tokens)</span>
              </label>
              <input type="number" name="max_tokens" min="50" max="4000" step="50"
                     value="{{ old('max_tokens', $assistant?->max_tokens ?? 500) }}"
                     class="w-full border-gray-300 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
              <p class="text-xs text-gray-400 mt-1">~75 palabras por cada 100 tokens. Recomendado: 300-800.</p>
              @error('max_tokens')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Contexto --}}
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mensajes de contexto</label>
              <input type="number" name="context_messages" min="1" max="50"
                     value="{{ old('context_messages', $assistant?->context_messages ?? 20) }}"
                     class="w-full border-gray-300 rounded-xl shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
              <p class="text-xs text-gray-400 mt-1">Cuántos mensajes previos de la conversación se envían como contexto al modelo.</p>
              @error('context_messages')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

          </div>
        </div>

        {{-- Botones --}}
        <div class="px-6 py-4 flex items-center justify-between gap-3 bg-gray-50 rounded-b-2xl">
          <div>
            @if($assistant)
              <form method="POST" action="{{ route('whatsapp.ai.destroy', $account) }}"
                    onsubmit="return confirm('¿Eliminar la configuración del asistente IA?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 underline">
                  Eliminar configuración
                </button>
              </form>
            @endif
          </div>
          <div class="flex gap-2">
            <a href="{{ route('whatsapp.accounts.index') }}"
               class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-100 transition">
              Cancelar
            </a>
            <button type="submit"
                    class="px-5 py-2 text-sm bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium">
              Guardar cambios
            </button>
          </div>
        </div>

      </form>

      {{-- Info adicional --}}
      <div class="rounded-xl bg-amber-50 border border-amber-200 px-5 py-4 text-xs text-amber-800 space-y-1.5">
        <p class="font-semibold text-sm text-amber-900">¿Cómo funciona?</p>
        <ul class="space-y-1 list-disc list-inside">
          <li>El asistente sólo responde mensajes de <strong>texto</strong> entrantes.</li>
          <li>Si el mensaje es una imagen, audio o video, <strong>no responde</strong> automáticamente.</li>
          <li>Cada respuesta usa tus créditos de OpenAI — revisa tu uso en platform.openai.com.</li>
          <li>El asistente ve los últimos <strong>{{ $assistant?->context_messages ?? 20 }} mensajes</strong> de la conversación como contexto.</li>
          <li>Los mensajes enviados por el asistente aparecen en el inbox sin nombre de agente.</li>
        </ul>
      </div>

    </div>
  </div>
</x-app-layout>
