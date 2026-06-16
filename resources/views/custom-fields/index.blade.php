<x-app-layout>
<div class="max-w-3xl mx-auto px-4 py-8">

  {{-- Header --}}
  <div class="mb-6" :style="!$store.sidebar.open ? 'padding-left:3.75rem;' : ''">
    <h1 class="text-xl font-bold text-gray-900">Campos personalizados</h1>
    <p class="text-sm text-gray-500 mt-1">Define campos extra para Contactos y Negociaciones (texto, número, fecha o lista).</p>
  </div>

  {{-- Tabs entity --}}
  <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 text-sm mb-5 shadow-sm">
    <a href="{{ route('custom-fields.index', ['entity' => 'contact']) }}"
       class="px-4 py-1.5 rounded-lg transition {{ $entity === 'contact' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
      👥 Contactos
    </a>
    <a href="{{ route('custom-fields.index', ['entity' => 'deal']) }}"
       class="px-4 py-1.5 rounded-lg transition {{ $entity === 'deal' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
      💼 Negociaciones
    </a>
  </div>

  @if(session('status'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-2.5 text-sm text-green-700">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-700">
      <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @php
    $typeLabels = ['text' => 'Texto', 'number' => 'Número', 'date' => 'Fecha', 'select' => 'Lista'];
    $typeIcons  = ['text' => '📝', 'number' => '🔢', 'date' => '📅', 'select' => '📋'];
  @endphp

  {{-- Lista de campos como cards --}}
  <div class="space-y-3 mb-6">
    @forelse($fields as $f)
      <details class="bg-white rounded-xl shadow-sm border border-gray-200 group">
        <summary class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 rounded-xl select-none">
          <span class="text-lg shrink-0">{{ $typeIcons[$f->field_type] ?? '📝' }}</span>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-sm text-gray-900 truncate">
              {{ $f->name }}
              @if($f->is_required)
                <span class="ml-1 text-[10px] font-bold text-red-500 uppercase">Requerido</span>
              @endif
              @if(!$f->is_active)
                <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 text-gray-500 px-2 py-0.5 text-[10px] font-semibold">Inactivo</span>
              @endif
            </p>
            <p class="text-[11px] text-gray-400 font-mono truncate">{{ $f->slug }} · {{ $typeLabels[$f->field_type] ?? $f->field_type }}</p>
          </div>
          <svg class="size-4 text-gray-400 group-open:rotate-180 transition shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </summary>

        <div class="border-t border-gray-100 px-4 py-3">
          <form method="POST" action="{{ route('custom-fields.update', $f) }}" class="space-y-3">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre</label>
                <input type="text" name="name" value="{{ $f->name }}" required maxlength="120"
                       class="w-full rounded-lg border-gray-200 text-sm py-1.5">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo</label>
                <select name="field_type" class="w-full rounded-lg border-gray-200 text-sm py-1.5">
                  @foreach($typeLabels as $val => $lbl)
                    <option value="{{ $val }}" {{ $f->field_type === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">
                Opciones <span class="text-gray-400 font-normal">(solo para tipo Lista — una por línea)</span>
              </label>
              <textarea name="options" rows="3" placeholder="Pequeña empresa&#10;Mediana empresa&#10;Grande"
                        class="w-full rounded-lg border-gray-200 text-xs py-1.5 font-mono">{{ is_array($f->options) ? implode("\n", $f->options) : '' }}</textarea>
            </div>

            <div class="flex flex-wrap items-center gap-4">
              <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" name="is_required" value="1"
                       class="rounded border-gray-300 text-indigo-600"
                       {{ $f->is_required ? 'checked' : '' }}>
                Obligatorio
              </label>
              <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       class="rounded border-gray-300 text-indigo-600"
                       {{ $f->is_active ? 'checked' : '' }}>
                Activo
              </label>

              <div class="ml-auto flex items-center gap-2">
                <button type="submit" class="px-3 py-1.5 rounded-md bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">
                  Guardar
                </button>
          </form>
                <form method="POST" action="{{ route('custom-fields.destroy', $f) }}" class="inline"
                      onsubmit="return confirm('¿Eliminar este campo? Se borrarán todos los valores guardados.');">
                  @csrf @method('DELETE')
                  <button type="submit" class="px-3 py-1.5 rounded-md border border-red-200 text-red-500 text-xs font-medium hover:bg-red-50">
                    Eliminar
                  </button>
                </form>
              </div>
            </div>
        </div>
      </details>
    @empty
      <div class="bg-white rounded-xl shadow-sm border border-dashed border-gray-200 py-10 text-center">
        <p class="text-sm text-gray-400">Aún no hay campos personalizados para
          <strong>{{ $entity === 'contact' ? 'contactos' : 'negociaciones' }}</strong>.</p>
        <p class="text-xs text-gray-400 mt-1">Crea el primero con el formulario de abajo ⬇</p>
      </div>
    @endforelse
  </div>

  {{-- Crear nuevo --}}
  <div class="bg-white rounded-xl shadow-sm border border-indigo-100 ring-1 ring-indigo-50 p-5">
    <h2 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
      <svg class="size-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Agregar nuevo campo
    </h2>
    <form method="POST" action="{{ route('custom-fields.store') }}" class="space-y-3">
      @csrf
      <input type="hidden" name="entity_type" value="{{ $entity }}">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre *</label>
          <input type="text" name="name" required maxlength="120"
                 placeholder="Ej: RUC, Industria, Fecha de seguimiento"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo *</label>
          <select name="field_type" id="newFieldType"
                  onchange="document.getElementById('newOptionsBox').classList.toggle('hidden', this.value !== 'select')"
                  class="w-full rounded-lg border-gray-200 text-sm py-2">
            <option value="text">📝 Texto</option>
            <option value="number">🔢 Número</option>
            <option value="date">📅 Fecha</option>
            <option value="select">📋 Lista (desplegable)</option>
          </select>
        </div>
      </div>

      <div id="newOptionsBox" class="hidden">
        <label class="block text-xs font-semibold text-gray-600 mb-1">Opciones</label>
        <textarea name="options" rows="3"
                  placeholder="Una opción por línea, ej:&#10;Pequeña empresa&#10;Mediana empresa&#10;Grande"
                  class="w-full rounded-lg border-gray-200 text-xs py-1.5 font-mono"></textarea>
      </div>

      <div class="flex items-center justify-between pt-1">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" name="is_required" value="1" class="rounded border-gray-300 text-indigo-600">
          Obligatorio
        </label>
        <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
          + Agregar campo
        </button>
      </div>
    </form>
  </div>
</div>
</x-app-layout>
