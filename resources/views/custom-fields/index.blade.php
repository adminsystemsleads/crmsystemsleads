<x-app-layout>
<div class="max-w-4xl mx-auto px-4 py-8">

  <h1 class="text-xl font-bold text-gray-900 mb-2">Campos personalizados</h1>
  <p class="text-sm text-gray-500 mb-5">Define campos extra para Contactos y Negociaciones (texto, número, fecha o lista).</p>

  {{-- Tabs entity --}}
  <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 text-sm mb-5">
    <a href="{{ route('custom-fields.index', ['entity' => 'contact']) }}"
       class="px-4 py-1.5 rounded-lg {{ $entity === 'contact' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
      Contactos
    </a>
    <a href="{{ route('custom-fields.index', ['entity' => 'deal']) }}"
       class="px-4 py-1.5 rounded-lg {{ $entity === 'deal' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
      Negociaciones
    </a>
  </div>

  @if(session('status'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- Lista de campos --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-gray-50 border-b border-gray-200">
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Nombre</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Tipo</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Opciones</th>
          <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600">Obligatorio</th>
          <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600">Activo</th>
          <th class="px-4 py-2.5"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @php
          $typeLabels = ['text' => 'Texto', 'number' => 'Número', 'date' => 'Fecha', 'select' => 'Lista'];
        @endphp
        @forelse($fields as $f)
          <tr>
            <form method="POST" action="{{ route('custom-fields.update', $f) }}" class="contents">
              @csrf @method('PUT')
              <td class="px-4 py-2">
                <input type="text" name="name" value="{{ $f->name }}" required maxlength="120"
                       class="w-full rounded-md border-gray-200 text-sm py-1.5">
                <p class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $f->slug }}</p>
              </td>
              <td class="px-4 py-2">
                <select name="field_type" class="rounded-md border-gray-200 text-sm py-1.5">
                  @foreach($typeLabels as $val => $lbl)
                    <option value="{{ $val }}" {{ $f->field_type === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                  @endforeach
                </select>
              </td>
              <td class="px-4 py-2 max-w-xs">
                <textarea name="options" rows="2" placeholder="Una opción por línea (solo para tipo Lista)"
                          class="w-full rounded-md border-gray-200 text-xs py-1.5">{{ is_array($f->options) ? implode("\n", $f->options) : '' }}</textarea>
              </td>
              <td class="px-4 py-2 text-center">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" name="is_required" value="1"
                       class="rounded border-gray-300 text-indigo-600"
                       {{ $f->is_required ? 'checked' : '' }}>
              </td>
              <td class="px-4 py-2 text-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       class="rounded border-gray-300 text-indigo-600"
                       {{ $f->is_active ? 'checked' : '' }}>
              </td>
              <td class="px-4 py-2 text-right whitespace-nowrap">
                <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">Guardar</button>
            </form>
                <form method="POST" action="{{ route('custom-fields.destroy', $f) }}" class="inline ml-2"
                      onsubmit="return confirm('¿Eliminar este campo? Se borrarán todos los valores guardados.');">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                </form>
              </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Aún no hay campos personalizados.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Crear nuevo --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
    <h2 class="text-sm font-bold text-gray-800 mb-3">Agregar nuevo campo</h2>
    <form method="POST" action="{{ route('custom-fields.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
      @csrf
      <input type="hidden" name="entity_type" value="{{ $entity }}">

      <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre *</label>
        <input type="text" name="name" required maxlength="120"
               placeholder="Ej: RUC, Industria, Fecha de seguimiento…"
               class="w-full rounded-lg border-gray-200 text-sm py-2">
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo *</label>
        <select name="field_type" id="newFieldType"
                onchange="document.getElementById('newOptionsBox').classList.toggle('hidden', this.value !== 'select')"
                class="w-full rounded-lg border-gray-200 text-sm py-2">
          <option value="text">Texto</option>
          <option value="number">Número</option>
          <option value="date">Fecha</option>
          <option value="select">Lista (desplegable)</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Obligatorio</label>
        <label class="inline-flex items-center gap-2 mt-2.5">
          <input type="checkbox" name="is_required" value="1" class="rounded border-gray-300 text-indigo-600">
          <span class="text-sm text-gray-700">Sí</span>
        </label>
      </div>

      <div id="newOptionsBox" class="md:col-span-4 hidden">
        <label class="block text-xs font-semibold text-gray-600 mb-1">Opciones (solo para Lista)</label>
        <textarea name="options" rows="3" placeholder="Una opción por línea, ej:&#10;Pequeña empresa&#10;Mediana empresa&#10;Grande"
                  class="w-full rounded-lg border-gray-200 text-sm py-2"></textarea>
      </div>

      <div class="md:col-span-4 flex justify-end">
        <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
          + Agregar campo
        </button>
      </div>
    </form>
  </div>
</div>
</x-app-layout>
