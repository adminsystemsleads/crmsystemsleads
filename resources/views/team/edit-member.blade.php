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
                <form action="{{ route('team.perfiles.updateMember', $member) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

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
                           class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
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
