<x-app-layout> 
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva negociación – {{ $pipeline->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6" x-data="{ showNewContact: false }">
                <form method="POST" action="{{ route('deals.store', $pipeline) }}">
                    @csrf

                    {{-- Título --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Título</label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Monto / Moneda / Fecha cierre --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Monto</label>
                            <input type="number" step="0.01" name="amount" value="{{ old('amount') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @error('amount')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Moneda</label>
                            <input type="text" name="currency" value="{{ old('currency', 'PEN') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @error('currency')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha cierre (opcional)</label>
                            <input type="date" name="close_date" value="{{ old('close_date') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @error('close_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Contacto existente (Select2) --}}
                    <div class="mb-2" x-show="!showNewContact">
                        <label class="block text-sm font-medium text-gray-700">Contacto</label>
                        <select name="contact_id"
                                id="contact_id_create"
                                class="select2-contact mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Sin contacto --</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}"
                                        {{ old('contact_id') == $contact->id ? 'selected' : '' }}>
                                    {{ $contact->name }}
                                    @if($contact->company) – {{ $contact->company }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('contact_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Persona responsable (Select2 con miembros del team) --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Persona responsable
                        </label>
                        <select name="responsible_id"
                                id="responsible_id_create"
                                class="select2-contact mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Sin responsable --</option>
                            @foreach($teamMembers as $member)
                                <option value="{{ $member->id }}"
                                    {{ old('responsible_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('responsible_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Toggle crear nuevo contacto --}}
                    <div class="mb-4">
                        <button type="button"
                                class="text-xs text-indigo-600 hover:text-indigo-800"
                                @click="showNewContact = !showNewContact">
                            <span x-show="!showNewContact">+ Crear nuevo contacto</span>
                            <span x-show="showNewContact">← Usar lista de contactos</span>
                        </button>
                    </div>

                    {{-- Nuevo contacto (opcional) --}}
                    <div class="mb-4 border rounded-md p-4 bg-gray-50" x-show="showNewContact">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Nuevo contacto</h3>

                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-700">Nombre</label>
                            <input type="text" name="new_contact_name" value="{{ old('new_contact_name') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Email</label>
                                <input type="email" name="new_contact_email" value="{{ old('new_contact_email') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Teléfono</label>
                                <input type="text" name="new_contact_phone" value="{{ old('new_contact_phone') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                        </div>

                        <p class="mt-2 text-[11px] text-gray-500">
                            Si llenas estos datos, se creará un nuevo contacto y se usará en esta negociación.
                        </p>
                    </div>

                    {{-- Fase --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Fase</label>
                        <select name="stage_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}"
                                    {{ (old('stage_id', $defaultStageId) == $stage->id) ? 'selected' : '' }}>
                                    {{ $stage->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('stage_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Descripción --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="description" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('pipelines.kanban', $pipeline) }}"
                           class="px-4 py-2 border rounded-md text-gray-700">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- jQuery + Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('.select2-contact').select2({
                placeholder: '-- Seleccione --',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
</x-app-layout>
