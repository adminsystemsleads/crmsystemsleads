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
                <form action="{{ route('perfil-unidad.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5"
                      x-data="{
                          photoPreview: null,
                          removePhoto: false,
                          selectFile() { this.$refs.photoInput.click(); },
                          onFileChange(event) {
                              const file = event.target.files[0];
                              if (!file) return;
                              const reader = new FileReader();
                              reader.onload = (e) => { this.photoPreview = e.target.result; this.removePhoto = false; };
                              reader.readAsDataURL(file);
                          },
                          markForRemoval() {
                              this.removePhoto = true;
                              this.photoPreview = null;
                              this.$refs.photoInput.value = '';
                          }
                      }">
                    @csrf

                    {{-- Burbuja de foto de perfil --}}
                    <div class="flex flex-col items-center pb-6 mb-2 border-b border-gray-100">
                        <div class="relative">
                            {{-- Vista previa de nueva foto --}}
                            <img x-show="photoPreview" :src="photoPreview"
                                 class="rounded-full object-cover ring-4 ring-white shadow-md"
                                 style="width:112px; height:112px; display:none;"
                                 alt="Vista previa">

                            {{-- Placeholder con inicial si se marcó para eliminar --}}
                            <div x-show="removePhoto && !photoPreview"
                                 class="rounded-full flex items-center justify-center font-bold ring-4 ring-white shadow-md"
                                 style="width:112px; height:112px; background-color:#E8ECF2; color:#1E2E48; font-size:42px; display:none;">
                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                            </div>

                            {{-- Foto actual (la que se ve por defecto) --}}
                            <img x-show="!photoPreview && !removePhoto"
                                 src="{{ $user->profile_photo_url }}"
                                 class="rounded-full object-cover ring-4 ring-white shadow-md"
                                 style="width:112px; height:112px;"
                                 alt="{{ $user->name }}">

                            {{-- Botón cámara superpuesto (dentro del círculo, esquina inferior derecha) --}}
                            <button type="button" @click="selectFile()"
                                    class="absolute rounded-full p-2 shadow-lg transition ring-2 ring-white"
                                    style="background-color:#1E2E48; bottom:4px; right:4px;"
                                    onmouseover="this.style.backgroundColor='#152139'"
                                    onmouseout="this.style.backgroundColor='#1E2E48'"
                                    title="Cambiar foto">
                                <svg style="width:14px;height:14px;color:#fff;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Input oculto --}}
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                               x-ref="photoInput" @change="onFileChange($event)" class="hidden">

                        {{-- Flag para indicar al server que se borre la foto --}}
                        <input type="hidden" name="remove_photo" :value="removePhoto ? '1' : '0'">

                        {{-- Acciones de foto --}}
                        <div class="mt-5 flex items-center gap-3 text-xs">
                            <button type="button" @click="selectFile()"
                                    class="font-medium underline"
                                    style="color:#1E2E48;">
                                Cambiar foto
                            </button>
                            @if ($user->profile_photo_path)
                                <span class="text-gray-300">·</span>
                                <button type="button" @click="markForRemoval()"
                                        class="font-medium underline text-red-600 hover:text-red-700"
                                        x-show="!removePhoto && !photoPreview">
                                    Eliminar foto
                                </button>
                                <button type="button" @click="removePhoto = false"
                                        class="font-medium underline text-gray-500 hover:text-gray-700"
                                        x-show="removePhoto" style="display:none;">
                                    Cancelar
                                </button>
                            @endif
                        </div>

                        <p class="text-[11px] text-gray-400 mt-2">JPG, PNG, GIF o WEBP · máx. 2 MB</p>

                        @error('photo') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
                    </div>

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
