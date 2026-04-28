{{-- resources/views/transparencia_ia/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transpariencia IA</h2>
  </x-slot>

  <div class="py-10">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('transparencia.ia.ask') }}" method="POST" class="flex gap-3 mb-4">
          @csrf
          <input type="text" name="q" value="{{ old('q', $q) }}" placeholder="Ej: ¿Cuánto se gastó en Luz en 2025 por unidad?"
                 class="flex-1 border rounded px-3 py-2">
          <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Consultar</button>
        </form>

        @error('q') <div class="mb-3 text-red-600 text-sm">{{ $message }}</div> @enderror

        @isset($answer)
          <div class="mb-4 p-3 rounded bg-indigo-50 border border-indigo-200 text-indigo-900">
            <strong>Respuesta IA:</strong> {{ $answer }}
          </div>
        @endisset

        @isset($sql)
          <div class="mb-4 text-xs text-gray-600">
            <span class="font-semibold">SQL ejecutado:</span>
            <pre class="mt-1 p-2 bg-gray-50 border rounded overflow-x-auto">{{ $sql }}</pre>
          </div>
        @endisset

        @if(!empty($rows))
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
              <thead class="bg-gray-100 text-gray-700">
                <tr>
                  @foreach(array_keys((array)$rows[0]) as $col)
                    <th class="border px-3 py-2 text-left">{{ $col }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
              @foreach($rows as $r)
                <tr>
                  @foreach((array)$r as $v)
                    <td class="border px-3 py-1">{{ is_bool($v) ? ($v ? 'Sí' : 'No') : $v }}</td>
                  @endforeach
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        @elseif(isset($q))
          <p class="text-gray-500">Sin resultados.</p>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
