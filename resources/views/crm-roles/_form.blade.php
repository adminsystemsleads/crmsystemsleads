{{--
  Formulario compartido entre create y edit.
  Variables esperadas:
    - $role   (CrmRole|new) — registro a editar o uno vacío para crear
    - $groups (array)       — catálogo de permisos desde CrmPermissions::groups()
    - $action (string)      — URL del form
    - $method (string)      — 'POST' para create, 'PUT' para update
--}}

<form method="POST" action="{{ $action }}" x-data="crmRoleForm()">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    {{-- Datos básicos --}}
    <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre del rol</label>
                <input id="name" name="name" type="text" required maxlength="120"
                       value="{{ old('name', $role->name) }}"
                       @if ($role->is_default) readonly @endif
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @if ($role->is_default) bg-gray-50 cursor-not-allowed @endif">
                @if ($role->is_default)
                    <p class="text-[11px] text-gray-500 mt-1">El nombre del rol Administrador no se puede modificar.</p>
                @endif
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Descripción <span class="text-gray-400 text-xs">(opcional)</span></label>
                <input id="description" name="description" type="text" maxlength="255"
                       value="{{ old('description', $role->description) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="Ej: Acceso completo a contactos y embudos, sin facturación">
            </div>
        </div>
    </div>

    {{-- Permisos --}}
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Permisos del rol</h3>
                <p class="text-xs text-gray-500 mt-0.5">Marca cada acción que este rol puede realizar.</p>
            </div>
            @unless ($role->is_default)
                <div class="flex gap-2 text-xs">
                    <button type="button" @click="selectAll()" class="px-3 py-1.5 rounded-md border border-gray-200 hover:bg-gray-50 text-gray-700">
                        Marcar todo
                    </button>
                    <button type="button" @click="selectNone()" class="px-3 py-1.5 rounded-md border border-gray-200 hover:bg-gray-50 text-gray-700">
                        Quitar todo
                    </button>
                </div>
            @endunless
        </div>

        @if ($role->is_default)
            <div class="mb-4 p-3 rounded-md text-xs"
                 style="background-color:#FBF7EC; color:#A08544; border:1px solid rgba(201,169,97,.35);">
                ⚠️ El rol <strong>Administrador</strong> siempre tiene acceso completo. Los permisos están bloqueados para garantizar que el sistema nunca quede sin alguien que pueda administrarlo.
            </div>
        @endif

        @php $current = old('permissions', $role->permissions ?? []); @endphp

        <div class="space-y-5">
            @foreach ($groups as $groupKey => $group)
                <div class="border border-gray-100 rounded-lg overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <svg style="width:18px;height:18px;color:#1E2E48;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $group['icon'] }}"/>
                            </svg>
                            <h4 class="text-sm font-semibold text-gray-900">{{ $group['label'] }}</h4>
                        </div>
                        @unless ($role->is_default)
                            <button type="button" @click="toggleGroup('{{ $groupKey }}')"
                                    class="text-xs text-gray-500 hover:text-gray-700">
                                Marcar/desmarcar grupo
                            </button>
                        @endunless
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3" data-group="{{ $groupKey }}">
                        @foreach ($group['permissions'] as $permKey => $permLabel)
                            <label class="flex items-start gap-2.5 text-sm cursor-pointer @if ($role->is_default) opacity-60 cursor-not-allowed @endif">
                                <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                                       @checked(in_array($permKey, (array) $current, true) || $role->is_default)
                                       @disabled($role->is_default)
                                       class="mt-0.5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <div class="flex-1">
                                    <div class="text-gray-800">{{ $permLabel }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $permKey }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex items-center justify-end gap-2 mt-6">
        <a href="{{ route('team.crm-roles.index') }}"
           class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
        <button type="submit"
                class="px-5 py-2 text-white text-sm rounded-md transition"
                style="background-color: #1E2E48;"
                onmouseover="this.style.backgroundColor='#152139'"
                onmouseout="this.style.backgroundColor='#1E2E48'">
            {{ $role->exists ? 'Guardar cambios' : 'Crear rol' }}
        </button>
    </div>
</form>

<script>
function crmRoleForm() {
    return {
        selectAll() {
            this.$root.querySelectorAll('input[name="permissions[]"]:not(:disabled)').forEach(cb => cb.checked = true);
        },
        selectNone() {
            this.$root.querySelectorAll('input[name="permissions[]"]:not(:disabled)').forEach(cb => cb.checked = false);
        },
        toggleGroup(groupKey) {
            const container = this.$root.querySelector(`[data-group="${groupKey}"]`);
            if (!container) return;
            const checkboxes = container.querySelectorAll('input[type="checkbox"]:not(:disabled)');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        }
    }
}
</script>
