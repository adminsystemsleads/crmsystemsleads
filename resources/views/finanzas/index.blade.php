{{-- resources/views/finanzas/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Mis Finanzas') }}
    </h2>
  </x-slot>

  <div class="py-10">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg p-6">

        {{-- FILTROS: una sola fila --}}
        <form method="GET" action="{{ route('finanzas.index') }}"
      class="flex flex-wrap justify-center items-end gap-3 mb-6">

  {{-- Campo Mes --}}
  <div>
    <label class="block text-sm text-gray-600 mb-1">Mes</label>
    <select name="mes" class="border rounded px-3 py-1.5 text-sm w-40">
      @foreach (range(1,12) as $m)
        <option value="{{ $m }}" {{ (int)$mes === (int)$m ? 'selected' : '' }}>
          {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
        </option>
      @endforeach
    </select>
  </div>

  {{-- Campo Año --}}
  <div>
    <label class="block text-sm text-gray-600 mb-1">Año</label>
    <input type="number" name="anio"
           class="border rounded px-3 py-1.5 text-sm w-28"
           value="{{ $anio }}">
  </div>

  {{-- Botón --}}
  <div class="pt-5">
    <button class="bg-indigo-600 text-white px-5 py-1.5 rounded text-sm hover:bg-indigo-700">
      Filtrar
    </button>
  </div>
</form>





        {{-- TABLA: grande y de ancho completo --}}
        <div class="overflow-x-auto">
          <table class="w-full border text-sm md:text-base">
            <thead class="bg-gray-100">
              <tr>
                <th class="border px-4 py-3 text-left">Categoría</th>
                <th class="border px-4 py-3 text-right">Monto</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($gastos as $g)
                <tr class="hover:bg-gray-50">
                 
                  <td class="border px-4 py-3">{{ $g->categoria->nombre ?? '-' }}</td>
                 
                  <td class="border px-4 py-3 text-right">
                    S/ {{ number_format($g->monto_pagar, 2) }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-gray-500 py-6">
                    Sin pagos verificados en este periodo.
                  </td>
                </tr>
              @endforelse
            </tbody>

            <tfoot>
              <tr class="bg-gray-50 font-semibold">
                <td colspan="1" class="border px-4 py-3 text-right">Total</td>
                <td class="border px-4 py-3 text-right">
                  S/ {{ number_format($total, 2) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

      </div>
    </div>
  </div>
</x-app-layout>
