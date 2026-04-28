<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Chat – {{ $conversation->contact_name ?? $conversation->contact_phone }}
      </h2>
      <a href="{{ route('whatsapp.inbox.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Volver</a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

      {{-- Panel izquierdo --}}
      <div class="bg-white shadow-sm sm:rounded-lg p-5 md:col-span-1 space-y-4">

        {{-- Contacto --}}
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Contacto</p>
          <p class="text-sm font-semibold text-gray-900">{{ $conversation->contact_name ?? '—' }}</p>
          <p class="text-xs text-gray-500">{{ $conversation->contact_phone }}</p>
        </div>

        {{-- Cuenta WhatsApp --}}
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Cuenta</p>
          <p class="text-sm text-gray-700">{{ $conversation->account->name ?? '—' }}</p>
        </div>

        {{-- Estado --}}
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Estado</p>
          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold
              {{ $conversation->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
            {{ $conversation->status === 'open' ? 'Abierta' : 'Cerrada' }}
          </span>
        </div>

        {{-- Negociación vinculada --}}
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1">Negociación</p>
          @if($currentDeal)
            <a href="{{ route('deals.edit', [$currentDeal->pipeline_id, $currentDeal->id]) }}"
               class="flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 hover:bg-indigo-100 transition group">
              <svg class="size-4 shrink-0 text-indigo-400 group-hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-indigo-700 truncate">{{ $currentDeal->title }}</p>
                <p class="text-[11px] text-indigo-400">
                  {{ ucfirst($currentDeal->status) }}
                  @if($currentDeal->amount)
                    · {{ number_format($currentDeal->amount, 2) }} {{ $currentDeal->currency }}
                  @endif
                </p>
              </div>
              <svg class="size-4 shrink-0 text-indigo-300 ml-auto group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </a>
          @else
            <p class="text-sm text-gray-400 italic">Sin negociación vinculada.</p>
          @endif
        </div>

      </div>

      {{-- Chat --}}
      <div class="bg-white shadow-sm sm:rounded-lg p-0 md:col-span-2 overflow-hidden">
        @if(session('status'))
          <div class="p-4 text-sm text-green-600 border-b">{{ session('status') }}</div>
        @endif

        <div id="chatBox" class="p-4 h-[60vh] overflow-y-auto space-y-3">
          @foreach($conversation->messages as $m)
            <div class="flex {{ $m->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
              <div class="max-w-[75%] rounded-lg px-3 py-2 text-sm
                          {{ $m->direction === 'outbound' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-900' }}"
                   data-message-id="{{ $m->message_id ?? '' }}"
                   data-db-id="{{ $m->id ?? '' }}">

                @php($type = $m->type ?? 'text')

                @if($type === 'image' && !empty($m->public_url))
                  @if(!empty($m->caption))
                    <div class="whitespace-pre-line mb-2">{{ $m->caption }}</div>
                  @endif
                  <img src="{{ $m->public_url }}" alt="imagen"
                       class="rounded-md max-w-full h-auto border border-black/5" />
                @elseif($type === 'video' && !empty($m->public_url))
                  @if(!empty($m->caption))
                    <div class="whitespace-pre-line mb-2">{{ $m->caption }}</div>
                  @endif
                  <video controls class="rounded-md max-w-full h-auto border border-black/5">
                    <source src="{{ $m->public_url }}" type="{{ $m->mime_type ?? 'video/mp4' }}">
                  </video>
                @elseif($type === 'audio' && !empty($m->public_url))
                  <audio controls class="w-full">
                    <source src="{{ $m->public_url }}" type="{{ $m->mime_type ?? 'audio/ogg' }}">
                  </audio>
                @elseif($type === 'document' && !empty($m->public_url))
                  @if(!empty($m->caption))
                    <div class="whitespace-pre-line mb-2">{{ $m->caption }}</div>
                  @endif
                  <a href="{{ $m->public_url }}" target="_blank" rel="noopener"
                     class="{{ $m->direction === 'outbound' ? 'text-white underline' : 'text-indigo-700 underline' }}">
                    📎 {{ $m->filename ?? 'Abrir archivo' }}
                  </a>
                @else
                  <div class="whitespace-pre-line">{{ $m->body }}</div>
                @endif

                <div class="text-[10px] opacity-80 mt-1">
                  {{ optional($m->created_at)->format('d/m H:i') }}
                  @if($m->direction === 'outbound' && !empty($m->sentBy))
                    • {{ $m->sentBy->name }}
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <form id="sendForm" method="POST" action="{{ route('whatsapp.inbox.send', $conversation) }}" class="p-4 border-t flex gap-2">
          @csrf
          <input id="msgInput" name="message" class="flex-1 border-gray-300 rounded-md shadow-sm"
                 placeholder="Escribe un mensaje..." value="{{ old('message') }}" autocomplete="off">
          <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Enviar</button>
        </form>

        @error('message')
          <div class="px-4 pb-4 text-sm text-red-600">{{ $message }}</div>
        @enderror
      </div>

    </div>
  </div>

  <script>
    (function () {
      const conversationId = @json($conversation->id);
      const chatBox = document.getElementById('chatBox');
      const input = document.getElementById('msgInput');

      function scrollBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
      }
      scrollBottom();

      function escapeHtml(str) {
        return (str ?? '').toString().replace(/[&<>"']/g, (m) => ({
          '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
        }[m]));
      }

      function formatDate(dtStr) {
        const dt = dtStr ? new Date(dtStr) : new Date();
        const hh = String(dt.getHours()).padStart(2,'0');
        const mm = String(dt.getMinutes()).padStart(2,'0');
        const dd = String(dt.getDate()).padStart(2,'0');
        const mo = String(dt.getMonth()+1).padStart(2,'0');
        return `${dd}/${mo} ${hh}:${mm}`;
      }

      function hasMessageId(messageId) {
        if (!messageId) return false;
        return !!chatBox.querySelector(`[data-message-id="${CSS.escape(messageId)}"]`);
      }

      function renderMessageContent(bubble, msg) {
        const type = msg.type || 'text';
        const caption = msg.caption || null;

        if (type === 'image' && msg.public_url) {
          if (caption) {
            const cap = document.createElement('div');
            cap.className = 'whitespace-pre-line mb-2';
            cap.textContent = caption;
            bubble.appendChild(cap);
          }
          const img = document.createElement('img');
          img.src = msg.public_url;
          img.alt = 'imagen';
          img.className = 'rounded-md max-w-full h-auto border border-black/5';
          bubble.appendChild(img);
          return;
        }

        if (type === 'video' && msg.public_url) {
          if (caption) {
            const cap = document.createElement('div');
            cap.className = 'whitespace-pre-line mb-2';
            cap.textContent = caption;
            bubble.appendChild(cap);
          }
          const video = document.createElement('video');
          video.controls = true;
          video.className = 'rounded-md max-w-full h-auto border border-black/5';
          const src = document.createElement('source');
          src.src = msg.public_url;
          src.type = msg.mime_type || 'video/mp4';
          video.appendChild(src);
          bubble.appendChild(video);
          return;
        }

        if (type === 'audio' && msg.public_url) {
          const audio = document.createElement('audio');
          audio.controls = true;
          audio.className = 'w-full';
          const src = document.createElement('source');
          src.src = msg.public_url;
          src.type = msg.mime_type || 'audio/ogg';
          audio.appendChild(src);
          bubble.appendChild(audio);
          return;
        }

        if (type === 'document' && msg.public_url) {
          if (caption) {
            const cap = document.createElement('div');
            cap.className = 'whitespace-pre-line mb-2';
            cap.textContent = caption;
            bubble.appendChild(cap);
          }
          const a = document.createElement('a');
          a.href = msg.public_url;
          a.target = '_blank';
          a.rel = 'noopener';
          a.className = 'underline';
          a.textContent = `📎 ${msg.filename || 'Abrir archivo'}`;
          bubble.appendChild(a);
          return;
        }

        // default text
        const body = document.createElement('div');
        body.className = 'whitespace-pre-line';
        body.innerHTML = escapeHtml(msg.body || '');
        bubble.appendChild(body);
      }

      function addMessageToDom(msg) {
        const isOut = msg.direction === 'outbound';

        const wrap = document.createElement('div');
        wrap.className = 'flex ' + (isOut ? 'justify-end' : 'justify-start');

        const bubble = document.createElement('div');
        bubble.className =
          'max-w-[75%] rounded-lg px-3 py-2 text-sm ' +
          (isOut ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-900');

        if (msg.message_id) bubble.dataset.messageId = msg.message_id;
        if (msg.id) bubble.dataset.dbId = msg.id;

        renderMessageContent(bubble, msg);

        const meta = document.createElement('div');
        meta.className = 'text-[10px] opacity-80 mt-1';

        const sentBy = msg.sent_by?.name ? ` • ${msg.sent_by.name}` : '';
        meta.textContent = formatDate(msg.created_at) + (isOut ? sentBy : '');

        bubble.appendChild(meta);
        wrap.appendChild(bubble);
        chatBox.appendChild(wrap);

        scrollBottom();
      }

      // Esperar Echo
      function waitForEcho({ timeoutMs = 15000, intervalMs = 150 } = {}) {
        return new Promise((resolve, reject) => {
          const started = Date.now();
          const t = setInterval(() => {
            if (window.Echo && typeof window.Echo.private === 'function') {
              clearInterval(t);
              resolve(window.Echo);
              return;
            }
            if (Date.now() - started > timeoutMs) {
              clearInterval(t);
              reject(new Error('Echo no se inicializó (timeout).'));
            }
          }, intervalMs);
        });
      }

      async function fetchMessageByDbId(dbId) {
        const res = await fetch(@json(url('/whatsapp/messages')) + '/' + dbId, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('No se pudo traer mensaje: ' + res.status);
        return await res.json();
      }

      waitForEcho()
        .then((Echo) => {
          const channel = `whatsapp.conversation.${conversationId}`;

          Echo.private(channel).listen('.WhatsappMessageReceived', async (e) => {
            // Ideal: el evento manda { message_id: <dbId> }
            const dbId = e?.message_id || e?.message?.id || e?.id || null;

            // fallback: si manda el objeto completo
            const fallbackMsg = e?.message ?? e;

            try {
              if (dbId) {
                const msg = await fetchMessageByDbId(dbId);

                if (msg.message_id && hasMessageId(msg.message_id)) return;
                addMessageToDom(msg);
                return;
              }

              // fallback (menos recomendado)
              if (fallbackMsg?.message_id && hasMessageId(fallbackMsg.message_id)) return;
              addMessageToDom(fallbackMsg);
            } catch (err) {
              console.warn('Realtime: no pude cargar el mensaje completo:', err.message);
              // último fallback: pinta algo
              if (fallbackMsg) addMessageToDom(fallbackMsg);
            }
          });

          console.log('Echo OK: escuchando', channel);
        })
        .catch((err) => console.warn('Realtime desactivado:', err.message));

      document.getElementById('sendForm').addEventListener('submit', () => {
        setTimeout(() => {
          input.value = '';
          scrollBottom();
        }, 50);
      });
    })();
  </script>
</x-app-layout>
