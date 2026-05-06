<x-app-layout>

@php
  $avatarColors = ['bg-green-100 text-green-700','bg-blue-100 text-blue-700','bg-purple-100 text-purple-700',
                   'bg-amber-100 text-amber-700','bg-rose-100 text-rose-700','bg-teal-100 text-teal-700'];
  function waAvatar(string $name, array $colors): string {
      return $colors[abs(crc32($name)) % count($colors)];
  }
  $convName  = $conversation->contact_name ?? $conversation->contact_phone ?? '?';
  $initials  = strtoupper(mb_substr($convName, 0, 1) . (mb_substr($convName, 1, 1) ?: ''));
  $avatarCls = waAvatar($convName, $avatarColors);

  // Calcular si la ventana de 24h ya pasó (basado en último mensaje INBOUND)
  $lastInbound = $conversation->messages()
      ->where('direction', 'inbound')
      ->orderByDesc('created_at')
      ->first();
  $waWindowExpired = !$lastInbound || $lastInbound->created_at->copy()->addHours(24)->isPast();
@endphp

<div class="flex flex-col bg-white" style="height:100vh;height:100dvh;">

  {{-- ════ BARRA SUPERIOR ════ --}}
  <div class="h-14 shrink-0 flex items-center gap-3 border-b border-gray-200 bg-white px-4">
    {{-- Botón hamburger – solo visible en mobile cuando el panel container ya está montado --}}
    <button onclick="setMobilePanel('sidebar')"
            class="wa-mobile-btn shrink-0 p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition" title="Ver conversaciones">
      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
    <svg class="size-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
      <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.126 1.532 5.855L.057 23.882a.5.5 0 00.611.61l6.102-1.6A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22a9.944 9.944 0 01-5.073-1.386l-.363-.215-3.764.987.999-3.671-.236-.375A9.955 9.955 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
    </svg>
    <span class="text-sm font-semibold text-gray-900">WhatsApp</span>
    <a href="{{ route('whatsapp.accounts.index') }}"
       class="ml-auto text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1">
      <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      Cuentas
    </a>
  </div>

  {{-- ════ TRES PANELES ════ --}}
  <div class="flex flex-1 overflow-hidden" id="panelContainer" data-mp="chat">

  {{-- ════════════════════════════════════════
       PANEL IZQUIERDO – Lista de conversaciones
       ════════════════════════════════════════ --}}
  <div id="leftPanel" class="shrink-0 border-r border-gray-200">

    {{-- Cabecera --}}
    <div class="h-12 px-3 flex items-center border-b border-gray-100 bg-gray-50">
      <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Conversaciones</span>
    </div>

    {{-- Filtro de cuenta --}}
    <div class="px-3 py-2 border-b border-gray-100 bg-white">
      <form method="GET" action="{{ route('whatsapp.inbox.show', $conversation) }}" class="flex gap-1.5">
        <select name="account_id" onchange="this.form.submit()"
                class="flex-1 text-xs rounded-lg border-gray-200 bg-gray-50 py-1.5 pr-6 text-gray-700">
          <option value="">Todas las cuentas</option>
          @foreach($accounts as $a)
            <option value="{{ $a->id }}" {{ (string)$accountId === (string)$a->id ? 'selected' : '' }}>
              {{ $a->name }}
            </option>
          @endforeach
        </select>
      </form>
    </div>

    {{-- Tabs: Todo / Abierto / Cerrado --}}
    <div class="flex border-b border-gray-100 bg-white shrink-0">
      @foreach(['all' => 'Todo', 'open' => 'Abierto', 'closed' => 'Cerrado'] as $val => $label)
        <a href="{{ route('whatsapp.inbox.show', $conversation) }}?status={{ $val }}{{ $accountId ? '&account_id='.$accountId : '' }}"
           class="flex-1 py-2 text-center text-xs font-medium transition
                  {{ $status === $val ? 'text-indigo-600 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>

    {{-- Lista scrollable --}}
    <div id="sidebarList" class="overflow-y-auto flex-1 bg-white">
      @forelse($conversations as $c)
        @php
          $cName = $c->contact_name ?? $c->contact_phone ?? '?';
          $cInit = strtoupper(mb_substr($cName, 0, 1) . (mb_substr($cName, 1, 1) ?: ''));
          $cAva  = waAvatar($cName, $avatarColors);
          $isActive = $c->id === $conversation->id;
          $time  = $c->last_message_at
            ? ($c->last_message_at->isToday() ? $c->last_message_at->format('H:i') : $c->last_message_at->format('d/m'))
            : '';
        @endphp
        <a id="sidebar-conv-{{ $c->id }}"
           href="{{ route('whatsapp.inbox.show', $c) }}{{ $accountId ? '?account_id='.$accountId : '' }}"
           data-conv-id="{{ $c->id }}"
           data-last-ts="{{ $c->last_message_at?->timestamp ?? 0 }}"
           class="flex items-center gap-3 px-3 py-3 border-b border-gray-50 hover:bg-gray-50 transition
                  {{ $isActive ? 'bg-indigo-50 border-l-[3px] border-l-indigo-500' : 'border-l-[3px] border-l-transparent' }}">
          {{-- Avatar --}}
          <div class="size-10 rounded-full flex items-center justify-center shrink-0 text-sm font-semibold {{ $cAva }}">
            {{ $cInit }}
          </div>
          {{-- Texto --}}
          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-1">
              <span class="sidebar-name text-sm font-semibold text-gray-900 truncate {{ $isActive ? 'text-indigo-700' : '' }}">
                {{ $cName }}
              </span>
              <span class="sidebar-time text-[10px] text-gray-400 shrink-0">{{ $time }}</span>
            </div>
            <p class="sidebar-preview text-xs text-gray-500 truncate mt-0.5">{{ $c->last_message_preview ?? '—' }}</p>
          </div>
          {{-- Puntos: notificación (azul) + estado open (verde) --}}
          <div class="flex flex-col items-center gap-1 shrink-0">
            <span class="unread-dot hidden size-2.5 rounded-full bg-blue-500"></span>
            @if($c->status === 'open')
              <span class="size-2 rounded-full bg-green-400"></span>
            @endif
          </div>
        </a>
      @empty
        <div class="p-6 text-center text-sm text-gray-400">No hay conversaciones.</div>
      @endforelse
    </div>

  </div>

  {{-- ════════════════════════════════════════
       PANEL CENTRAL – Chat
       ════════════════════════════════════════ --}}
  <div id="centerPanel" class="min-w-0 bg-gray-50">

    {{-- Cabecera del chat --}}
    <div id="chatHeader" class="h-14 px-3 md:px-4 flex items-center gap-2 md:gap-3 border-b border-gray-200 bg-white shrink-0">
      {{-- Botón volver al sidebar (solo mobile) --}}
      <button onclick="setMobilePanel('sidebar')" class="wa-mobile-btn shrink-0 p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition" title="Conversaciones">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <div id="chatHeaderAvatar" class="size-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0 {{ $avatarCls }}">
        {{ $initials }}
      </div>
      <div class="min-w-0">
        <p id="chatHeaderName" class="text-sm font-semibold text-gray-900 truncate">{{ $convName }}</p>
        <p id="chatHeaderPhone" class="text-xs text-gray-500">{{ $conversation->contact_phone }}</p>
      </div>
      <span id="chatHeaderStatus" class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold shrink-0
          {{ $conversation->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
        {{ $conversation->status === 'open' ? 'Abierta' : 'Cerrada' }}
      </span>
      @if(isset($aiAssistant) && $aiAssistant?->is_active)
        <span id="aiBadge" title="Asistente IA ({{ $aiAssistant->model }})"
              class="ml-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold shrink-0
                     {{ $conversation->ai_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-500' }}">
          <span class="size-1.5 rounded-full inline-block {{ $conversation->ai_active ? 'bg-green-500 animate-pulse' : 'bg-red-400' }}"></span>
          {{ $conversation->ai_active ? 'IA' : 'IA pausada' }}
        </span>
      @else
        <span id="aiBadge" class="hidden"></span>
      @endif
      <span id="chatHeaderAccount" class="hidden lg:block text-xs text-gray-400">{{ $conversation->account?->name ?? '' }}</span>
      {{-- Botón detalles contacto (solo mobile) --}}
      <button onclick="setMobilePanel('info')" class="wa-mobile-btn ml-auto shrink-0 p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition" title="Detalles del contacto">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
      </button>
    </div>

    {{-- Mensajes --}}
    <div id="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3">

      @foreach($conversation->messages as $m)
        @php $isOut = $m->direction === 'outbound'; $type = $m->type ?? 'text'; @endphp
        <div class="flex {{ $isOut ? 'justify-end' : 'justify-start' }}"
             data-message-id="{{ $m->message_id ?? '' }}"
             data-db-id="{{ $m->id ?? '' }}">
          <div class="max-w-[85%] md:max-w-[70%] rounded-2xl px-3 py-2 text-sm shadow-sm
                      {{ $isOut ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-white text-gray-900 rounded-bl-sm' }}">

            @if($type === 'image' && !empty($m->public_url))
              @if(!empty($m->caption))<div class="whitespace-pre-line mb-1.5 text-xs">{{ $m->caption }}</div>@endif
              <a href="{{ $m->public_url }}" target="_blank" rel="noopener">
                <img src="{{ $m->public_url }}" alt="imagen"
                     class="rounded-lg object-cover cursor-zoom-in"
                     style="max-width:240px;max-height:200px;display:block;"/>
              </a>
            @elseif($type === 'video' && !empty($m->public_url))
              @if(!empty($m->caption))<div class="whitespace-pre-line mb-1.5 text-xs">{{ $m->caption }}</div>@endif
              <video controls class="rounded-lg" style="max-width:240px;max-height:200px;display:block;">
                <source src="{{ $m->public_url }}" type="{{ $m->mime_type ?? 'video/mp4' }}">
              </video>
            @elseif($type === 'audio' && !empty($m->public_url))
              <audio controls class="w-full"><source src="{{ $m->public_url }}" type="{{ $m->mime_type ?? 'audio/ogg' }}"></audio>
            @elseif($type === 'document' && !empty($m->public_url))
              @if(!empty($m->caption))<div class="whitespace-pre-line mb-1.5 text-xs">{{ $m->caption }}</div>@endif
              <a href="{{ $m->public_url }}" target="_blank" rel="noopener"
                 class="{{ $isOut ? 'text-white underline' : 'text-indigo-700 underline' }}">
                📎 {{ $m->filename ?? 'Abrir archivo' }}
              </a>
            @else
              <div class="whitespace-pre-line">{{ $m->body }}</div>
            @endif

            <div class="text-[10px] opacity-60 mt-1 flex items-center gap-1 {{ $isOut ? 'justify-end' : '' }}">
              {{ optional($m->created_at)->format('d/m H:i') }}
              @if($isOut && !empty($m->sentBy))
                <span>· {{ $m->sentBy->name }}</span>
              @endif
            </div>
          </div>
        </div>
      @endforeach

    </div>

    {{-- Formulario de envío --}}
    <div class="border-t border-gray-200 bg-white px-4 py-3 shrink-0">
      {{-- Preview archivo seleccionado --}}
      <div id="filePreview" class="hidden mb-2 flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-50 border border-indigo-100 text-xs text-indigo-700">
        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
        </svg>
        <span id="filePreviewName" class="truncate flex-1"></span>
        <button type="button" id="fileClear" class="shrink-0 text-indigo-400 hover:text-red-500">✕</button>
      </div>

      {{-- Banner: ventana de 24h vencida (solo se muestra cuando aplica) --}}
      <div id="windowExpiredBanner" class="{{ $waWindowExpired ? '' : 'hidden' }} mb-2 rounded-lg bg-amber-50 border border-amber-300 px-3 py-2.5">
        <div class="flex items-start gap-2">
          <svg class="size-4 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <div class="flex-1 text-xs text-amber-800">
            <p class="font-semibold">Ventana de 24h vencida</p>
            <p class="mt-0.5">El cliente no ha escrito en más de 24 horas. WhatsApp solo permite enviar
              <strong>plantillas aprobadas</strong> en este momento.</p>
            <button type="button" onclick="openTemplatesModal()"
                    class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 transition">
              <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              Enviar plantilla
            </button>
          </div>
        </div>
      </div>

      <form id="sendForm" method="POST" action="{{ route('whatsapp.inbox.send', $conversation) }}"
            class="flex items-end gap-2" enctype="multipart/form-data">
        @csrf
        <input type="file" id="mediaInput" name="media" class="hidden"
               accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/3gpp">

        {{-- Botón adjuntar --}}
        <button type="button" id="attachBtn"
                class="shrink-0 p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition"
                title="Adjuntar imagen o video">
          <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
          </svg>
        </button>

        {{-- Botón plantillas --}}
        <button type="button" onclick="openTemplatesModal()"
                class="shrink-0 p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition"
                title="Enviar plantilla aprobada">
          <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
        </button>

        <textarea id="msgInput" name="message" rows="1"
                  class="flex-1 resize-none rounded-xl border-gray-200 bg-gray-50 text-sm px-3 py-2 focus:ring-indigo-400 focus:border-indigo-400"
                  placeholder="Escribe un mensaje..."
                  style="min-height:40px;max-height:120px;"
                  autocomplete="off"></textarea>

        <button type="submit" id="sendBtn"
                class="shrink-0 p-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition disabled:opacity-50">
          <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
          </svg>
        </button>
      </form>
      <p class="mt-1 text-[10px] text-gray-400">Imágenes: JPG, PNG, GIF, WEBP · Videos: MP4 (máx. 16 MB)</p>
    </div>

  </div>

  {{-- ════════════════════════════════════════
       PANEL DERECHO – Detalles del contacto
       ════════════════════════════════════════ --}}
  <div id="rightPanel" class="shrink-0 border-l border-gray-200 bg-white overflow-y-auto">

    {{-- Header --}}
    <div class="h-14 px-4 flex items-center gap-2 border-b border-gray-100 shrink-0">
      <button onclick="setMobilePanel('chat')" class="wa-mobile-btn shrink-0 p-1.5 -ml-1 rounded-lg text-gray-500 hover:bg-gray-100 transition" title="Volver al chat">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <span class="text-sm font-semibold text-gray-900">Detalles del contacto</span>
    </div>

    {{-- Avatar grande --}}
    <div class="flex flex-col items-center py-5 border-b border-gray-100">
      <div class="size-14 rounded-full flex items-center justify-center text-xl font-bold {{ $avatarCls }}">
        {{ $initials }}
      </div>
      <p class="mt-2 text-sm font-semibold text-gray-900">{{ $convName }}</p>
      <span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold
          {{ $conversation->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
        {{ $conversation->status === 'open' ? 'Abierta' : 'Cerrada' }}
      </span>
    </div>

    {{-- Campos --}}
    <div class="px-4 py-4 space-y-3 border-b border-gray-100 text-sm">
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-0.5">Teléfono</p>
        <p class="text-gray-800 font-medium">{{ $conversation->contact_phone }}</p>
      </div>
      @if($conversation->account)
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-0.5">Cuenta WhatsApp</p>
          <p class="text-gray-800">{{ $conversation->account->name }}</p>
        </div>
      @endif
      @if($conversation->last_message_at)
        <div>
          <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-0.5">Último mensaje</p>
          <p class="text-gray-500 text-xs">{{ $conversation->last_message_at->diffForHumans() }}</p>
        </div>
      @endif
    </div>

    {{-- Asistente IA toggle --}}
    @if($hasAi)
    <div id="aiToggleSection"
         data-toggle-url="{{ route('whatsapp.inbox.ai.toggle', $conversation) }}"
         data-active="{{ $conversation->ai_active ? '1' : '0' }}"
         onclick="toggleAiBot(this)"
         class="px-4 py-3 border-b cursor-pointer select-none transition-colors
                {{ $conversation->ai_active
                    ? 'bg-green-50 border-green-100 hover:bg-green-100'
                    : 'bg-red-50 border-red-100 hover:bg-red-100' }}">
      <div class="flex items-center gap-2">
        <svg class="size-4 shrink-0 {{ $conversation->ai_active ? 'text-green-500' : 'text-red-400' }}"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082"/>
        </svg>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-semibold {{ $conversation->ai_active ? 'text-green-800' : 'text-red-700' }}">Asistente IA</p>
          <p id="aiToggleLabel" class="flex items-center gap-1 text-[10px] font-semibold mt-0.5
                                       {{ $conversation->ai_active ? 'text-green-600' : 'text-red-500' }}">
            <span id="aiToggleDot" class="size-2 rounded-full inline-block
                                          {{ $conversation->ai_active ? 'bg-green-500 animate-pulse' : 'bg-red-400' }}"></span>
            {{ $conversation->ai_active ? 'Activo — toca para pausar' : 'Pausado — toca para activar' }}
          </p>
        </div>
        <svg class="size-4 shrink-0 {{ $conversation->ai_active ? 'text-green-400' : 'text-red-300' }}"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </div>
    </div>
    @endif

    {{-- Negociación vinculada --}}
    <div class="px-4 py-4">
      <div class="flex items-center justify-between mb-2">
        <p class="text-xs font-semibold text-gray-700">Negociación</p>
      </div>

      @if($currentDeal)
        @php
          $dealPipeline = optional($currentDeal->pipeline);
          $dealStage    = is_object($currentDeal->stage) ? $currentDeal->stage : null;
        @endphp
        <a href="{{ route('deals.edit', [$currentDeal->pipeline_id, $currentDeal->id]) }}"
           class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 hover:bg-indigo-50 hover:border-indigo-200 transition group">
          <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-gray-900 truncate group-hover:text-indigo-700">{{ $currentDeal->title }}</p>
            <p class="text-[10px] text-gray-400 truncate mt-0.5">
              {{ $dealPipeline->name }}@if($dealStage) › {{ $dealStage->name }}@endif
            </p>
          </div>
          <div class="flex items-center gap-1.5 shrink-0">
            <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[9px] font-semibold
                {{ $currentDeal->status === 'open' ? 'bg-green-100 text-green-700' : ($currentDeal->status === 'won' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-600') }}">
              {{ match($currentDeal->status) { 'open' => 'Abierto', 'won' => 'Ganado', 'lost' => 'Perdido', default => $currentDeal->status } }}
            </span>
            <svg class="size-3 text-gray-300 group-hover:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>
      @else
        <div x-data="{ open: false }" class="space-y-2">
          {{-- Estado vacío + botón --}}
          <div class="rounded-xl border border-dashed border-gray-200 p-3 text-center">
            <p class="text-xs text-gray-400 mb-2">Sin negociación vinculada.</p>
            <button @click="open = !open"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
              <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Crear negociación
            </button>
          </div>

          {{-- Formulario desplegable --}}
          <div x-show="open" x-transition class="rounded-xl border border-indigo-100 bg-indigo-50 p-3 space-y-2">
            <form method="POST" action="{{ route('whatsapp.inbox.deal.create', $conversation) }}">
              @csrf

              <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wide text-gray-500 mb-1">
                  Pipeline
                </label>
                <select name="pipeline_id" required
                        class="w-full text-xs rounded-lg border-gray-200 bg-white py-1.5 text-gray-800">
                  <option value="">Selecciona un pipeline…</option>
                  @foreach($pipelines as $pl)
                    <option value="{{ $pl->id }}"
                      {{ $conversation->account?->pipeline_id == $pl->id ? 'selected' : '' }}>
                      {{ $pl->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wide text-gray-500 mb-1">
                  Título (opcional)
                </label>
                <input type="text" name="title"
                       placeholder="{{ ($conversation->contact_name ?? $conversation->contact_phone ?? 'WhatsApp') . ' - WhatsApp' }}"
                       class="w-full text-xs rounded-lg border-gray-200 bg-white py-1.5 text-gray-800 placeholder-gray-400">
              </div>

              <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
                  Crear
                </button>
                <button type="button" @click="open = false"
                        class="flex-1 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition">
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      @endif
    </div>

  </div>{{-- fin panel derecho --}}

  </div>{{-- fin tres paneles --}}

</div>{{-- fin layout principal --}}

<style>
/* ── Responsive WhatsApp inbox ───────────────────────────────────── */
@media (min-width: 768px) {
  #leftPanel   { display: flex; flex-direction: column; width: 18rem; flex-shrink: 0; }
  #centerPanel { display: flex; flex-direction: column; flex: 1 1 0%; }
  #rightPanel  { display: flex; flex-direction: column; width: 280px; flex-shrink: 0; }
  .wa-mobile-btn { display: none !important; }
}
@media (max-width: 767px) {
  #leftPanel, #centerPanel, #rightPanel { display: none; }
  #panelContainer[data-mp="sidebar"] #leftPanel   { display: flex; flex-direction: column; width: 100%; }
  #panelContainer[data-mp="chat"]    #centerPanel { display: flex; flex-direction: column; flex: 1 1 0%; }
  #panelContainer[data-mp="info"]    #rightPanel  { display: flex; flex-direction: column; width: 100%; }
}
</style>

<script>
function setMobilePanel(panel) {
  const c = document.getElementById('panelContainer');
  if (c) c.dataset.mp = panel;
}

(function () {
  // ── Estado mutable (cambia al switchear conversación) ──────────────────────
  let conversationId = @json($conversation->id);
  let pollUrl        = @json(route('whatsapp.inbox.messages', $conversation));
  let echoChannel    = null;
  let aiToggleUrl    = @json(route('whatsapp.inbox.ai.toggle', $conversation));
  let hasAi          = @json($hasAi);
  const sidebarPollUrl = @json(route('whatsapp.sidebar.poll'))
                       + '?{{ http_build_query(array_filter(['account_id' => $accountId, 'status' => $status !== 'all' ? $status : null])) }}';
  const chatBox = document.getElementById('chatBox');
  const input   = document.getElementById('msgInput');

  // Último ID renderizado (inicializado con el último mensaje ya en pantalla)
  let lastDbId = 0;
  chatBox.querySelectorAll('[data-db-id]').forEach(el => {
    const id = parseInt(el.dataset.dbId, 10);
    if (id > lastDbId) lastDbId = id;
  });

  function scrollBottom() { chatBox.scrollTop = chatBox.scrollHeight; }
  scrollBottom();

  if (input) {
    input.addEventListener('input', function () {
      this.style.height = 'auto';
      this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('sendForm').requestSubmit();
      }
    });
  }

  function escapeHtml(str) {
    return (str ?? '').toString().replace(/[&<>"']/g, m =>
      ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])
    );
  }

  function formatDate(dtStr) {
    const dt = dtStr ? new Date(dtStr) : new Date();
    return String(dt.getDate()).padStart(2,'0') + '/' +
           String(dt.getMonth()+1).padStart(2,'0') + ' ' +
           String(dt.getHours()).padStart(2,'0') + ':' +
           String(dt.getMinutes()).padStart(2,'0');
  }

  function hasDbId(id) { return !!chatBox.querySelector(`[data-db-id="${id}"]`); }
  function hasMessageId(mid) { return mid && !!chatBox.querySelector(`[data-message-id="${CSS.escape(mid)}"]`); }

  function renderContent(bubble, msg, isOut) {
    const type = msg.type || 'text';
    const cls = isOut ? 'text-white underline' : 'text-indigo-700 underline';
    if (type === 'image' && msg.public_url) {
      if (msg.caption) { const d = document.createElement('div'); d.className='whitespace-pre-line mb-1.5 text-xs'; d.textContent=msg.caption; bubble.appendChild(d); }
      const link = document.createElement('a'); link.href=msg.public_url; link.target='_blank'; link.rel='noopener';
      const img = document.createElement('img'); img.src=msg.public_url; img.alt='imagen'; img.className='rounded-lg object-cover cursor-zoom-in'; img.style.cssText='max-width:240px;max-height:200px;display:block;';
      link.appendChild(img); bubble.appendChild(link); return;
    }
    if (type === 'video' && msg.public_url) {
      if (msg.caption) { const d = document.createElement('div'); d.className='whitespace-pre-line mb-1.5 text-xs'; d.textContent=msg.caption; bubble.appendChild(d); }
      const v = document.createElement('video'); v.controls=true; v.className='rounded-lg'; v.style.cssText='max-width:240px;max-height:200px;display:block;';
      const s = document.createElement('source'); s.src=msg.public_url; s.type=msg.mime_type||'video/mp4'; v.appendChild(s); bubble.appendChild(v); return;
    }
    if (type === 'audio' && msg.public_url) {
      const a = document.createElement('audio'); a.controls=true; a.className='w-full';
      const s = document.createElement('source'); s.src=msg.public_url; s.type=msg.mime_type||'audio/ogg'; a.appendChild(s); bubble.appendChild(a); return;
    }
    if (type === 'document' && msg.public_url) {
      if (msg.caption) { const d = document.createElement('div'); d.className='whitespace-pre-line mb-1.5 text-xs'; d.textContent=msg.caption; bubble.appendChild(d); }
      const a = document.createElement('a'); a.href=msg.public_url; a.target='_blank'; a.rel='noopener'; a.className=cls; a.textContent='📎 '+(msg.filename||'Abrir archivo'); bubble.appendChild(a); return;
    }
    const d = document.createElement('div'); d.className='whitespace-pre-line'; d.innerHTML=escapeHtml(msg.body||''); bubble.appendChild(d);
  }

  function addMessageToDom(msg, tempId) {
    if (msg.id && hasDbId(msg.id)) return; // ya está en pantalla
    const isOut = msg.direction === 'outbound';
    const wrap  = document.createElement('div');
    wrap.className = 'flex ' + (isOut ? 'justify-end' : 'justify-start');
    const bubble = document.createElement('div');
    bubble.className = 'max-w-[70%] rounded-2xl px-3 py-2 text-sm shadow-sm ' +
      (isOut ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-white text-gray-900 rounded-bl-sm');
    if (msg.message_id) bubble.dataset.messageId = msg.message_id;
    if (msg.id)         bubble.dataset.dbId       = String(msg.id);
    if (tempId)         bubble.dataset.tempId     = tempId;
    renderContent(bubble, msg, isOut);
    const meta = document.createElement('div');
    meta.className = 'text-[10px] opacity-60 mt-1 flex items-center gap-1 ' + (isOut ? 'justify-end' : '');
    meta.textContent = formatDate(msg.created_at) + (isOut && msg.sent_by?.name ? ' · ' + msg.sent_by.name : '');
    bubble.appendChild(meta);
    wrap.appendChild(bubble);
    chatBox.appendChild(wrap);
    if (msg.id && msg.id > lastDbId) lastDbId = msg.id;
    scrollBottom();
  }

  // ── POLLING mensajes — conversación activa, cada 4s ────────────────────
  async function poll() {
    try {
      const res = await fetch(pollUrl + '?after=' + lastDbId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) return;
      const msgs = await res.json();
      msgs.forEach(addMessageToDom);
    } catch (_) {}
  }
  setInterval(poll, 4000);

  // ── POLLING sidebar — actualiza preview y notificaciones, cada 6s ──────
  function formatSidebarTime(ts) {
    if (!ts) return '';
    const d = new Date(ts * 1000);
    const now = new Date();
    const isToday = d.toDateString() === now.toDateString();
    const pad = n => String(n).padStart(2, '0');
    return isToday
      ? pad(d.getHours()) + ':' + pad(d.getMinutes())
      : pad(d.getDate()) + '/' + pad(d.getMonth() + 1);
  }

  async function pollSidebar() {
    try {
      const res = await fetch(sidebarPollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) return;
      const convs = await res.json();
      convs.forEach(c => {
        const el = document.getElementById('sidebar-conv-' + c.id);
        if (!el) return;
        const prevTs = parseInt(el.dataset.lastTs || '0', 10);
        const newTs  = c.last_message_at || 0;
        // Actualizar preview y hora siempre
        const previewEl = el.querySelector('.sidebar-preview');
        const timeEl    = el.querySelector('.sidebar-time');
        if (previewEl && c.last_message_preview) previewEl.textContent = c.last_message_preview;
        if (timeEl && newTs) timeEl.textContent = formatSidebarTime(newTs);
        // Mostrar punto azul si hay mensaje nuevo y NO es la conversación activa
        if (newTs > prevTs && c.id !== conversationId) {
          const dot = el.querySelector('.unread-dot');
          if (dot) dot.classList.remove('hidden');
          el.dataset.lastTs = newTs;
          // Mover al tope del sidebar
          const list = document.getElementById('sidebarList');
          if (list && el.parentNode === list) list.prepend(el);
        }
      });
    } catch (_) {}
  }
  setInterval(pollSidebar, 6000);

  // ── WEBSOCKET (Reverb/Echo) — si está disponible mejora a tiempo real ──
  async function fetchMessage(dbId) {
    const res = await fetch(@json(url('/whatsapp/messages')) + '/' + dbId, { headers: {'X-Requested-With':'XMLHttpRequest'} });
    if (!res.ok) throw new Error(res.status);
    return res.json();
  }

  function waitForEcho(timeout = 10000) {
    return new Promise((resolve, reject) => {
      const start = Date.now();
      const t = setInterval(() => {
        if (window.Echo?.private) { clearInterval(t); resolve(window.Echo); return; }
        if (Date.now() - start > timeout) { clearInterval(t); reject(new Error('Echo timeout')); }
      }, 150);
    });
  }

  waitForEcho().then(Echo => {
    echoChannel = Echo.private(`whatsapp.conversation.${conversationId}`).listen('.WhatsappMessageReceived', async e => {
      const dbId = e?.id || e?.message?.id || null;
      const fallback = e?.message ?? e;
      try {
        if (dbId) {
          const msg = await fetchMessage(dbId);
          addMessageToDom(msg);
        } else {
          addMessageToDom(fallback);
        }
      } catch (err) {
        if (fallback) addMessageToDom(fallback);
      }
    });
  }).catch(() => { /* polling cubre el caso sin WebSocket */ });

  // ── ADJUNTAR ARCHIVO ─────────────────────────────────────────────────────
  const sendForm    = document.getElementById('sendForm');
  const sendBtn     = document.getElementById('sendBtn');
  const attachBtn   = document.getElementById('attachBtn');
  const mediaInput  = document.getElementById('mediaInput');
  const filePreview = document.getElementById('filePreview');
  const filePreviewName = document.getElementById('filePreviewName');
  const fileClear   = document.getElementById('fileClear');
  const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content;

  attachBtn?.addEventListener('click', () => mediaInput?.click());

  mediaInput?.addEventListener('change', () => {
    const file = mediaInput.files[0];
    if (!file) return;
    const maxMB = file.type.startsWith('video/') ? 16 : 5;
    if (file.size > maxMB * 1024 * 1024) {
      alert(`El archivo supera el límite de ${maxMB} MB.`);
      mediaInput.value = '';
      return;
    }
    filePreviewName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)';
    filePreview.classList.remove('hidden');
  });

  fileClear?.addEventListener('click', () => {
    mediaInput.value = '';
    filePreview.classList.add('hidden');
    filePreviewName.textContent = '';
  });

  // ── ENVÍO AJAX ────────────────────────────────────────────────────────────
  sendForm?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const text = input?.value?.trim();
    const hasFile = mediaInput?.files?.length > 0;
    if (!text && !hasFile) return;

    if (sendBtn) sendBtn.disabled = true;

    // Burbuja optimista
    const tempId = 'temp-' + Date.now();
    const tempMsg = {
      id: null, _tempId: tempId,
      direction: 'outbound',
      type: hasFile ? (mediaInput.files[0].type.startsWith('video/') ? 'video' : 'image') : 'text',
      body: text || (hasFile ? '[' + (mediaInput.files[0].type.startsWith('video/') ? 'video' : 'imagen') + ']' : ''),
      public_url: hasFile ? URL.createObjectURL(mediaInput.files[0]) : null,
      mime_type:  hasFile ? mediaInput.files[0].type : null,
      created_at: new Date().toISOString(),
      sent_by: { name: '' },
    };
    addMessageToDom(tempMsg, tempId);
    if (input) { input.value = ''; input.style.height = 'auto'; }

    // Construir FormData (soporta texto + archivo)
    const formData = new FormData();
    formData.append('_token', csrfToken);
    if (text) formData.append('message', text);
    if (hasFile) formData.append('media', mediaInput.files[0]);

    try {
      const res = await fetch(sendForm.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        body: formData,
      });

      // Limpiar preview de archivo
      if (hasFile) {
        mediaInput.value = '';
        filePreview.classList.add('hidden');
        filePreviewName.textContent = '';
      }

      const contentType = res.headers.get('content-type') || '';
      if (contentType.includes('application/json')) {
        const msg = await res.json();
        const tempEl = chatBox.querySelector(`[data-temp-id="${tempId}"]`);
        if (tempEl && msg.id) {
          tempEl.dataset.dbId = String(msg.id);
          if (msg.message_id) tempEl.dataset.messageId = msg.message_id;
          delete tempEl.dataset.tempId;
          if (msg.id > lastDbId) lastDbId = msg.id;
        }
      }
    } catch (err) {
      console.warn('Send error:', err.message);
    } finally {
      if (sendBtn) sendBtn.disabled = false;
      input?.focus();
    }
  });

  // ── AVATAR COLORS ──────────────────────────────────────────────────────────
  const avatarColors = [
    'bg-green-100 text-green-700','bg-blue-100 text-blue-700','bg-purple-100 text-purple-700',
    'bg-amber-100 text-amber-700','bg-rose-100 text-rose-700','bg-teal-100 text-teal-700'
  ];
  function getAvatarCls(name) {
    let h = 0;
    for (let i = 0; i < name.length; i++) h = (Math.imul(31, h) + name.charCodeAt(i)) | 0;
    return avatarColors[Math.abs(h) % avatarColors.length];
  }

  // ── SWITCH CONVERSACIÓN SIN RECARGA ───────────────────────────────────────
  async function switchConversation(convId, pageUrl) {
    if (convId === conversationId) return;

    // Actualizar sidebar activo de inmediato
    document.querySelectorAll('[data-conv-id]').forEach(el => {
      const active = parseInt(el.dataset.convId) === convId;
      el.classList.toggle('bg-indigo-50', active);
      el.classList.toggle('border-l-indigo-500', active);
      el.classList.toggle('border-l-transparent', !active);
      el.querySelector('.sidebar-name')?.classList.toggle('text-indigo-700', active);
      if (active) el.querySelector('.unread-dot')?.classList.add('hidden');
    });

    // Loading
    chatBox.innerHTML = '<div class="flex items-center justify-center py-8 text-gray-400 text-sm">Cargando…</div>';

    try {
      const res = await fetch('/whatsapp/inbox/' + convId + '/panel', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error(res.status);
      const data = await res.json();

      // Exponer urls + estado para el modal de plantillas
      window.WAPanel = { urls: data.urls };

      // Mostrar/ocultar banner de ventana 24h vencida
      const banner = document.getElementById('windowExpiredBanner');
      if (banner) banner.classList.toggle('hidden', !data.window_expired);

      // Actualizar header chat
      const cName    = data.contact_name || data.contact_phone || '?';
      const initials = (cName.charAt(0) + (cName.charAt(1) || '')).toUpperCase();
      const avcls    = getAvatarCls(cName);
      const avatar   = document.getElementById('chatHeaderAvatar');
      if (avatar) { avatar.textContent = initials; avatar.className = 'size-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0 ' + avcls; }
      const nameEl = document.getElementById('chatHeaderName'); if (nameEl) nameEl.textContent = cName;
      const phoneEl = document.getElementById('chatHeaderPhone'); if (phoneEl) phoneEl.textContent = data.contact_phone || '';
      const statusEl = document.getElementById('chatHeaderStatus');
      if (statusEl) {
        statusEl.textContent = data.status === 'open' ? 'Abierta' : 'Cerrada';
        statusEl.className = 'ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold shrink-0 ' +
          (data.status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600');
      }
      const accountEl = document.getElementById('chatHeaderAccount'); if (accountEl) accountEl.textContent = data.account_name || '';

      // Actualizar badge IA en el header
      const aiBadge = document.getElementById('aiBadge');
      if (aiBadge) {
        if (data.has_ai) {
          const aiOn = data.ai_active !== false;
          aiBadge.className = 'ml-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold shrink-0 ' +
            (aiOn ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-500');
          aiBadge.innerHTML = aiOn
            ? '<span class="size-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span> IA'
            : '<span class="size-1.5 rounded-full bg-red-400 inline-block"></span> IA pausada';
        } else {
          aiBadge.className = 'hidden';
          aiBadge.innerHTML = '';
        }
      }

      // Renderizar mensajes
      chatBox.innerHTML = '';
      lastDbId = 0;
      (data.messages || []).forEach(m => addMessageToDom(m));
      scrollBottom();

      // Actualizar estado JS (guardar ID viejo antes de sobreescribir)
      const oldConvId = conversationId;
      conversationId = data.id;
      pollUrl        = data.urls.messages;
      aiToggleUrl    = data.urls.ai_toggle;
      hasAi          = data.has_ai ?? false;
      if (sendForm) sendForm.action = data.urls.send;

      // Actualizar panel derecho
      const rightPanel = document.getElementById('rightPanel');
      if (rightPanel) {
        rightPanel.innerHTML = buildRightPanel(data);
        if (window.Alpine) Alpine.initTree(rightPanel);
      }

      // Suscripción WebSocket
      if (window.Echo) {
        if (echoChannel) { try { window.Echo.leave('whatsapp.conversation.' + oldConvId); } catch(_){} }
        echoChannel = window.Echo.private('whatsapp.conversation.' + data.id).listen('.WhatsappMessageReceived', async e => {
          const dbId = e?.id || e?.message?.id || null;
          const fallback = e?.message ?? e;
          try {
            if (dbId) { const msg = await fetchMessage(dbId); addMessageToDom(msg); }
            else addMessageToDom(fallback);
          } catch(_) { if (fallback) addMessageToDom(fallback); }
        });
      }

      // URL del browser
      history.pushState({ convId: data.id }, '', pageUrl);

      // En mobile: cambiar al panel de chat
      setMobilePanel('chat');
    } catch (err) {
      console.warn('switchConversation error:', err);
      chatBox.innerHTML = '<div class="flex items-center justify-center py-8 text-red-400 text-sm">Error al cargar conversación.</div>';
    }
  }

  function buildRightPanel(data) {
    const cName    = data.contact_name || data.contact_phone || '?';
    const initials = (cName.charAt(0) + (cName.charAt(1) || '')).toUpperCase();
    const avcls    = getAvatarCls(cName);
    const statusBadge = data.status === 'open'
      ? '<span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold bg-green-100 text-green-700">Abierta</span>'
      : '<span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-500">Cerrada</span>';
    const lastMsgHtml = data.last_message_at
      ? `<div><p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-0.5">Último mensaje</p><p class="text-gray-500 text-xs">${escapeHtml(data.last_message_at)}</p></div>`
      : '';
    const accountHtml = data.account_name
      ? `<div><p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-0.5">Cuenta WhatsApp</p><p class="text-gray-800">${escapeHtml(data.account_name)}</p></div>`
      : '';

    const aiActive = data.ai_active ?? true;
    const aiCardCls  = aiActive
      ? 'px-4 py-3 border-b border-green-100 bg-green-50 cursor-pointer select-none transition-colors hover:bg-green-100'
      : 'px-4 py-3 border-b border-red-100 bg-red-50 cursor-pointer select-none transition-colors hover:bg-red-100';
    const aiSvgCls   = aiActive ? 'text-green-500' : 'text-red-400';
    const aiTitleCls = aiActive ? 'text-green-800' : 'text-red-700';
    const aiLblCls   = aiActive ? 'text-green-600' : 'text-red-500';
    const aiDotCls   = aiActive ? 'bg-green-500 animate-pulse' : 'bg-red-400';
    const aiChevCls  = aiActive ? 'text-green-400' : 'text-red-300';
    const aiLblTxt   = aiActive ? 'Activo — toca para pausar' : 'Pausado — toca para activar';
    const aiSectionHtml = data.has_ai ? `
      <div id="aiToggleSection"
           data-toggle-url="${escapeHtml(data.urls.ai_toggle)}"
           data-active="${aiActive ? '1' : '0'}"
           onclick="toggleAiBot(this)"
           class="${aiCardCls}">
        <div class="flex items-center gap-2">
          <svg class="size-4 shrink-0 ${aiSvgCls}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082"/>
          </svg>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold ${aiTitleCls}">Asistente IA</p>
            <p id="aiToggleLabel" class="flex items-center gap-1 text-[10px] font-semibold mt-0.5 ${aiLblCls}">
              <span id="aiToggleDot" class="size-2 rounded-full inline-block ${aiDotCls}"></span>
              ${aiLblTxt}
            </p>
          </div>
          <svg class="size-4 shrink-0 ${aiChevCls}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </div>` : '';

    let dealHtml;
    if (data.current_deal) {
      const d = data.current_deal;
      const statusMap = { open: 'Abierto', won: 'Ganado', lost: 'Perdido' };
      const statusClsMap = { open: 'bg-green-100 text-green-700', won: 'bg-blue-100 text-blue-700', lost: 'bg-red-100 text-red-600' };
      const sc = statusClsMap[d.status] || 'bg-gray-100 text-gray-500';
      const sl = statusMap[d.status] || d.status;
      const sub = d.pipeline_name + (d.stage_name ? ' › ' + d.stage_name : '');
      dealHtml = `<a href="${escapeHtml(d.edit_url)}"
        class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 hover:bg-indigo-50 hover:border-indigo-200 transition group">
        <div class="min-w-0 flex-1">
          <p class="text-xs font-semibold text-gray-900 truncate group-hover:text-indigo-700">${escapeHtml(d.title)}</p>
          <p class="text-[10px] text-gray-400 truncate mt-0.5">${escapeHtml(sub)}</p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
          <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[9px] font-semibold ${sc}">${sl}</span>
          <svg class="size-3 text-gray-300 group-hover:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </a>`;
    } else {
      const pipelineOptions = (data.pipelines || []).map(p =>
        `<option value="${p.id}">${escapeHtml(p.name)}</option>`
      ).join('');
      const defaultTitle = escapeHtml(cName) + ' - WhatsApp';
      dealHtml = `<div x-data="{ open: false }" class="space-y-2">
        <div class="rounded-xl border border-dashed border-gray-200 p-3 text-center">
          <p class="text-xs text-gray-400 mb-2">Sin negociación vinculada.</p>
          <button @click="open = !open" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Crear negociación
          </button>
        </div>
        <div x-show="open" x-transition class="rounded-xl border border-indigo-100 bg-indigo-50 p-3 space-y-2">
          <form method="POST" action="${escapeHtml(data.urls.create_deal)}">
            <input type="hidden" name="_token" value="${escapeHtml(document.querySelector('meta[name=csrf-token]')?.content || '')}">
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wide text-gray-500 mb-1">Pipeline</label>
              <select name="pipeline_id" required class="w-full text-xs rounded-lg border-gray-200 bg-white py-1.5 text-gray-800">
                <option value="">Selecciona un pipeline…</option>${pipelineOptions}
              </select>
            </div>
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wide text-gray-500 mb-1">Título (opcional)</label>
              <input type="text" name="title" placeholder="${defaultTitle}" class="w-full text-xs rounded-lg border-gray-200 bg-white py-1.5 text-gray-800 placeholder-gray-400">
            </div>
            <div class="flex gap-2 pt-1">
              <button type="submit" class="flex-1 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">Crear</button>
              <button type="button" @click="open = false" class="flex-1 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200 transition">Cancelar</button>
            </div>
          </form>
        </div>
      </div>`;
    }

    return `
      <div class="h-14 px-4 flex items-center gap-2 border-b border-gray-100 shrink-0">
        <button onclick="setMobilePanel('chat')"
                class="wa-mobile-btn shrink-0 p-1.5 -ml-1 rounded-lg text-gray-500 hover:bg-gray-100 transition" title="Volver al chat">
          <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <span class="text-sm font-semibold text-gray-900">Detalles del contacto</span>
      </div>
      <div class="flex flex-col items-center py-5 border-b border-gray-100">
        <div class="size-14 rounded-full flex items-center justify-center text-xl font-bold ${avcls}">${initials}</div>
        <p class="mt-2 text-sm font-semibold text-gray-900">${escapeHtml(cName)}</p>
        ${statusBadge}
      </div>
      <div class="px-4 py-4 space-y-3 border-b border-gray-100 text-sm">
        <div><p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-0.5">Teléfono</p>
          <p class="text-gray-800 font-medium">${escapeHtml(data.contact_phone || '')}</p>
        </div>
        ${accountHtml}${lastMsgHtml}
      </div>
      ${aiSectionHtml}
      <div class="px-4 py-4">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-semibold text-gray-700">Negociación</p>
        </div>
        ${dealHtml}
      </div>`;
  }

  // ── TOGGLE IA POR CONVERSACIÓN ────────────────────────────────────────────
  window.toggleAiBot = async function(btn) {
    const currentlyActive = btn.dataset.active === '1';
    const newActive = !currentlyActive;

    // Optimistic UI
    applyAiToggleState(btn, newActive);

    try {
      const res = await fetch(aiToggleUrl, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
      });
      if (!res.ok) throw new Error(res.status);
      const data = await res.json();
      applyAiToggleState(btn, data.ai_active);
    } catch(e) {
      // Revert on failure
      applyAiToggleState(btn, currentlyActive);
    }
  };

  function applyAiToggleState(card, active) {
    card.dataset.active = active ? '1' : '0';
    // Tarjeta completa
    card.className = active
      ? 'px-4 py-3 border-b border-green-100 bg-green-50 cursor-pointer select-none transition-colors hover:bg-green-100'
      : 'px-4 py-3 border-b border-red-100 bg-red-50 cursor-pointer select-none transition-colors hover:bg-red-100';
    // SVGs (primero = icono IA, último = chevron)
    const svgs = card.querySelectorAll('svg');
    if (svgs[0]) svgs[0].className = 'size-4 shrink-0 ' + (active ? 'text-green-500' : 'text-red-400');
    if (svgs[1]) svgs[1].className = 'size-4 shrink-0 ' + (active ? 'text-green-400' : 'text-red-300');
    // Título
    const title = card.querySelector('p.text-xs.font-semibold');
    if (title) title.className = 'text-xs font-semibold ' + (active ? 'text-green-800' : 'text-red-700');
    // Label + dot
    const label = document.getElementById('aiToggleLabel');
    if (label) {
      label.className = 'flex items-center gap-1 text-[10px] font-semibold mt-0.5 ' + (active ? 'text-green-600' : 'text-red-500');
      const dot = document.getElementById('aiToggleDot');
      if (dot) dot.className = 'size-2 rounded-full inline-block ' + (active ? 'bg-green-500 animate-pulse' : 'bg-red-400');
      const textNode = label.childNodes[label.childNodes.length - 1];
      if (textNode) textNode.textContent = active ? ' Activo — toca para pausar' : ' Pausado — toca para activar';
    }
    // Actualizar badge del header
    const badge = document.getElementById('aiBadge');
    if (badge) {
      badge.className = active
        ? 'ml-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold bg-green-100 text-green-700 shrink-0'
        : 'ml-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold bg-red-100 text-red-500 shrink-0';
      badge.innerHTML = active
        ? '<span class="size-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span> IA'
        : '<span class="size-1.5 rounded-full bg-red-400 inline-block"></span> IA pausada';
    }
  }

  // Interceptar clicks del sidebar
  document.getElementById('sidebarList')?.addEventListener('click', function (e) {
    const link = e.target.closest('[data-conv-id]');
    if (!link) return;
    e.preventDefault();
    switchConversation(parseInt(link.dataset.convId), link.href);
  });

  // Browser back/forward
  window.addEventListener('popstate', function (e) {
    const convId = e.state?.convId;
    if (convId) switchConversation(convId, location.href);
    else location.reload();
  });

})();
</script>

{{-- ════════════════════════════════════════
     MODAL DE PLANTILLAS DE WHATSAPP
     ════════════════════════════════════════ --}}
<div id="templatesModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
      <h3 class="text-base font-bold text-gray-900">Plantillas aprobadas de WhatsApp</h3>
      <div class="flex items-center gap-2">
        <button type="button" id="reloadTemplatesBtn"
                class="p-1.5 rounded-md text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Recargar lista">
          <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
        <button type="button" onclick="closeTemplatesModal()"
                class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition">
          <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    {{-- Búsqueda --}}
    <div class="px-5 py-3 border-b border-gray-100">
      <input type="text" id="tplSearch" placeholder="Buscar plantilla por nombre…"
             class="w-full rounded-lg border-gray-200 text-sm py-2 px-3">
    </div>

    {{-- Listado --}}
    <div id="templatesList" class="flex-1 overflow-y-auto px-5 py-3 space-y-2">
      <p id="templatesLoading" class="text-center text-sm text-gray-400 py-8">Cargando plantillas…</p>
    </div>

    {{-- Formulario de variables (oculto hasta seleccionar) --}}
    <div id="tplFormSection" class="hidden border-t border-gray-200 px-5 py-4 bg-gray-50">
      <div class="mb-3">
        <p class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Plantilla seleccionada</p>
        <p class="text-sm font-bold text-gray-900" id="tplSelectedName"></p>
        <p class="text-xs text-gray-500" id="tplSelectedLang"></p>
      </div>

      <div id="tplPreviewBox" class="bg-white border border-gray-200 rounded-lg p-3 mb-3 text-sm whitespace-pre-line"></div>

      <div id="tplVariablesBox" class="space-y-2 mb-3"></div>

      <div class="flex gap-2 justify-end">
        <button type="button" onclick="resetTemplateSelection()"
                class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">Cancelar</button>
        <button type="button" id="sendTplBtn"
                class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
          Enviar plantilla
        </button>
      </div>
    </div>
  </div>
</div>

@verbatim
<script>
(function () {
  const modal     = document.getElementById('templatesModal');
  const listBox   = document.getElementById('templatesList');
  const searchBox = document.getElementById('tplSearch');
  const reloadBtn = document.getElementById('reloadTemplatesBtn');
  const formBox   = document.getElementById('tplFormSection');
  const sendBtn   = document.getElementById('sendTplBtn');

  let allTemplates = [];
  let selected = null;

  function getCurrentConvUrls() {
    // El JS de la conversación principal expone estos URLs en window.WAPanel?.urls
    if (window.WAPanel?.urls) return window.WAPanel.urls;
    // Fallback: rutas estáticas usando el path actual (debe contener /whatsapp/inbox/{id})
    const m = location.pathname.match(/\/whatsapp\/inbox\/(\d+)/);
    if (!m) return null;
    const base = '/whatsapp/inbox/' + m[1];
    return {
      templates:     base + '/templates',
      send_template: base + '/send-template',
    };
  }

  window.openTemplatesModal = function () {
    modal.classList.remove('hidden');
    resetTemplateSelection();
    loadTemplates(false);
  };

  window.closeTemplatesModal = function () {
    modal.classList.add('hidden');
  };

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeTemplatesModal();
  });

  function loadTemplates(force) {
    const urls = getCurrentConvUrls();
    if (!urls?.templates) return;

    listBox.innerHTML = '<p class="text-center text-sm text-gray-400 py-8">Cargando plantillas…</p>';

    fetch(urls.templates + (force ? '?refresh=1' : ''), {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
    })
    .then(r => r.json())
    .then(data => {
      if (!data.ok) {
        listBox.innerHTML = '<div class="text-center py-8"><p class="text-sm text-red-600">' +
          (data.message || 'Error al cargar plantillas') + '</p></div>';
        return;
      }
      allTemplates = data.templates || [];
      renderList(allTemplates);
    })
    .catch(err => {
      listBox.innerHTML = '<p class="text-center text-sm text-red-600 py-8">Error: ' + err.message + '</p>';
    });
  }

  function renderList(items) {
    if (!items.length) {
      listBox.innerHTML = '<p class="text-center text-sm text-gray-400 py-8">No hay plantillas aprobadas. Créalas en Meta Business Manager.</p>';
      return;
    }

    listBox.innerHTML = items.map(t => {
      const cat = (t.category || '').toLowerCase();
      const catColor = cat === 'marketing' ? 'bg-purple-100 text-purple-700'
                     : cat === 'utility'   ? 'bg-blue-100 text-blue-700'
                     : 'bg-gray-100 text-gray-600';
      const body = (t.components || []).find(c => c.type === 'BODY')?.text || '';
      const preview = body.length > 120 ? body.substring(0, 120) + '…' : body;
      return `
        <button type="button" data-tpl='${JSON.stringify(t).replace(/'/g, "&apos;")}'
                class="tpl-item w-full text-left bg-white border border-gray-200 hover:border-indigo-400 rounded-lg p-3 transition">
          <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-sm text-gray-900 truncate">${t.name}</p>
              <p class="text-xs text-gray-400 mt-0.5">${t.language}</p>
            </div>
            <span class="inline-flex shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ${catColor}">${t.category || '—'}</span>
          </div>
          ${preview ? '<p class="mt-2 text-xs text-gray-600 line-clamp-2">' + escapeHtml(preview) + '</p>' : ''}
        </button>`;
    }).join('');

    listBox.querySelectorAll('.tpl-item').forEach(btn => {
      btn.addEventListener('click', () => selectTemplate(JSON.parse(btn.dataset.tpl.replace(/&apos;/g, "'"))));
    });
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function selectTemplate(tpl) {
    selected = tpl;
    document.getElementById('tplSelectedName').textContent = tpl.name;
    document.getElementById('tplSelectedLang').textContent = 'Idioma: ' + tpl.language;

    const body = (tpl.components || []).find(c => c.type === 'BODY');
    const header = (tpl.components || []).find(c => c.type === 'HEADER');
    const footer = (tpl.components || []).find(c => c.type === 'FOOTER');

    const bodyVarCount = body ? (body.text.match(/\{\{\d+\}\}/g) || []).length : 0;
    const headerVarCount = (header && header.format === 'TEXT') ? (header.text.match(/\{\{\d+\}\}/g) || []).length : 0;

    let preview = '';
    if (header && header.format === 'TEXT') preview += '*' + header.text + '*\n\n';
    if (body) preview += body.text;
    if (footer) preview += '\n\n_' + footer.text + '_';
    document.getElementById('tplPreviewBox').textContent = preview || '(sin contenido)';

    const varsBox = document.getElementById('tplVariablesBox');
    varsBox.innerHTML = '';
    let html = '';
    for (let i = 0; i < headerVarCount; i++) {
      html += `<div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Header — Variable {{${i+1}}}</label>
        <input type="text" data-vartype="header" data-varidx="${i}" class="w-full rounded-lg border-gray-200 text-sm py-2" placeholder="Valor para {{${i+1}}}">
      </div>`;
    }
    for (let i = 0; i < bodyVarCount; i++) {
      html += `<div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Body — Variable {{${i+1}}}</label>
        <input type="text" data-vartype="body" data-varidx="${i}" class="w-full rounded-lg border-gray-200 text-sm py-2" placeholder="Valor para {{${i+1}}}">
      </div>`;
    }
    if (!html) {
      html = '<p class="text-xs text-gray-400">Esta plantilla no tiene variables. Puedes enviarla directamente.</p>';
    }
    varsBox.innerHTML = html;

    formBox.classList.remove('hidden');
  }

  window.resetTemplateSelection = function () {
    selected = null;
    formBox.classList.add('hidden');
    document.getElementById('tplVariablesBox').innerHTML = '';
    document.getElementById('tplPreviewBox').textContent = '';
  };

  searchBox.addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase().trim();
    renderList(q
      ? allTemplates.filter(t => t.name.toLowerCase().includes(q) || (t.category || '').toLowerCase().includes(q))
      : allTemplates);
  });

  reloadBtn.addEventListener('click', () => loadTemplates(true));

  sendBtn.addEventListener('click', () => {
    if (!selected) return;
    const urls = getCurrentConvUrls();
    if (!urls?.send_template) return;

    const headerParams = [];
    const bodyParams   = [];
    document.querySelectorAll('#tplVariablesBox input').forEach(inp => {
      if (inp.dataset.vartype === 'header') headerParams[parseInt(inp.dataset.varidx, 10)] = inp.value;
      else bodyParams[parseInt(inp.dataset.varidx, 10)] = inp.value;
    });

    // Validar que todas estén llenas
    const allFilled = [...headerParams, ...bodyParams].every(v => (v ?? '').toString().trim() !== '');
    const hasVars = headerParams.length + bodyParams.length > 0;
    if (hasVars && !allFilled) {
      alert('Completa todas las variables antes de enviar.');
      return;
    }

    // Construir preview con valores reemplazados
    const bodyComp = (selected.components || []).find(c => c.type === 'BODY');
    let preview = bodyComp?.text || '';
    bodyParams.forEach((v, i) => { preview = preview.replace(new RegExp('\\{\\{' + (i+1) + '\\}\\}', 'g'), v); });

    sendBtn.disabled = true;
    sendBtn.textContent = 'Enviando…';

    fetch(urls.send_template, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept':       'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      body: JSON.stringify({
        name:          selected.name,
        language:      selected.language,
        body_params:   bodyParams,
        header_params: headerParams,
        preview:       preview,
      }),
    })
    .then(r => r.json().then(d => ({ status: r.status, data: d })))
    .then(({ status, data }) => {
      sendBtn.disabled = false;
      sendBtn.textContent = 'Enviar plantilla';
      if (status === 200 && data.ok) {
        closeTemplatesModal();
        // Refrescar el panel actual de mensajes (la función ya existe en el JS principal)
        if (typeof refreshMessages === 'function') refreshMessages();
        else if (typeof loadConversation === 'function') loadConversation();
      } else {
        alert('❌ ' + (data.message || 'Error al enviar plantilla'));
      }
    })
    .catch(err => {
      sendBtn.disabled = false;
      sendBtn.textContent = 'Enviar plantilla';
      alert('Error: ' + err.message);
    });
  });
})();
</script>
@endverbatim

</x-app-layout>
