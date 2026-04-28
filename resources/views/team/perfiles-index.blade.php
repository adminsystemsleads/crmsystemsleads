<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Perfiles de Miembros del Condominio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="py-8">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow sm:rounded-lg">
        <div class="p-4 border-b">
          <p class="text-sm text-gray-600">Miembros del team actual y sus perfiles.</p>
        </div>

        <div class="divide-y">
          @forelse ($perfiles as $p)
            <div class="p-4 flex items-center justify-between">
              <div>
                <div class="font-medium text-gray-800">{{ $p->user->name }}</div>
                <div class="text-sm text-gray-500">
                  Perfil: <strong>{{ $p->perfil ?? '—' }}</strong> ·
                  Unidad: <strong>{{ $p->unidad ?? '—' }}</strong> ·
                  Correo: <strong>{{ $p->correo ?? '—' }}</strong> ·
                  Tel: <strong>{{ $p->telefono ?? '—' }}</strong>
                </div>
                @if ($p->notas)
                  <div class="text-xs text-gray-500 mt-1">Notas: {{ $p->notas }}</div>
                @endif
              </div>
              <a href="{{ route('perfil-unidad.edit') }}"
                 class="text-indigo-600 hover:text-indigo-700 text-sm">Ver/Editar</a>
            </div>
          @empty
            <div class="p-6 text-gray-500">No hay perfiles aún.</div>
          @endforelse
        </div>

        <div class="p-4">
          {{ $perfiles->links() }}
        </div>
      </div>
    </div>
  </div>
        </div>
    </div>
</x-app-layout>


