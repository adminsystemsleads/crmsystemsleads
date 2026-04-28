
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             <div class="py-8">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow sm:rounded-lg p-6">

        @if (session('success'))
          <div class="mb-4 rounded-md bg-green-50 p-3 text-green-800 border border-green-200">
            {{ session('success') }}
          </div>
        @endif

        <form action="{{ route('perfil-unidad.update') }}" method="POST" class="space-y-5">
          @csrf

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Perfil</label>
            <select name="perfil" class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
              <option value="" @selected($perfil->perfil === null)>— Seleccionar —</option>
              <option value="propietario" @selected($perfil->perfil==='propietario')>Propietario</option>
              <option value="residente" @selected($perfil->perfil==='residente')>Residente</option>
            </select>
            @error('perfil') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
            <input type="text" name="unidad" value="{{ old('unidad', $perfil->unidad) }}"
                   class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Ej: Torre A - 502">
            @error('unidad') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo de contacto</label>
            <input type="email" name="correo" value="{{ old('correo', $perfil->correo) }}"
                   class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            @error('correo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
            <input type="text" name="telefono" value="{{ old('telefono', $perfil->telefono) }}"
                   class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            @error('telefono') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notas adicionales</label>
            <textarea name="notas" rows="4"
                      class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Información relevante para la administración">{{ old('notas', $perfil->notas) }}</textarea>
            @error('notas') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="pt-2">
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
              Guardar cambios
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
        </div>
    </div>
</x-app-layout>
