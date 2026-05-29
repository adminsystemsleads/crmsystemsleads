<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-3 text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <form action="{{ route('perfil-unidad.update') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" name="nombre" required maxlength="255"
                               value="{{ old('nombre', $user->name) }}"
                               class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                               autocomplete="name">
                        @error('nombre') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="tel" name="telefono" maxlength="50"
                               value="{{ old('telefono', $perfil->telefono) }}"
                               class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                               autocomplete="tel">
                        @error('telefono') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Correo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                        <input type="email" name="correo" maxlength="120"
                               value="{{ old('correo', $perfil->correo) }}"
                               class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                               autocomplete="email">
                        @error('correo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notas adicionales --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas adicionales</label>
                        <textarea name="notas" rows="4" maxlength="2000"
                                  class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Información relevante adicional">{{ old('notas', $perfil->notas) }}</textarea>
                        @error('notas') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Rol asignado (solo lectura) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Rol asignado
                            <span class="text-xs font-normal text-gray-400 ml-1">(no editable)</span>
                        </label>
                        <div class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 flex items-center gap-2">
                            <svg style="width:16px;height:16px;color:#1E2E48;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span class="font-medium">{{ $rolDisplay }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Solo un administrador del equipo puede modificar tu rol desde
                            <strong>Perfiles → Configuración → Permisos de Acceso CRM</strong>.
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="px-5 py-2 text-white text-sm rounded-md transition"
                                style="background-color: #1E2E48;"
                                onmouseover="this.style.backgroundColor='#152139'"
                                onmouseout="this.style.backgroundColor='#1E2E48'">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
