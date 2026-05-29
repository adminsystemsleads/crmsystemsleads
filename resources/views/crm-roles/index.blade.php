<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                {{-- Icono badge --}}
                <div class="flex items-center justify-center rounded-xl"
                     style="width:42px; height:42px; background-color:#E8ECF2;">
                    <svg style="width:22px;height:22px;color:#1E2E48;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                        Configurar Roles y Permisos de CRM
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Define qué puede hacer cada miembro del equipo en el CRM.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('team.perfiles.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition px-3 py-2 rounded-md hover:bg-gray-100">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Volver a Perfiles
                </a>
                <a href="{{ route('team.crm-roles.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-white text-sm font-medium rounded-md transition shadow-sm"
                   style="background-color: #1E2E48;"
                   onmouseover="this.style.backgroundColor='#152139'"
                   onmouseout="this.style.backgroundColor='#1E2E48'">
                    <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo rol
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Flash messages --}}
            @if (session('status'))
                <div class="flex items-start gap-3 px-4 py-3 bg-green-50 text-green-800 text-sm rounded-lg border border-green-200">
                    <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="flex items-start gap-3 px-4 py-3 bg-red-50 text-red-800 text-sm rounded-lg border border-red-200">
                    <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Stats summary --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-white rounded-lg p-4 border border-gray-100">
                    <p class="text-[10px] uppercase tracking-wider font-semibold text-gray-400">Total de roles</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $roles->count() }}</p>
                </div>
                <div class="bg-white rounded-lg p-4 border border-gray-100">
                    <p class="text-[10px] uppercase tracking-wider font-semibold text-gray-400">Roles del sistema</p>
                    <p class="text-2xl font-bold mt-1" style="color:#A08544;">{{ $roles->where('is_default', true)->count() }}</p>
                </div>
                <div class="bg-white rounded-lg p-4 border border-gray-100">
                    <p class="text-[10px] uppercase tracking-wider font-semibold text-gray-400">Roles personalizados</p>
                    <p class="text-2xl font-bold mt-1" style="color:#1E2E48;">{{ $roles->where('is_default', false)->count() }}</p>
                </div>
            </div>

            {{-- Roles list --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-100">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Roles disponibles</h3>
                    <span class="text-xs text-gray-500">{{ $roles->count() }} {{ $roles->count() === 1 ? 'rol' : 'roles' }}</span>
                </div>

                @if ($roles->isEmpty())
                    {{-- Empty state --}}
                    <div class="px-5 py-12 text-center">
                        <div class="inline-flex items-center justify-center rounded-full mb-3"
                             style="width:56px; height:56px; background-color:#E8ECF2;">
                            <svg style="width:28px;height:28px;color:#1E2E48;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-900">No hay roles creados aún</p>
                        <p class="text-xs text-gray-500 mt-1 mb-4">Crea tu primer rol para empezar a gestionar accesos del CRM.</p>
                        <a href="{{ route('team.crm-roles.create') }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 text-white text-sm font-medium rounded-md transition shadow-sm"
                           style="background-color: #1E2E48;">
                            <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Crear primer rol
                        </a>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach ($roles as $role)
                            @php
                                $isSystem = (bool) $role->is_default;
                                $avatarBg = $isSystem ? '#FBF7EC' : '#E8ECF2';
                                $avatarColor = $isSystem ? '#A08544' : '#1E2E48';
                                $initial = mb_strtoupper(mb_substr($role->name, 0, 1));
                            @endphp
                            <li class="px-5 py-4 flex items-center gap-4 hover:bg-gray-50 transition group">
                                {{-- Avatar circular con inicial --}}
                                <div class="flex items-center justify-center rounded-full font-bold text-sm flex-shrink-0"
                                     style="width:42px; height:42px; background-color:{{ $avatarBg }}; color:{{ $avatarColor }};">
                                    {{ $initial }}
                                </div>

                                {{-- Info del rol --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $role->name }}</h4>
                                        @if ($isSystem)
                                            <span class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wider font-semibold px-2 py-0.5 rounded-full"
                                                  style="background-color:#FBF7EC; color:#A08544; border:1px solid rgba(201,169,97,.35);">
                                                <svg style="width:10px;height:10px;" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2l2.39 6.95H22l-6.18 4.5 2.36 7.28L12 16.27 5.82 20.73l2.36-7.28L2 8.95h7.61z"/>
                                                </svg>
                                                Sistema
                                            </span>
                                        @endif
                                    </div>
                                    @if ($role->description)
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $role->description }}</p>
                                    @else
                                        <p class="text-xs text-gray-400 italic mt-0.5">Sin descripción</p>
                                    @endif
                                </div>

                                {{-- Contador de permisos --}}
                                <div class="hidden sm:flex flex-col items-end flex-shrink-0">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                          style="background-color:#E8ECF2; color:#1E2E48;">
                                        <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $role->permission_count }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 mt-0.5">permisos</span>
                                </div>

                                {{-- Acciones --}}
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <a href="{{ route('team.crm-roles.edit', $role) }}"
                                       class="inline-flex items-center justify-center w-9 h-9 rounded-md text-gray-500 hover:bg-white hover:text-gray-900 transition border border-transparent hover:border-gray-200"
                                       title="Editar">
                                        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    @if ($isSystem)
                                        {{-- Botón de eliminar deshabilitado para roles del sistema --}}
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-md text-gray-300 cursor-not-allowed"
                                              title="El rol del sistema no se puede eliminar">
                                            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/>
                                            </svg>
                                        </span>
                                    @else
                                        <form action="{{ route('team.crm-roles.destroy', $role) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar el rol &quot;{{ $role->name }}&quot;? Los usuarios con este rol quedarán sin rol asignado. Esta acción no se puede deshacer.');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-md text-gray-500 hover:bg-red-50 hover:text-red-600 transition border border-transparent hover:border-red-200"
                                                    title="Eliminar rol">
                                                <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Info panel --}}
            <div class="flex items-start gap-3 px-4 py-3 rounded-lg border"
                 style="background-color:#FBF7EC; border-color:rgba(201,169,97,.35);">
                <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;color:#A08544;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-xs leading-relaxed" style="color:#7a6132;">
                    El rol <strong>Administrador</strong> es creado por el sistema y siempre conserva todos los permisos.
                    Puedes editarle la descripción pero no quitarle accesos ni eliminarlo. El rol <strong>Editor</strong>
                    viene pre-configurado con permisos de lectura, creación y edición (sin eliminar) — puedes modificarlo
                    o eliminarlo si necesitas.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
