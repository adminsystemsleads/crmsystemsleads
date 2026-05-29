<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Configurar Roles y Permisos de CRM
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Define qué puede hacer cada miembro del equipo en el CRM.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('team.perfiles.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700">← Volver a Perfiles</a>
                <a href="{{ route('team.crm-roles.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-white text-sm rounded-md transition"
                   style="background-color: #1E2E48;"
                   onmouseover="this.style.backgroundColor='#152139'"
                   onmouseout="this.style.backgroundColor='#1E2E48'">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo rol
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 px-4 py-2 bg-green-50 text-green-700 text-sm rounded-md border border-green-100">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 px-4 py-2 bg-red-50 text-red-700 text-sm rounded-md border border-red-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permisos</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($roles as $role)
                            <tr>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900">{{ $role->name }}</span>
                                        @if ($role->is_default)
                                            <span class="text-[10px] uppercase tracking-wider font-semibold px-2 py-0.5 rounded-full"
                                                  style="background-color:#FBF7EC; color:#A08544; border:1px solid rgba(201,169,97,.35);">
                                                Sistema
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $role->description ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                          style="background-color:#E8ECF2; color:#1E2E48;">
                                        {{ $role->permission_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right space-x-3">
                                    <a href="{{ route('team.crm-roles.edit', $role) }}"
                                       class="text-blue-600 hover:text-blue-900">Editar</a>
                                    @unless ($role->is_default)
                                        <form action="{{ route('team.crm-roles.destroy', $role) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('¿Eliminar el rol &quot;{{ $role->name }}&quot;? Esta acción no se puede deshacer.');">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">
                                    No hay roles creados aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-500 mt-3">
                💡 El rol <strong>Administrador</strong> es creado por el sistema y siempre conserva todos los permisos. Puedes editarle la descripción pero no quitarle accesos ni eliminarlo.
            </p>
        </div>
    </div>
</x-app-layout>
