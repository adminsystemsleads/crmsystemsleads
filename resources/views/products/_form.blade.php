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
<div class="flex items-center gap-2">
  <input type="checkbox" name="is_active" id="chkActive" value="1" checked
         class="rounded border-gray-300 text-indigo-600">
  <label for="chkActive" class="text-sm text-gray-700">Producto activo</label>
</div>
