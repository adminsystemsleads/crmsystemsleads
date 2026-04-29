<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold text-gray-800">Contactos</h2>
      <a href="{{ route('contacts.create') }}"
         class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo contacto
      </a>
    </div>
  </x-slot>

  <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

    @if(session('status'))
      <div class="mb-4 flex items-center gap-2 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        <svg class="size-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('status') }}
      </div>
    @endif

    {{-- Barra de búsqueda y filtros --}}
    <div class="mb-5 flex flex-wrap items-center gap-3">
      <form method="GET" action="{{ route('contacts.index') }}" class="flex flex-1 items-center gap-2 min-w-0">
        <div class="relative flex-1 min-w-0 max-w-sm">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input type="text" name="q" value="{{ $q }}"
                 placeholder="Buscar por nombre, email, teléfono…"
                 class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-indigo-400 focus:border-indigo-400">
        </div>
        <select name="status" onchange="this.form.submit()"
                class="text-sm border border-gray-200 rounded-lg py-2 px-3 bg-white text-gray-700">
          <option value="">Todos los estados</option>
          @foreach(['nuevo' => 'Nuevo', 'activo' => 'Activo', 'cliente' => 'Cliente', 'inactivo' => 'Inactivo', 'perdido' => 'Perdido'] as $val => $label)
            <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm hover:bg-gray-700 transition">
          Buscar
        </button>
        @if($q || $status)
          <a href="{{ route('contacts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
        @endif
      </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Contacto</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hidden md:table-cell">Teléfono</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hidden lg:table-cell">Empresa</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 hidden lg:table-cell">Origen</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</th>
            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 hidden sm:table-cell">Negocios</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          @forelse($contacts as $contact)
            @php
              $colors = ['bg-indigo-100 text-indigo-700','bg-green-100 text-green-700','bg-amber-100 text-amber-700',
                         'bg-rose-100 text-rose-700','bg-purple-100 text-purple-700','bg-blue-100 text-blue-700'];
              $avatarCls = $colors[abs(crc32($contact->name)) % count($colors)];
              $initials  = strtoupper(mb_substr($contact->name, 0, 1) . (mb_substr($contact->name, 1, 1) ?: ''));
            @endphp
            <tr class="hover:bg-gray-50 transition">
              {{-- Contacto --}}
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="size-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0 {{ $avatarCls }}">
                    {{ $initials }}
                  </div>
                  <div class="min-w-0">
                    <a href="{{ route('contacts.edit', $contact) }}"
                       class="font-semibold text-gray-900 hover:text-indigo-600 truncate block">
                      {{ $contact->name }}
                    </a>
                    @if($contact->email)
                      <span class="text-xs text-gray-500 truncate block">{{ $contact->email }}</span>
                    @endif
                  </div>
                </div>
              </td>
              {{-- Teléfono --}}
              <td class="px-4 py-3 hidden md:table-cell text-gray-700">{{ $contact->phone ?? '—' }}</td>
              {{-- Empresa --}}
              <td class="px-4 py-3 hidden lg:table-cell">
                <div class="text-gray-700">{{ $contact->company ?? '—' }}</div>
                @if($contact->position)
                  <div class="text-xs text-gray-400">{{ $contact->position }}</div>
                @endif
              </td>
              {{-- Origen --}}
              <td class="px-4 py-3 hidden lg:table-cell text-gray-500 text-xs capitalize">{{ $contact->source ?? '—' }}</td>
              {{-- Estado --}}
              <td class="px-4 py-3">
                @php
                  $statusCls = match($contact->status) {
                    'activo'   => 'bg-green-100 text-green-700',
                    'cliente'  => 'bg-blue-100 text-blue-700',
                    'inactivo' => 'bg-gray-100 text-gray-500',
                    'perdido'  => 'bg-red-100 text-red-600',
                    default    => 'bg-amber-100 text-amber-700',
                  };
                @endphp
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusCls }} capitalize">
                  {{ $contact->status ?? 'nuevo' }}
                </span>
              </td>
              {{-- Negocios --}}
              <td class="px-4 py-3 text-center hidden sm:table-cell">
                @if($contact->deals_count > 0)
                  <span class="inline-flex items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-xs font-semibold w-6 h-6">
                    {{ $contact->deals_count }}
                  </span>
                @else
                  <span class="text-gray-300">—</span>
                @endif
              </td>
              {{-- Acciones --}}
              <td class="px-4 py-3 text-right">
                <a href="{{ route('contacts.edit', $contact) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:border-indigo-300 hover:text-indigo-600 transition">
                  <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  Editar
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-12 text-center">
                <svg class="size-10 mx-auto text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-sm text-gray-400">No se encontraron contactos.</p>
                <a href="{{ route('contacts.create') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">
                  Crear el primero →
                </a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($contacts->hasPages())
      <div class="mt-4">{{ $contacts->withQueryString()->links() }}</div>
    @endif

  </div>
</x-app-layout>
