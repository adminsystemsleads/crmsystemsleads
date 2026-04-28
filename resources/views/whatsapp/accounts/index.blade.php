<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">WhatsApp – Cuentas</h2>
      <a href="{{ route('whatsapp.accounts.create') }}"
         class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
        + Conectar cuenta
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

      @if(session('status'))
        <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
      @endif

      <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="text-left text-gray-600 border-b">
              <tr>
                <th class="py-2 pr-4">Nombre</th>
                <th class="py-2 pr-4">Phone Number ID</th>
                <th class="py-2 pr-4">Pipeline</th>
                <th class="py-2 pr-4">Activo</th>
                <th class="py-2 pr-4"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($accounts as $a)
                <tr class="border-b">
                  <td class="py-2 pr-4 font-medium">{{ $a->name }}</td>
                  <td class="py-2 pr-4">{{ $a->phone_number_id }}</td>
                  <td class="py-2 pr-4">{{ $a->pipeline->name ?? '-' }}</td>
                  <td class="py-2 pr-4">
                    <span class="px-2 py-1 rounded text-xs {{ $a->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                      {{ $a->is_active ? 'Sí' : 'No' }}
                    </span>
                  </td>
                  <td class="py-2 pr-4 text-right">
                    <a class="text-indigo-600 hover:underline mr-3" href="{{ route('whatsapp.accounts.edit', $a) }}">Editar</a>
                    <form class="inline" method="POST" action="{{ route('whatsapp.accounts.destroy', $a) }}"
                          onsubmit="return confirm('¿Eliminar esta cuenta?');">
                      @csrf
                      @method('DELETE')
                      <button class="text-red-600 hover:underline" type="submit">Eliminar</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td class="py-3 text-gray-500" colspan="5">No hay cuentas conectadas.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          <a href="{{ route('whatsapp.inbox.index') }}" class="text-indigo-600 hover:underline text-sm">
            Ir al Inbox →
          </a>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
