<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Permisos del embudo – {{ $pipeline->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <a href="{{ route('pipelines.kanban', $pipeline) }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                ← Volver al Kanban
            </a>

            @if(session('status'))
                <div class="mt-4 mb-4 text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mt-4 mb-4 text-sm text-red-600">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mt-4">
                <p class="text-sm text-gray-600 mb-6">
                    Define qué usuarios del equipo pueden ver, editar, eliminar negociaciones y configurar este embudo.
                    El propietario del equipo siempre tiene todos los permisos por defecto.
                </p>

                {{-- ✅ IMPORTANTE: EL BOTÓN DEBE ESTAR DENTRO DEL FORM --}}
                <form method="POST" action="{{ route('pipelines.permissions.update', $pipeline) }}">
                    @csrf
                    @method('PUT')

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 pr-4">Usuario</th>
                                    <th class="text-center py-2 px-2">Ver</th>
                                    <th class="text-center py-2 px-2">Editar</th>
                                    <th class="text-center py-2 px-2">Eliminar</th>
                                    <th class="text-center py-2 px-2">Configurar embudo</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($teamMembers as $member)
                                    @php
                                        $row = $permissions[$member->id] ?? null;

                                        $isOwner = ((int)$team->owner_id === (int)$member->id);

                                        // Admin Jetstream (si existe role en pivot)
                                        $isAdmin = false;
                                        try {
                                            $isAdmin = ($team->users()
                                                ->where('users.id', $member->id)
                                                ->wherePivot('role','admin')
                                                ->exists());
                                        } catch (\Throwable $e) {
                                            $isAdmin = false;
                                        }

                                        $locked = $isOwner || $isAdmin;
                                    @endphp

                                    <tr class="border-b">
                                        <td class="py-3 pr-4">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-800">{{ $member->name }}</span>

                                                @if($isOwner)
                                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                                        Owner
                                                    </span>
                                                @elseif($isAdmin)
                                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                                        Admin
                                                    </span>
                                                @endif
                                            </div>
                                            @if($locked)
                                                <div class="text-[11px] text-gray-500 mt-1">
                                                    (Siempre tiene permisos completos)
                                                </div>
                                            @endif
                                        </td>

                                        {{-- VER --}}
                                        <td class="text-center py-3 px-2">
                                            <input type="checkbox"
                                                name="permissions[{{ $member->id }}][can_view]"
                                                @checked($locked ? true : (bool)($row->can_view ?? false))
                                                @disabled($locked)
                                            >
                                        </td>

                                        {{-- EDITAR --}}
                                        <td class="text-center py-3 px-2">
                                            <input type="checkbox"
                                                name="permissions[{{ $member->id }}][can_edit]"
                                                @checked($locked ? true : (bool)($row->can_edit ?? false))
                                                @disabled($locked)
                                            >
                                        </td>

                                        {{-- ELIMINAR --}}
                                        <td class="text-center py-3 px-2">
                                            <input type="checkbox"
                                                name="permissions[{{ $member->id }}][can_delete]"
                                                @checked($locked ? true : (bool)($row->can_delete ?? false))
                                                @disabled($locked)
                                            >
                                        </td>

                                        {{-- CONFIGURAR --}}
                                        <td class="text-center py-3 px-2">
                                            <input type="checkbox"
                                                name="permissions[{{ $member->id }}][can_configure]"
                                                @checked($locked ? true : (bool)($row->can_configure ?? false))
                                                @disabled($locked)
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Guardar permisos
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
