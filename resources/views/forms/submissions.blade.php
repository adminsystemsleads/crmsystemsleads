<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center gap-2">
      <a href="{{ route('formularios.index') }}" class="text-gray-400 hover:text-gray-600">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </a>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Envíos') }} · {{ $form->name }}</h2>
    </div>
  </x-slot>

  <div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-4">
      <p class="text-sm text-gray-500">{{ $submissions->total() }} {{ __('envíos recibidos') }}</p>
      <a href="{{ route('formularios.edit', $form) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Editar formulario') }}</a>
    </div>

    @if($submissions->isEmpty())
      <div class="rounded-xl bg-white border border-gray-200 shadow-sm px-6 py-16 text-center text-gray-400 text-sm">
        {{ __('Todavía no hay envíos de este formulario.') }}
      </div>
    @else
      <div class="rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
              <th class="text-left font-semibold px-4 py-3">{{ __('Fecha') }}</th>
              <th class="text-left font-semibold px-4 py-3">{{ __('Contacto') }}</th>
              <th class="text-left font-semibold px-4 py-3">{{ __('Negociación') }}</th>
              <th class="text-left font-semibold px-4 py-3">{{ __('Datos') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach($submissions as $s)
              <tr class="hover:bg-gray-50 align-top">
                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $s->created_at?->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3">
                  @if($s->contact)
                    <a href="{{ route('contacts.edit', $s->contact) }}" class="text-indigo-600 hover:underline font-medium">{{ $s->contact->name }}</a>
                    <div class="text-xs text-gray-400">{{ $s->contact->phone }} {{ $s->contact->email ? '· '.$s->contact->email : '' }}</div>
                  @else
                    <span class="text-gray-400">—</span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  @if($s->deal)
                    <span class="text-gray-700">{{ $s->deal->title }}</span>
                  @else
                    <span class="text-gray-400">—</span>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <details>
                    <summary class="text-xs text-indigo-600 cursor-pointer">{{ __('Ver datos') }}</summary>
                    <pre class="mt-1 text-[11px] text-gray-600 bg-gray-50 rounded p-2 overflow-x-auto max-w-md">{{ json_encode($s->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                  </details>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-4">{{ $submissions->links() }}</div>
    @endif
  </div>
</x-app-layout>
