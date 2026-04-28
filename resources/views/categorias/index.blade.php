<x-app-layout>
  <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ __('Administrar Categoria de Pagos') }}
      </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="max-w-3xl mx-auto p-6">

          <h1 class="text-2xl font-semibold mb-6">Categorías de Pago</h1>

          @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-green-800 border border-green-200">
              {{ session('success') }}
            </div>
          @endif

          @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-3 text-red-800 border border-red-200">
              {{ session('error') }}
            </div>
          @endif

          {{-- Form crear categoría (solo admin) --}}
          @if (Auth::user()->hasTeamRole(Auth::user()->currentTeam, 'admin'))
            <form action="{{ route('categorias.store') }}" method="POST"
                  class="mb-6 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
              @csrf

              <input type="text" name="nombre"
                     placeholder="Nueva categoría (ej. Agua, Luz, Mantenimiento)"
                     class="sm:col-span-7 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                     value="{{ old('nombre') }}" required>

              <select name="tipo"
                      class="sm:col-span-3 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach (['GASTOS FIJOS','GASTOS VARIABLES','INGRESOS','OTROS'] as $t)
                  <option value="{{ $t }}" @selected(old('tipo')===$t)>{{ $t }}</option>
                @endforeach
              </select>

              <button type="submit"
                      class="sm:col-span-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                Agregar
              </button>
            </form>

            @error('nombre')
              <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
            @enderror
            @error('tipo')
              <p class="text-sm text-red-600 mb-4">{{ $message }}</p>
            @enderror
          @endif

          {{-- Lista de categorías --}}
          @php
            $badge = [
              'INGRESOS'          => 'bg-emerald-100 text-emerald-700',
              'GASTOS FIJOS'      => 'bg-indigo-100 text-indigo-700',
              'GASTOS VARIABLES'  => 'bg-amber-100 text-amber-800',
              'OTROS'             => 'bg-gray-100 text-gray-700',
            ];
          @endphp

          <div class="bg-white shadow rounded-lg divide-y">
            @forelse ($categorias as $categoria)
              <div class="flex items-center justify-between p-4">
                <div class="min-w-0">
                  <div class="font-medium text-gray-800 flex items-center gap-2">
                    <span class="truncate">{{ $categoria->nombre }}</span>
                    <span class="text-xs px-2 py-0.5 rounded {{ $badge[$categoria->tipo] ?? 'bg-gray-100 text-gray-700' }}">
                      {{ $categoria->tipo }}
                    </span>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    Usos en gastos: <span class="font-semibold">{{ $categoria->gastos_count }}</span>
                  </div>
                </div>

                @if (Auth::user()->hasTeamRole(Auth::user()->currentTeam, 'admin'))
                  <form action="{{ route('categorias.destroy', $categoria) }}" method="POST"
                        onsubmit="return confirm('⚠️ Esta acción eliminará la categoría «{{ $categoria->nombre }}».{{ $categoria->gastos_count > 0 ? ' Actualmente está vinculada a ' . $categoria->gastos_count . ' gasto(s). Deberás reasignarlos o eliminarlos antes.' : '' }}\n\n¿Deseas continuar?');">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-600 hover:text-red-700 text-sm">Eliminar</button>
                  </form>
                @endif
              </div>
            @empty
              <div class="p-6 text-gray-500">Aún no hay categorías.</div>
            @endforelse
          </div>

          <div class="mt-4">
            {{ $categorias->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
