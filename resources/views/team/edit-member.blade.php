<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar usuario: {{ $member->name }}
            </h2>
            <a href="{{ route('team.perfiles.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                ← Volver al listado
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 px-4 py-2 bg-red-50 text-red-700 text-sm rounded-md border border-red-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <form action="{{ route('team.perfiles.updateMember', $member) }}" method="POST" enctype="multipart/form-data" class="space-y-5"
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
                    @method('PUT')

                    {{-- Burbuja de foto del miembro --}}
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
                                {{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}
                            </div>

                            {{-- Foto actual --}}
                            <img x-show="!photoPreview && !removePhoto"
                                 src="{{ $member->profile_photo_url }}"
                                 class="rounded-full object-cover ring-4 ring-white shadow-md"
                                 style="width:112px; height:112px;"
                                 alt="{{ $member->name }}">

                            {{-- Botón cámara superpuesto --}}
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

                        <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                               x-ref="photoInput" @change="onFileChange($event)" class="hidden">

                        <input type="hidden" name="remove_photo" :value="removePhoto ? '1' : '0'">

                        <div class="mt-5 flex items-center gap-3 text-xs">
                            <button type="button" @click="selectFile()"
                                    class="font-medium underline"
                                    style="color:#1E2E48;">
                                Cambiar foto
                            </button>
                            @if ($member->profile_photo_path)
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
                               value="{{ old('nombre', $member->name) }}"
                               class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="tel" name="telefono" maxlength="50"
                               value="{{ old('telefono', $profile->telefono) }}"
                               class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Correo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                        <input type="email" name="correo" maxlength="120"
                               value="{{ old('correo', $profile->correo ?? $member->email) }}"
                               class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Notas adicionales --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas adicionales</label>
                        <textarea name="notas" rows="4" maxlength="2000"
                                  class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Información relevante">{{ old('notas', $profile->notas) }}</textarea>
                    </div>

                    {{-- Rol asignado --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Rol asignado
                            @if ($onlyOneUser || $isEditingSelf)
                                <span class="text-xs font-normal text-gray-400 ml-1">(no editable)</span>
                            @endif
                        </label>

                        @if ($onlyOneUser)
                            {{-- Solo 1 usuario en el team: bloquear cambio para evitar dejar el equipo sin admin --}}
                            <div class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <strong>{{ $profile->crmRole?->name ?? 'Administrador (por defecto)' }}</strong>
                            </div>
                            <p class="text-xs mt-1"
                               style="color:#A08544;">
                                ⚠️ El equipo solo tiene 1 usuario. No se puede modificar su rol porque dejaría al sistema sin administrador.
                                Invita a otro usuario al equipo para poder gestionar roles.
                            </p>
                        @elseif ($isEditingSelf)
                            {{-- Admin editando su propio perfil: tampoco puede cambiar su rol --}}
                            <div class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <strong>{{ $profile->crmRole?->name ?? 'Administrador (por defecto)' }}</strong>
                            </div>
                            <p class="text-xs mt-1"
                               style="color:#A08544;">
                                ⚠️ No puedes cambiar tu propio rol. Pídele a otro administrador del equipo que lo modifique.
                            </p>
                        @else
                            <select name="crm_role_id"
                                    class="w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">— Sin rol asignado —</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                            @selected(old('crm_role_id', $profile->crm_role_id) == $role->id)>
                                        {{ $role->name }}@if ($role->is_default) (sistema) @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                Para crear o modificar roles ve a
                                <a href="{{ route('team.crm-roles.index') }}" style="color:#1E2E48;" class="underline">Configurar Roles y Permisos de CRM</a>.
                            </p>
                        @endif
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <a href="{{ route('team.perfiles.index') }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition bg-white"
                           style="color:#dc2626; border:1.5px solid #dc2626;"
                           onmouseover="this.style.backgroundColor='#fef2f2'"
                           onmouseout="this.style.backgroundColor='#fff'">Cancelar</a>
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
