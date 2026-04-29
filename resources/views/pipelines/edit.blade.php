<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar pipeline: {{ $pipeline->name }}
        </h2>
    </x-slot>

    <div class="py-8 space-y-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Datos generales del pipeline --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('pipelines.update', $pipeline) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $pipeline->name) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="description" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $pipeline->description) }}</textarea>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="is_active" value="1"
                               class="rounded border-gray-300"
                               {{ $pipeline->is_active ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">Pipeline activo</span>
                    </div>

                    <div class="mb-6 flex items-start gap-3 p-3 rounded-lg bg-indigo-50 border border-indigo-100">
                        <input type="checkbox" name="show_in_nav" value="1"
                               id="show_in_nav"
                               class="mt-0.5 rounded border-gray-300 text-indigo-600"
                               {{ $pipeline->show_in_nav ? 'checked' : '' }}>
                        <div>
                            <label for="show_in_nav" class="text-sm font-medium text-gray-800 cursor-pointer">
                                Mostrar en menú lateral como acceso rápido
                            </label>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Los usuarios con permiso de ver este pipeline podrán acceder al Kanban directamente desde la barra lateral.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Fases del pipeline --}}
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Fases del pipeline</h3>

                {{-- Lista de fases --}}
                <table class="min-w-full divide-y divide-gray-200 mb-6">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prob.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse($pipeline->stages as $stage)
                            <tr>
                                <form method="POST"
                                      action="{{ route('pipelines.stages.update', [$pipeline, $stage]) }}">
                                    @csrf
                                    @method('PUT')

                                    <td class="px-4 py-2">
                                        <input type="number" name="sort_order" value="{{ $stage->sort_order }}"
                                               class="w-20 border-gray-300 rounded-md text-sm">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" name="name" value="{{ $stage->name }}"
                                               class="w-full border-gray-300 rounded-md text-sm">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="probability" value="{{ $stage->probability }}"
                                               min="0" max="100"
                                               class="w-20 border-gray-300 rounded-md text-sm">
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center space-x-2">
                                            <label class="inline-flex items-center text-xs">
                                                <input type="checkbox" name="is_won" value="1"
                                                       class="rounded border-gray-300"
                                                       {{ $stage->is_won ? 'checked' : '' }}>
                                                <span class="ml-1">Ganada</span>
                                            </label>
                                            <label class="inline-flex items-center text-xs">
                                                <input type="checkbox" name="is_lost" value="1"
                                                       class="rounded border-gray-300"
                                                       {{ $stage->is_lost ? 'checked' : '' }}>
                                                <span class="ml-1">Perdida</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-right space-x-2">
                                        <button type="submit"
                                                class="text-indigo-600 hover:text-indigo-900 text-xs">
                                            Guardar
                                        </button>
                                </form>
                                <form method="POST"
                                      action="{{ route('pipelines.stages.destroy', [$pipeline, $stage]) }}"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('¿Eliminar esta fase?')"
                                            class="text-red-600 hover:text-red-900 text-xs">
                                        Eliminar
                                    </button>
                                </form>
                                    </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-gray-500">
                                    Aún no hay fases definidas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Nueva fase --}}
                <h4 class="text-sm font-semibold mb-2">Agregar nueva fase</h4>
                <form method="POST" action="{{ route('pipelines.stages.store', $pipeline) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Nombre</label>
                        <input type="text" name="name"
                               class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Slug (opcional)</label>
                        <input type="text" name="slug"
                               class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Probabilidad %</label>
                        <input type="number" name="probability" min="0" max="100"
                               class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                    </div>
                    <div class="flex space-x-2">
                        <label class="inline-flex items-center text-xs mt-5">
                            <input type="checkbox" name="is_won" value="1"
                                   class="rounded border-gray-300">
                            <span class="ml-1">Ganada</span>
                        </label>
                        <label class="inline-flex items-center text-xs mt-5">
                            <input type="checkbox" name="is_lost" value="1"
                                   class="rounded border-gray-300">
                            <span class="ml-1">Perdida</span>
                        </label>
                        <button type="submit"
                                class="ml-auto mt-4 px-3 py-2 bg-indigo-600 text-white rounded-md text-xs hover:bg-indigo-700">
                            Añadir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
