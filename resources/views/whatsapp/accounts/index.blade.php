<x-app-layout>
  <x-slot name="header">
    <div class="page-head flex items-center justify-between">
      <h2 class="page-head-title font-semibold text-xl text-gray-800 leading-tight">WhatsApp – {{ __('Cuentas') }}</h2>
      <a href="{{ route('whatsapp.accounts.create') }}"
         class="page-head-actions text-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
        + {{ __('Conectar cuenta') }}
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

      @if(session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-800">
          {{ session('status') }}
        </div>
      @endif

      <div class="bg-white shadow-sm sm:rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 border-b">
              <tr>
                <th class="px-5 py-3">{{ __('Nombre') }}</th>
                <th class="px-5 py-3">Phone Number ID</th>
                <th class="px-5 py-3">Pipeline</th>
                <th class="px-5 py-3">{{ __('Estado') }}</th>
                <th class="px-5 py-3">{{ __('Asistente IA') }}</th>
                <th class="px-5 py-3 text-right">{{ __('Acciones') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($accounts as $a)
                @php $ai = $a->aiAssistant; @endphp
                <tr class="hover:bg-gray-50 transition">
                  <td class="px-5 py-3 font-medium text-gray-900">{{ $a->name }}</td>
                  <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $a->phone_number_id }}</td>
                  <td class="px-5 py-3 text-gray-600">{{ $a->pipeline->name ?? '-' }}</td>
                  <td class="px-5 py-3">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                      {{ $a->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                      {{ $a->is_active ? __('Activo') : __('Inactivo') }}
                    </span>
                  </td>
                  <td class="px-5 py-3">
                    @if($ai && $ai->is_active)
                      <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium bg-violet-100 text-violet-700">
                        <span class="size-1.5 rounded-full bg-violet-500 animate-pulse inline-block"></span>
                        {{ $ai->model }}
                      </span>
                    @elseif($ai)
                      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-500">
                        {{ __('IA pausada') }}
                      </span>
                    @else
                      <span class="text-xs text-gray-400">—</span>
                    @endif
                  </td>
                  <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-3">
                      <a href="{{ route('whatsapp.ai.edit', $a) }}"
                         class="inline-flex items-center gap-1 text-xs text-violet-600 hover:text-violet-800 font-medium">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082"/>
                        </svg>
                        {{ __('IA') }}
                      </a>
                      <a href="{{ route('whatsapp.templates.index', $a) }}"
                         class="inline-flex items-center gap-1 text-xs text-green-600 hover:text-green-800 font-medium">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/></svg>
                        {{ __('Plantillas') }}
                      </a>
                      <a href="{{ route('whatsapp.accounts.edit', $a) }}"
                         class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">{{ __('Editar') }}</a>
                      <form class="inline" method="POST" action="{{ route('whatsapp.accounts.destroy', $a) }}"
                            onsubmit="return confirm('{{ __('¿Eliminar esta cuenta?') }}');">
                        @csrf
                        @method('DELETE')
                        <button class="text-xs text-red-500 hover:text-red-700 font-medium" type="submit">{{ __('Eliminar') }}</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td class="px-5 py-6 text-center text-gray-400" colspan="6">{{ __('No hay cuentas conectadas.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
          <a href="{{ route('whatsapp.inbox.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
            {{ __('Ir al Inbox') }} →
          </a>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
