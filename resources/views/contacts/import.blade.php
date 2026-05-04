<x-app-layout>
<div class="max-w-3xl mx-auto px-4 py-8">

  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('contacts.index') }}" class="text-gray-400 hover:text-gray-600 transition">
      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl font-bold text-gray-900">Importar contactos desde CSV</h1>
  </div>

  @if(session('status'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
  @endif

  {{-- Instrucciones --}}
  <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 mb-6">
    <h2 class="text-sm font-bold text-indigo-900 mb-2">Cómo importar</h2>
    <ol class="text-sm text-indigo-900 space-y-1.5 list-decimal list-inside">
      <li>Descarga la <strong>plantilla CSV</strong> con ejemplos ya cargados.</li>
      <li>Llénala con tus contactos en Excel o Google Sheets.</li>
      <li>Guárdala como <strong>CSV (delimitado por comas)</strong>.</li>
      <li>Súbela en el formulario de abajo.</li>
    </ol>
    <a href="{{ route('contacts.import.template') }}"
       class="inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
      <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
      </svg>
      Descargar plantilla CSV
    </a>
  </div>

  {{-- Tabla con ejemplo de columnas --}}
  <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
    <h2 class="text-sm font-bold text-gray-800 mb-3">Columnas del CSV</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-xs">
        <thead>
          <tr class="bg-gray-50 border-b">
            <th class="px-3 py-2 text-left font-semibold text-gray-600">Columna</th>
            <th class="px-3 py-2 text-left font-semibold text-gray-600">Obligatorio</th>
            <th class="px-3 py-2 text-left font-semibold text-gray-600">Ejemplo</th>
            <th class="px-3 py-2 text-left font-semibold text-gray-600">Notas</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr><td class="px-3 py-2 font-mono">first_name</td><td class="px-3 py-2"><span class="text-red-600 font-semibold">Sí</span></td><td class="px-3 py-2">Juan</td><td class="px-3 py-2 text-gray-500">Nombre</td></tr>
          <tr><td class="px-3 py-2 font-mono">last_name</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">Pérez</td><td class="px-3 py-2 text-gray-500">Apellido</td></tr>
          <tr><td class="px-3 py-2 font-mono">email</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">juan@empresa.com</td><td class="px-3 py-2 text-gray-500">Correo válido</td></tr>
          <tr><td class="px-3 py-2 font-mono">phone</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">+51987654321</td><td class="px-3 py-2 text-gray-500">Con código país recomendado</td></tr>
          <tr><td class="px-3 py-2 font-mono">company</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">Empresa SAC</td><td class="px-3 py-2 text-gray-500"></td></tr>
          <tr><td class="px-3 py-2 font-mono">position</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">Gerente</td><td class="px-3 py-2 text-gray-500"></td></tr>
          <tr><td class="px-3 py-2 font-mono">tipo_doc</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">1, 6, 4</td><td class="px-3 py-2 text-gray-500">1=DNI, 6=RUC, 4=CE</td></tr>
          <tr><td class="px-3 py-2 font-mono">num_doc</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">20123456789</td><td class="px-3 py-2 text-gray-500">Para facturación</td></tr>
          <tr><td class="px-3 py-2 font-mono">status</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">nuevo, activo, cliente</td><td class="px-3 py-2 text-gray-500">Por defecto: nuevo</td></tr>
          <tr><td class="px-3 py-2 font-mono">source</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">whatsapp, web, manual</td><td class="px-3 py-2 text-gray-500">Origen del contacto</td></tr>
          <tr><td class="px-3 py-2 font-mono">notes</td><td class="px-3 py-2 text-gray-400">No</td><td class="px-3 py-2">Cliente referido por…</td><td class="px-3 py-2 text-gray-500">Texto libre</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- Formulario de subida --}}
  <form method="POST" action="{{ route('contacts.import.store') }}" enctype="multipart/form-data"
        class="bg-white border border-gray-200 rounded-xl p-5">
    @csrf
    <label class="block text-sm font-semibold text-gray-700 mb-2">Archivo CSV</label>
    <input type="file" name="csv_file" accept=".csv,text/csv" required
           class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                  file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
    @error('csv_file')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror

    <div class="flex justify-end gap-2 mt-5">
      <a href="{{ route('contacts.index') }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">Cancelar</a>
      <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
        Importar contactos
      </button>
    </div>
  </form>
</div>
</x-app-layout>
