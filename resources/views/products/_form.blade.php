@php
  // El id sufijo evita colisiones cuando este partial se incluye 2 veces (nuevo + editar)
  $sfx = isset($edit) ? 'edit' : 'new';
@endphp

<div>
  <label class="block text-xs font-semibold text-gray-600 mb-1">Imagen (opcional)</label>
  @isset($edit)
    <div id="currentImageWrap-edit" class="mb-2"></div>
  @endisset
  <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
         class="block w-full text-xs text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0
                file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
  <p class="text-[10px] text-gray-400 mt-1">JPG, PNG o WebP. Máx 2MB.</p>
  @isset($edit)
    <label class="inline-flex items-center gap-2 mt-2 text-xs text-gray-600">
      <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-red-500">
      Eliminar imagen actual
    </label>
  @endisset
</div>

<div>
  <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre *</label>
  <input type="text" name="name" required maxlength="255" value="{{ old('name') }}"
         class="w-full rounded-lg border-gray-200 text-sm py-2">
</div>

<div>
  <label class="block text-xs font-semibold text-gray-600 mb-1">Descripción</label>
  <textarea name="description" rows="2" maxlength="1000"
            class="w-full rounded-lg border-gray-200 text-sm py-2">{{ old('description') }}</textarea>
</div>

<div class="grid grid-cols-2 gap-3">
  <div>
    <label class="block text-xs font-semibold text-gray-600 mb-1">Unidad</label>
    <input type="text" name="unit" value="{{ old('unit', 'unidad') }}" maxlength="50"
           class="w-full rounded-lg border-gray-200 text-sm py-2">
  </div>
  <div>
    <label class="block text-xs font-semibold text-gray-600 mb-1">Moneda</label>
    <select name="currency" class="w-full rounded-lg border-gray-200 text-sm py-2">
      <option value="PEN" {{ old('currency','PEN')==='PEN'?'selected':'' }}>PEN</option>
      <option value="USD" {{ old('currency')==='USD'?'selected':'' }}>USD</option>
      <option value="EUR" {{ old('currency')==='EUR'?'selected':'' }}>EUR</option>
    </select>
  </div>
</div>

<div>
  <label class="block text-xs font-semibold text-gray-600 mb-1">Precio *</label>
  <input type="number" name="price" required min="0" step="0.01" value="{{ old('price', 0) }}"
         class="w-full rounded-lg border-gray-200 text-sm py-2">
</div>

{{-- Hidden + checkbox: garantiza que SIEMPRE viaje is_active (1 ó 0) --}}
<div class="flex items-center gap-2">
  <input type="hidden" name="is_active" value="0">
  <input type="checkbox" name="is_active" id="chkActive-{{ $sfx }}" value="1" checked
         class="rounded border-gray-300 text-indigo-600">
  <label for="chkActive-{{ $sfx }}" class="text-sm text-gray-700">Producto activo</label>
</div>
