<x-app-layout>
<div class="max-w-3xl mx-auto px-4 py-8">

  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('invoices.index') }}" class="text-gray-400 hover:text-gray-600 transition">
      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl font-bold text-gray-900">Configuración de Facturación Electrónica</h1>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
  @endif

  <form method="POST" action="{{ route('invoice-config.update') }}" class="space-y-6">
    @csrf @method('PUT')

    {{-- Datos del emisor --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
      <h2 class="text-sm font-bold text-gray-800 border-b pb-2">Datos del Emisor (SUNAT)</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">RUC *</label>
          <input type="text" name="ruc" required maxlength="11" pattern="\d{11}"
                 value="{{ old('ruc', $config->ruc ?? '') }}"
                 placeholder="20123456789"
                 class="w-full rounded-lg border-gray-200 text-sm py-2 @error('ruc') border-red-400 @enderror">
          @error('ruc')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Razón Social *</label>
          <input type="text" name="razon_social" required maxlength="250"
                 value="{{ old('razon_social', $config->razon_social ?? '') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2 @error('razon_social') border-red-400 @enderror">
          @error('razon_social')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre Comercial</label>
          <input type="text" name="nombre_comercial" maxlength="250"
                 value="{{ old('nombre_comercial', $config->nombre_comercial ?? '') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">País</label>
          <input type="text" name="cod_pais" maxlength="2" value="{{ old('cod_pais', $config->cod_pais ?? 'PE') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Dirección *</label>
        <input type="text" name="direccion" required maxlength="250"
               value="{{ old('direccion', $config->direccion ?? '') }}"
               class="w-full rounded-lg border-gray-200 text-sm py-2">
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Ubigeo *</label>
          <input type="text" name="ubigeo" required maxlength="6"
                 value="{{ old('ubigeo', $config->ubigeo ?? '150101') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Departamento *</label>
          <input type="text" name="departamento" required maxlength="100"
                 value="{{ old('departamento', $config->departamento ?? 'LIMA') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Provincia *</label>
          <input type="text" name="provincia" required maxlength="100"
                 value="{{ old('provincia', $config->provincia ?? 'LIMA') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Distrito *</label>
          <input type="text" name="distrito" required maxlength="100"
                 value="{{ old('distrito', $config->distrito ?? 'LIMA') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Serie Factura</label>
          <input type="text" name="serie_factura" maxlength="4"
                 value="{{ old('serie_factura', $config->serie_factura ?? 'F001') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Serie Boleta</label>
          <input type="text" name="serie_boleta" maxlength="4"
                 value="{{ old('serie_boleta', $config->serie_boleta ?? 'B001') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
      </div>
    </div>

    {{-- Credenciales SUNAT --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
      <h2 class="text-sm font-bold text-gray-800 border-b pb-2">Credenciales SOL / Ambiente</h2>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Usuario SOL</label>
          <input type="text" name="sol_user" maxlength="50"
                 value="{{ old('sol_user', $config->sol_user ?? '') }}"
                 placeholder="MODDATOS"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Clave SOL</label>
          <input type="password" name="sol_password" maxlength="50"
                 value="{{ old('sol_password', $config->sol_password ?? '') }}"
                 class="w-full rounded-lg border-gray-200 text-sm py-2">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Ambiente</label>
          <select name="ambiente" class="w-full rounded-lg border-gray-200 text-sm py-2">
            <option value="beta"       {{ old('ambiente', $config->ambiente ?? 'beta') === 'beta'       ? 'selected' : '' }}>Beta (pruebas)</option>
            <option value="produccion" {{ old('ambiente', $config->ambiente ?? 'beta') === 'produccion' ? 'selected' : '' }}>Producción</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Certificado PEM</label>
        <textarea name="certificate_pem" rows="6"
                  placeholder="-----BEGIN CERTIFICATE-----&#10;...&#10;-----END CERTIFICATE-----"
                  class="w-full rounded-lg border-gray-200 text-xs font-mono py-2">{{ old('certificate_pem', $config->certificate_pem ?? '') }}</textarea>
        <p class="text-[11px] text-gray-400 mt-1">Pega aquí el contenido del archivo .pem de tu certificado digital.</p>
      </div>
    </div>

    <div class="flex justify-end">
      <button type="submit"
              class="px-6 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
        Guardar configuración
      </button>
    </div>
  </form>
</div>
</x-app-layout>
