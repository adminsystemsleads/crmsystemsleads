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
@endphp

<div class="flex flex-col bg-white" style="height:100vh;">

  {{-- ════ BARRA SUPERIOR ════ --}}
  <div class="h-14 shrink-0 flex items-center gap-3 border-b border-gray-200 bg-white px-4">
    {{-- Espacio para el hamburger del nav en pantallas pequeñas --}}
    <div class="w-8 lg:hidden shrink-0"></div>
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
  <div class="flex flex-1 overflow-hidden">

  {{-- ════════════════════════════════════════
       PANEL IZQUIERDO – Lista de conversaciones
       ════════════════════════════════════════ --}}
  <div class="w-72 shrink-0 flex flex-col border-r border-gray-200">

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
  <div class="flex-1 flex flex-col min-w-0 bg-gray-50">

    {{-- Cabecera del chat --}}
    <div class="h-14 px-4 flex items-center gap-3 border-b border-gray-200 bg-white shrink-0">
      <div class="size-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0 {{ $avatarCls }}">
        {{ $initials }}
      </div>
      <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-900 truncate">{{ $convName }}</p>
        <p class="text-xs text-gray-500">{{ $conversation->contact_phone }}</p>
      </div>
      <span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold shrink-0
          {{ $conversation->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
        {{ $conversation->status === 'open' ? 'Abierta' : 'Cerrada' }}
      </span>
      @if($conversation->account)
        <span class="ml-auto text-xs text-gray-400 hidden lg:block">{{ $conversation->account->name }}</span>
      @endif
    </div>

    {{-- Mensajes --}}
    <div id="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3">

      @foreach($conversation->messages as $m)
        @php $isOut = $m->direction === 'outbound'; $type = $m->type ?? 'text'; @endphp
        <div class="flex {{ $isOut ? 'justify-end' : 'justify-start' }}"
             data-message-id="{{ $m->message_id ?? '' }}"
             data-db-id="{{ $m->id ?? '' }}">
          <div class="max-w-[70%] rounded-2xl px-3 py-2 text-sm shadow-sm
                      {{ $isOut ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-white text-gray-900 rounded-bl-sm' }}">

            @if($type === 'image' && !empty($m->public_url))
              @if(!empty($m->caption))<div class="whitespace-pre-line mb-1.5 text-xs">{{ $m->caption }}</div>@endif
              <img src="{{ $m->public_url }}" alt="imagen" class="rounded-lg max-w-full h-auto"/>
            @elseif($type === 'video' && !empty($m->public_url))
              @if(!empty($m->caption))<div class="whitespace-pre-line mb-1.5 text-xs">{{ $m->caption }}</div>@endif
              <video controls class="rounded-lg max-w-full h-auto">
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
      @if(session('status'))
        <div class="mb-2 text-xs text-green-600">{{ session('status') }}</div>
      @endif
      <form id="sendForm" method="POST" action="{{ route('whatsapp.inbox.send', $conversation) }}"
            class="flex items-end gap-2">
        @csrf
        <textarea id="msgInput" name="message" rows="1"
                  class="flex-1 resize-none rounded-xl border-gray-200 bg-gray-50 text-sm px-3 py-2 focus:ring-indigo-400 focus:border-indigo-400"
                  placeholder="Escribe un mensaje..."
                  style="min-height:40px;max-height:120px;"
                  autocomplete="off">{{ old('message') }}</textarea>
        <button type="submit"
                class="shrink-0 p-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition">
          <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
          </svg>
        </button>
      </form>
      @error('message')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
      @enderror
    </div>

  </div>

  {{-- ════════════════════════════════════════
       PANEL DERECHO – Detalles del contacto
       ════════════════════════════════════════ --}}
  <div class="w-[280px] shrink-0 border-l border-gray-200 flex flex-col bg-white overflow-y-auto">

    {{-- Header --}}
    <div class="h-14 px-4 flex items-center justify-between border-b border-gray-100 shrink-0">
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

    {{-- Negociación vinculada --}}
    <div class="px-4 py-4">
      <div class="flex items-center justify-between mb-2">
        <p class="text-xs font-semibold text-gray-700">Negociación</p>
      </div>

      @if($currentDeal)
        <a href="{{ route('deals.edit', [$currentDeal->pipeline_id, $currentDeal->id]) }}"
           class="block rounded-xl border border-gray-200 bg-gray-50 p-3 hover:bg-indigo-50 hover:border-indigo-200 transition group">
          <div class="flex items-start gap-2">
            <svg class="size-4 shrink-0 mt-0.5 text-indigo-400 group-hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <div class="min-w-0 flex-1">
              <p class="text-xs font-semibold text-gray-900 truncate group-hover:text-indigo-700">
                {{ $currentDeal->title }}
              </p>
              <p class="text-[11px] text-gray-500 mt-0.5">
                {{ ucfirst($currentDeal->status) }}
                @if($currentDeal->amount)
                  · {{ number_format($currentDeal->amount, 2) }} {{ $currentDeal->currency }}
                @endif
              </p>
              <span class="mt-1 inline-flex items-center rounded-full px-1.5 py-0.5 text-[9px] font-semibold
                  {{ $currentDeal->status === 'open' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $currentDeal->status === 'open' ? 'Abierto' : ucfirst($currentDeal->status) }}
              </span>
            </div>
            <svg class="size-3.5 shrink-0 text-gray-300 group-hover:text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </a>
      @else
        <div class="rounded-xl border border-dashed border-gray-200 p-4 text-center">
          <p class="text-xs text-gray-400">Sin negociación vinculada.</p>
          <p class="text-[10px] text-gray-300 mt-0.5">Se crea automáticamente al recibir el primer mensaje.</p>
        </div>
      @endif
    </div>

  </div>{{-- fin panel derecho --}}

  </div>{{-- fin tres paneles --}}

</div>{{-- fin layout principal --}}

<script>
(function () {
  const conversationId  = @json($conversation->id);
  const pollUrl         = @json(route('whatsapp.inbox.messages', $conversation));
  const sidebarPollUrl  = @json(route('whatsapp.sidebar.poll'))
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
      const img = document.createElement('img'); img.src=msg.public_url; img.alt='imagen'; img.className='rounded-lg max-w-full h-auto'; bubble.appendChild(img); return;
    }
    if (type === 'video' && msg.public_url) {
      if (msg.caption) { const d = document.createElement('div'); d.className='whitespace-pre-line mb-1.5 text-xs'; d.textContent=msg.caption; bubble.appendChild(d); }
      const v = document.createElement('video'); v.controls=true; v.className='rounded-lg max-w-full h-auto';
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
    Echo.private(`whatsapp.conversation.${conversationId}`).listen('.WhatsappMessageReceived', async e => {
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

  // ── ENVÍO AJAX — sin recargar página ────────────────────────────────────
  const sendForm   = document.getElementById('sendForm');
  const sendBtn    = sendForm?.querySelector('button[type=submit]');
  const sendUrl    = sendForm?.action;
  const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content
                  || sendForm?.querySelector('input[name=_token]')?.value;

  sendForm?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const text = input?.value?.trim();
    if (!text) return;

    if (sendBtn) sendBtn.disabled = true;

    // Optimista: mostrar el mensaje en pantalla de inmediato
    const tempId = 'temp-' + Date.now();
    const tempMsg = {
      id: null, _tempId: tempId,
      direction: 'outbound', type: 'text', body: text,
      created_at: new Date().toISOString(),
      sent_by: { name: '' },
    };
    addMessageToDom(tempMsg, tempId);
    if (input) { input.value = ''; input.style.height = 'auto'; }

    try {
      const res = await fetch(sendUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ message: text }),
      });

      const contentType = res.headers.get('content-type') || '';
      if (contentType.includes('application/json')) {
        const msg = await res.json();
        // Reemplazar burbuja temporal con la real (tiene id de DB)
        const tempEl = chatBox.querySelector(`[data-temp-id="${tempId}"]`);
        if (tempEl && msg.id) {
          tempEl.dataset.dbId   = String(msg.id);
          if (msg.message_id) tempEl.dataset.messageId = msg.message_id;
          delete tempEl.dataset.tempId;
          if (msg.id > lastDbId) lastDbId = msg.id;
        }
      }
      // Si devuelve redirect/HTML el mensaje igual fue enviado — el polling lo confirmará
    } catch (err) {
      console.warn('Send response error (message may have been sent):', err.message);
    } finally {
      if (sendBtn) sendBtn.disabled = false;
      input?.focus();
    }
  });
})();
</script>

</x-app-layout>
