<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Formularios') }}</h2>
  </x-slot>

  <div class="max-w-5xl mx-auto px-4 py-8">

    <div class="flex flex-wrap items-center justify-between gap-2 mb-6">
      <p class="text-sm text-gray-500">{{ __('Crea formularios para captar clientes y generar negociaciones automáticamente.') }}</p>
      <a href="{{ route('formularios.create') }}"
         class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('Nuevo formulario') }}
      </a>
    </div>

    @if(session('success'))
      <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
      </div>
    @endif

    @if($forms->isEmpty())
      <div class="rounded-xl bg-white border border-gray-200 shadow-sm px-6 py-16 text-center">
        <div class="mx-auto mb-4 size-12 rounded-full bg-indigo-50 flex items-center justify-center">
          <svg class="size-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-6-8h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/>
          </svg>
        </div>
        <p class="text-gray-600 font-medium">{{ __('Aún no tienes formularios') }}</p>
        <p class="text-gray-400 text-sm mt-1">{{ __('Crea tu primer formulario para empezar a captar clientes.') }}</p>
      </div>
    @else
      <div class="space-y-3">
        @foreach($forms as $form)
          <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-4 flex flex-wrap items-center gap-4">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-800 truncate">{{ $form->name }}</span>
                @if($form->is_active)
                  <span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-[10px] font-semibold">{{ __('Activo') }}</span>
                @else
                  <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-500 px-2 py-0.5 text-[10px] font-semibold">{{ __('Inactivo') }}</span>
                @endif
              </div>
              <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                <span>{{ $form->submissions_count }} {{ __('envíos') }}</span>
                <a href="{{ $form->public_url }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 text-indigo-600 hover:underline truncate max-w-[16rem]">
                  <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                  {{ $form->public_url }}
                </a>
              </div>
            </div>

            <div class="flex items-center gap-1.5">
              <button type="button"
                      onclick="navigator.clipboard.writeText('{{ $form->public_url }}');this.dataset.done='1';this.querySelector('span').textContent='{{ __('Copiado') }}';"
                      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>{{ __('Copiar link') }}</span>
              </button>
              <a href="{{ route('formularios.submissions', $form) }}"
                 class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                {{ __('Envíos') }}
              </a>
              <a href="{{ route('formularios.edit', $form) }}"
                 class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
                {{ __('Editar') }}
              </a>
              <form method="POST" action="{{ route('formularios.destroy', $form) }}"
                    onsubmit="return confirm('{{ __('¿Eliminar este formulario?') }}')">
                @csrf @method('DELETE')
                <button type="submit" title="{{ __('Eliminar') }}"
                        class="inline-flex items-center justify-center size-8 rounded-lg border border-gray-200 text-gray-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition">
                  <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </form>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</x-app-layout>
