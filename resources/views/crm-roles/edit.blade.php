<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Rol:') }} {{ $role->name }} {{ __('— Configurar Roles y Permisos de CRM') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @include('crm-roles._form', [
                'role'   => $role,
                'groups' => $groups,
                'action' => route('team.crm-roles.update', $role),
                'method' => 'PUT',
            ])
        </div>
    </div>
</x-app-layout>
