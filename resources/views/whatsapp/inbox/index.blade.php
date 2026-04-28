<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">WhatsApp – Inbox</h2>
      <a href="{{ route('whatsapp.accounts.index') }}" class="text-sm text-indigo-600 hover:underline">
        Administrar cuentas →
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

      <div class="mb-4 flex items-center gap-3">
        <form method="GET" action="{{ route('whatsapp.inbox.index') }}" class="flex items-center gap-2">
          <select name="account_id" class="border-gray-300 rounded-md shadow-sm text-sm">
            <option value="">Todas las cuentas</option>
            @foreach($accounts as $a)
              <option value="{{ $a->id }}" {{ (string)$accountId === (string)$a->id ? 'selected' : '' }}>
                {{ $a->name }}
              </option>
            @endforeach
          </select>
          <button class="px-3 py-2 bg-gray-900 text-white rounded-md text-sm">Filtrar</button>
        </form>
      </div>

      <div class="bg-white shadow-sm sm:rounded-lg p-0 overflow-hidden">
        @forelse($conversations as $c)
          <a href="{{ route('whatsapp.inbox.show', $c) }}"
             class="block px-5 py-4 border-b hover:bg-gray-50">
            <div class="flex justify-between items-center">
              <div>
                <div class="font-semibold text-gray-800">
                  {{ $c->contact_name ?? $c->contact_phone ?? 'Sin nombre' }}
                  <span class="text-xs text-gray-500 ml-2">{{ $c->contact_phone }}</span>
                </div>
                <div class="text-sm text-gray-600 mt-1">
                  {{ $c->last_message_preview ?? '—' }}
                </div>
                <div class="text-xs text-gray-400 mt-1">
                  {{ $c->account->name ?? '-' }}
                </div>
              </div>

              <div class="text-xs text-gray-500">
                {{ $c->last_message_at ? $c->last_message_at->format('d/m H:i') : '' }}
              </div>
            </div>
          </a>
        @empty
          <div class="p-6 text-sm text-gray-500">No hay conversaciones todavía.</div>
        @endforelse
      </div>

      <div class="mt-4">
        {{ $conversations->withQueryString()->links() }}
      </div>

    </div>
  </div>
</x-app-layout>
