<x-app-layout>

@php
  $avatarColors = ['bg-green-100 text-green-700','bg-blue-100 text-blue-700','bg-purple-100 text-purple-700',
                   'bg-amber-100 text-amber-700','bg-rose-100 text-rose-700','bg-teal-100 text-teal-700'];
  function waAvatarIdx(string $name, array $colors): string {
      return $colors[abs(crc32($name)) % count($colors)];
  }
@endphp

<div class="flex flex-col bg-white" style="height:100vh;">

  {{-- BARRA SUPERIOR --}}
  <div class="h-14 shrink-0 flex items-center gap-3 border-b border-gray-200 bg-white px-4">
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

  {{-- TRES PANELES --}}
  <div class="flex flex-1 overflow-hidden">

  {{-- PANEL IZQUIERDO – Lista --}}
  <div class="w-72 shrink-0 flex flex-col border-r border-gray-200">

    <div class="h-12 px-3 flex items-center border-b border-gray-100 bg-gray-50">
      <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Conversaciones</span>
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

    <div class="px-3 py-2 border-b border-gray-100 bg-white">
      <form method="GET" action="{{ route('whatsapp.inbox.index') }}">
        <select name="account_id" onchange="this.form.submit()"
                class="w-full text-xs rounded-lg border-gray-200 bg-gray-50 py-1.5 pr-6 text-gray-700">
          <option value="">Todas las cuentas</option>
          @foreach($accounts as $a)
            <option value="{{ $a->id }}" {{ (string)$accountId === (string)$a->id ? 'selected' : '' }}>
              {{ $a->name }}
            </option>
          @endforeach
        </select>
      </form>
    </div>

    <div class="flex border-b border-gray-100 bg-white shrink-0">
      @foreach(['all' => 'Todo', 'open' => 'Abierto', 'closed' => 'Cerrado'] as $val => $label)
        <a href="{{ route('whatsapp.inbox.index') }}?status={{ $val }}{{ $accountId ? '&account_id='.$accountId : '' }}"
           class="flex-1 py-2 text-center text-xs font-medium transition
                  {{ $status === $val ? 'text-indigo-600 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>

    <div class="overflow-y-auto flex-1 bg-white">
      @forelse($conversations as $c)
        @php
          $cName = $c->contact_name ?? $c->contact_phone ?? '?';
          $cInit = strtoupper(mb_substr($cName,0,1) . (mb_substr($cName,1,1) ?: ''));
          $cAva  = waAvatarIdx($cName, $avatarColors);
          $time  = $c->last_message_at
            ? ($c->last_message_at->isToday() ? $c->last_message_at->format('H:i') : $c->last_message_at->format('d/m'))
            : '';
        @endphp
        <a href="{{ route('whatsapp.inbox.show', $c) }}"
           class="flex items-center gap-3 px-3 py-3 border-b border-gray-50 hover:bg-gray-50 transition border-l-[3px] border-l-transparent">
          <div class="size-10 rounded-full flex items-center justify-center shrink-0 text-sm font-semibold {{ $cAva }}">
            {{ $cInit }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-1">
              <span class="text-sm font-semibold text-gray-900 truncate">{{ $cName }}</span>
              <span class="text-[10px] text-gray-400 shrink-0">{{ $time }}</span>
            </div>
            <p class="text-xs text-gray-500 truncate mt-0.5">{{ $c->last_message_preview ?? '—' }}</p>
          </div>
          @if($c->status === 'open')
            <span class="size-2 rounded-full bg-green-400 shrink-0"></span>
          @endif
        </a>
      @empty
        <div class="p-8 text-center">
          <svg class="size-10 mx-auto text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          <p class="text-sm text-gray-400">No hay conversaciones.</p>
        </div>
      @endforelse
    </div>

  </div>

  {{-- PANEL CENTRAL – Estado vacío --}}
  <div class="flex-1 flex flex-col items-center justify-center bg-gray-50 text-center px-8">
    <div class="size-16 rounded-full bg-indigo-50 flex items-center justify-center mb-4">
      <svg class="size-8 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
    </div>
    <h3 class="text-sm font-semibold text-gray-700 mb-1">Selecciona una conversación</h3>
    <p class="text-xs text-gray-400">Elige una conversación de la lista para ver los mensajes.</p>
  </div>

  </div>{{-- fin tres paneles --}}

</div>{{-- fin layout --}}

</x-app-layout>
