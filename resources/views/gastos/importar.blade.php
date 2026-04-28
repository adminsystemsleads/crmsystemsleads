{{-- resources/views/gastos/import.blade.php --}}
@php
  $team = Auth::user()->currentTeam;

  // TODAS las categorías del team (pueden ser muchas)
  $categorias = \App\Models\Categoria::where('team_id', $team->id)
      ->orderBy('nombre')
      ->pluck('nombre')
      ->toArray();

  // Para la vista, armamos una cadena con límite visual (ej. primeras 12 y el resto “+N más”)
  $maxPreview = 12;
  $previewCategorias = array_slice($categorias, 0, $maxPreview);
  $restantes = max(count($categorias) - $maxPreview, 0);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
           {{ __('Importar Gastos (Excel/CSV)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="py-10">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white p-6 rounded-lg shadow">

        {{-- Mensajes de éxito / error / advertencia --}}
        @if (session('success'))
          <div class="mb-4 rounded-md bg-green-50 border border-green-200 p-3 text-green-800">
            <strong>✅ {{ session('success') }}</strong>
          </div>
        @endif

        @if (session('warning'))
          <div class="mb-4 rounded-md bg-yellow-50 border border-yellow-200 p-3 text-yellow-800 whitespace-pre-line">
            ⚠️ <strong>Aviso:</strong> {!! session('warning') !!}
          </div>
        @endif

        @if (session('error'))
          <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-3 text-red-800">
            ❌ <strong>Error:</strong> {{ session('error') }}
          </div>
        @endif

        <form action="{{ route('gastos.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
          @csrf

          <div class="grid gap-2">
            <label class="text-sm font-medium text-gray-700">Archivo (.xlsx, .xls, .csv)</label>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                   class="block w-full border rounded px-3 py-2">
            @error('file')
              <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Instrucciones / Formato --}}
          <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-800">
            <h3 class="font-semibold mb-2">Instrucciones y formato del archivo</h3>

            <p class="mb-2">
              El archivo debe estar en formato “ancho”: cada <b>columna</b> (después de <code>unidad</code>, <code>mes</code>, <code>anio</code> y opcional <code>descripcion</code>)
              representa una <b>categoría</b> existente en el condominio, y cada <b>celda</b> contiene el <b>monto</b> para esa categoría.
            </p>

            <ul class="list-disc ml-5 space-y-1">
              <li>
                <b>Encabezados obligatorios:</b>
                <code class="bg-white/60 px-1.5 py-0.5 rounded border">unidad</code>,
                <code class="bg-white/60 px-1.5 py-0.5 rounded border">mes</code>,
                <code class="bg-white/60 px-1.5 py-0.5 rounded border">anio</code>.
                (Opcional: <code class="bg-white/60 px-1.5 py-0.5 rounded border">descripcion</code>)
              </li>
              <li>
                <b>unidad</b> puede ir vacía: en ese caso el gasto se registra como <b>general</b> (sin unidad). Si está vacía y hay columna
                <code>descripcion</code>, se guardará solo en gastos generales.
              </li>
              <li>
                <b>mes</b> acepta nombre (<i>Enero… Diciembre</i>) o número (<i>1…12</i>). En base de datos se guarda como número (1–12).
              </li>
              <li>
                <b>anio</b> debe ser numérico (por ejemplo, <i>2025</i>). En base de datos se guarda como entero.
              </li>
              <li>
                <b>Categorías:</b> agrega una columna por cada categoría ya creada en tu condominio. El nombre debe coincidir con el de la
                categoría; el sistema es tolerante a <i>mayúsculas/acentos/espacios</i> (se normalizan internamente).
              </li>
              <li>
                Las celdas vacías o con <i>0</i> no generan gasto. Las columnas que no coincidan con ninguna categoría existente se ignoran.
              </li>
              <li>
                Si <b>unidad</b> tiene un valor que no existe en tu condominio, la fila se omitirá y se reportará en los mensajes de advertencia.
              </li>
            </ul>

            {{-- Vista previa de encabezados esperados --}}
            <div class="mt-3">
              <p class="font-semibold mb-1">Ejemplo de encabezados (según tus categorías actuales):</p>
              <div class="rounded border bg-white p-3 overflow-x-auto">
                <code class="whitespace-nowrap">
                  unidad, mes, anio, descripcion
                  @foreach($previewCategorias as $c), {{ $c }}@endforeach
                  @if($restantes > 0), … (+{{ $restantes }} más) @endif
                </code>
              </div>
              <p class="text-xs text-gray-600 mt-2">
                Puedes incluir <b>todas</b> tus categorías como columnas (no hay límite práctico).
                El sistema tomará únicamente las columnas que coincidan con categorías existentes.
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
              Importar
            </button>
          </div>
        </form>

        {{-- Listado de categorías (colapsable simple) --}}
        <div x-data="{ open:false }" class="mt-8 text-sm">
          <button type="button"
                  @click="open = !open"
                  class="text-indigo-600 hover:text-indigo-700 font-medium">
            Ver todas las categorías del condominio ({{ count($categorias) }})
          </button>
          <div x-show="open" x-cloak class="mt-2 rounded border border-gray-200 p-3 bg-white">
            @if(count($categorias))
              <div class="flex flex-wrap gap-2">
                @foreach($categorias as $c)
                  <span class="inline-block text-gray-700 bg-gray-100 rounded px-2 py-1 border text-xs">{{ $c }}</span>
                @endforeach
              </div>
            @else
              <p class="text-gray-500">Aún no has creado categorías.</p>
            @endif
          </div>
        </div>

      </div>
    </div>
  </div>
        </div>
    </div>
</x-app-layout>



