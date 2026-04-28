<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalle negociación – {{ $deal->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2 text-sm">
                <p><strong>Pipeline:</strong> {{ $pipeline->name }}</p>
                <p><strong>Fase:</strong> {{ optional($deal->stage)->name }}</p>
                <p><strong>Contacto:</strong> {{ optional($deal->contact)->name }}</p>
                <p><strong>Monto:</strong> {{ $deal->currency }} {{ number_format($deal->amount, 2) }}</p>
                <p><strong>Estado:</strong> {{ $deal->status }}</p>
                <p><strong>Fecha cierre:</strong> {{ $deal->close_date }}</p>
                <p><strong>Descripción:</strong></p>
                <p>{{ $deal->description }}</p>

                <div class="pt-4 flex justify-end space-x-2">
                    <a href="{{ route('deals.edit', [$pipeline, $deal]) }}"
                       class="px-3 py-2 bg-indigo-600 text-white rounded-md text-xs">Editar</a>
                    <a href="{{ route('pipelines.kanban', $pipeline) }}"
                       class="px-3 py-2 border rounded-md text-xs">Volver al Kanban</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
