<x-app-layout> 
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar negociación – {{ $deal->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('pipelines.kanban', $pipeline) }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                ← Volver al Kanban
            </a>

            @if(session('status'))
                <div class="mb-4 text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- ====== COLUMNA IZQUIERDA: DATOS DE LA NEGOCIACIÓN ====== --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form method="POST"
                          action="{{ route('deals.update', [$pipeline, $deal]) }}">
                        @csrf
                        @method('PUT')

                        {{-- Título --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Título de la negociación
                            </label>
                            <input type="text" name="title"
                                   value="{{ old('title', $deal->title) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @error('title')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Contacto (Select2) --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Contacto
                            </label>
                            <select name="contact_id"
                                    id="contact_id_edit"
                                    class="select2-contact mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Sin contacto --</option>
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->id }}"
                                        {{ (old('contact_id', $deal->contact_id) == $contact->id) ? 'selected' : '' }}>
                                        {{ $contact->name }}
                                        @if($contact->company) – {{ $contact->company }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('contact_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Persona responsable (Select2) --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Persona responsable
                            </label>
                            <select name="responsible_id"
                                    id="responsible_id_edit"
                                    class="select2-responsible mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Sin responsable --</option>
                                @foreach($teamMembers as $member)
                                    <option value="{{ $member->id }}"
                                        {{ old('responsible_id', $deal->responsible_id) == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('responsible_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Monto y moneda --}}
                        <div class="mb-4 grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Monto
                                </label>
                                <input type="number" step="0.01" name="amount"
                                       value="{{ old('amount', $deal->amount) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                @error('amount')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Moneda
                                </label>
                                <input type="text" name="currency"
                                       value="{{ old('currency', $deal->currency ?? 'PEN') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                @error('currency')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Pipeline + Fase --}}
                        <div class="mb-4 grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Pipeline
                                </label>
                                <input type="text" disabled
                                       value="{{ $pipeline->name }}"
                                       class="mt-1 block w-full border-gray-200 bg-gray-100 rounded-md shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Fase
                                </label>
                                <select name="stage_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    @foreach($stages as $stage)
                                        <option value="{{ $stage->id }}"
                                            {{ (old('stage_id', $deal->stage_id) == $stage->id) ? 'selected' : '' }}>
                                            {{ $stage->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('stage_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Estado --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Estado
                            </label>
                            <select name="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="open" {{ old('status', $deal->status) === 'open' ? 'selected' : '' }}>
                                    Abierta
                                </option>
                                <option value="won" {{ old('status', $deal->status) === 'won' ? 'selected' : '' }}>
                                    Ganada
                                </option>
                                <option value="lost" {{ old('status', $deal->status) === 'lost' ? 'selected' : '' }}>
                                    Perdida
                                </option>
                            </select>
                            @error('status')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fecha de cierre --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Fecha estimada de cierre
                            </label>
                            <input type="date" name="close_date"
                                   value="{{ old('close_date', $deal->close_date ? $deal->close_date->format('Y-m-d') : '') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @error('close_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Descripción / notas
                            </label>
                            <textarea name="description" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $deal->description) }}</textarea>
                            @error('description')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-between items-center">
                            <a href="{{ route('pipelines.kanban', $pipeline) }}"
                               class="text-sm text-gray-600 hover:text-gray-900">
                                ← Volver al Kanban
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ====== COLUMNA DERECHA: COMENTARIOS / ACTIVIDAD + HISTORIAL ====== --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-4"
                     x-data="{ tab: '{{ $whatsappConversations->isNotEmpty() ? 'whatsapp' : 'comments' }}' }">
                    {{-- Tabs --}}
                    <div class="border-b mb-3 flex space-x-4 text-sm">
                        {{-- WhatsApp primero --}}
                        <button type="button"
                                class="pb-2 border-b-2 flex items-center gap-1"
                                :class="tab === 'whatsapp'
                                    ? 'border-green-600 text-green-600 font-semibold'
                                    : 'border-transparent text-gray-500'"
                                @click="tab = 'whatsapp'">
                            <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/>
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.126 1.532 5.855L.057 23.882a.5.5 0 00.611.61l6.102-1.6A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22a9.944 9.944 0 01-5.073-1.386l-.363-.215-3.764.987.999-3.671-.236-.375A9.955 9.955 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                            </svg>
                            WhatsApp
                            @if($whatsappConversations->isNotEmpty())
                                <span class="inline-flex items-center justify-center rounded-full bg-green-100 text-green-700 text-[10px] font-semibold px-1.5">
                                    {{ $whatsappConversations->count() }}
                                </span>
                            @endif
                        </button>
                        <button type="button"
                                class="pb-2 border-b-2"
                                :class="tab === 'comments'
                                    ? 'border-indigo-600 text-indigo-600 font-semibold'
                                    : 'border-transparent text-gray-500'"
                                @click="tab = 'comments'">
                            Comentario
                        </button>
                        <button type="button"
                                class="pb-2 border-b-2"
                                :class="tab === 'activity'
                                    ? 'border-indigo-600 text-indigo-600 font-semibold'
                                    : 'border-transparent text-gray-500'"
                                @click="tab = 'activity'">
                            Actividad
                        </button>
                    </div>

                    {{-- === Comentarios === --}}
                    <div x-show="tab === 'comments'" x-cloak>
                        {{-- Form nuevo comentario --}}
                        <form method="POST"
                              action="{{ route('deals.comments.store', [$pipeline, $deal]) }}"
                              class="mb-4">
                            @csrf
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Deje un comentario
                            </label>
                            <textarea name="body" rows="2"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                      placeholder="Escriba un comentario...">{{ old('body') }}</textarea>
                            @error('body')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            <div class="flex justify-end mt-2">
                                <button type="submit"
                                        class="px-3 py-1.5 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700">
                                    Agregar comentario
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- === Actividad === --}}
                    <div x-show="tab === 'activity'" x-cloak>
                        {{-- Form nueva actividad --}}
                        <form method="POST"
                              action="{{ route('deals.activities.store', [$pipeline, $deal]) }}"
                              class="mb-4 space-y-3">
                            @csrf

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">
                                        Tipo
                                    </label>
                                    <select name="type"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                        <option value="call">Llamada</option>
                                        <option value="meeting">Reunión</option>
                                        <option value="task">Tarea</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">
                                        Fecha y hora
                                    </label>
                                    <input type="datetime-local" name="due_at"
                                           value="{{ old('due_at') }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700">
                                    Asunto
                                </label>
                                <input type="text" name="subject"
                                       value="{{ old('subject') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                       placeholder="Ej. Reunión de demo, llamada de seguimiento...">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700">
                                    Notas
                                </label>
                                <textarea name="notes" rows="2"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                          placeholder="Detalles de la actividad...">{{ old('notes') }}</textarea>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                        class="px-3 py-1.5 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700">
                                    Guardar actividad
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- === WhatsApp Conversaciones === --}}
                    <div x-show="tab === 'whatsapp'" x-cloak>
                        @if($whatsappConversations->isEmpty())
                            <div class="flex flex-col items-center justify-center py-8 text-center text-gray-400">
                                <svg class="size-10 mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="text-sm">No hay conversaciones de WhatsApp vinculadas.</p>
                                <p class="text-xs mt-1">Se vinculan automáticamente cuando llega un mensaje.</p>
                            </div>
                        @else
                            <ul class="space-y-2">
                                @foreach($whatsappConversations as $conv)
                                    <a href="{{ route('whatsapp.inbox.show', $conv) }}"
                                       class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 hover:bg-green-50 hover:border-green-200 transition group">
                                        {{-- Avatar inicial --}}
                                        <div class="size-9 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-sm font-semibold shrink-0">
                                            {{ strtoupper(mb_substr($conv->contact_name ?? $conv->contact_phone ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-gray-900 truncate group-hover:text-green-700">
                                                    {{ $conv->contact_name ?? $conv->contact_phone }}
                                                </span>
                                                <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[9px] font-semibold shrink-0
                                                    {{ $conv->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' }}">
                                                    {{ $conv->status === 'open' ? 'Abierta' : 'Cerrada' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-400 truncate mt-0.5">
                                                {{ $conv->contact_phone }}
                                                @if($conv->account) · {{ $conv->account->name }} @endif
                                                @if($conv->last_message_at) · {{ $conv->last_message_at->diffForHumans() }} @endif
                                            </p>
                                            @if($conv->last_message_preview)
                                                <p class="text-xs text-gray-500 truncate italic mt-0.5">{{ $conv->last_message_preview }}</p>
                                            @endif
                                        </div>
                                        <svg class="size-4 text-gray-300 group-hover:text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- ====== HISTORIAL COMBINADO (COMENTARIOS + ACTIVIDADES) ====== --}}
                    @php
                        $timeline = $comments->map(function ($c) {
                            return [
                                'kind' => 'comment',
                                'date' => $c->created_at,
                                'item' => $c,
                            ];
                        })->merge(
                            $activities->map(function ($a) {
                                return [
                                    'kind' => 'activity',
                                    'date' => $a->created_at, // o $a->due_at si prefieres
                                    'item' => $a,
                                ];
                            })
                        )->sortByDesc('date');
                    @endphp

                    <div class="mt-6 pt-4 border-t">
                        <h3 class="text-sm font-semibold text-gray-800 mb-3">
                            Historial
                        </h3>

                        <div class="space-y-3 max-h-[360px] overflow-y-auto text-xs">
                            @forelse($timeline as $entry)
                                @if($entry['kind'] === 'comment')
                                    @php $comment = $entry['item']; @endphp
                                    {{-- Tarjeta de comentario --}}
                                    <div class="border border-gray-100 rounded-md p-2 bg-gray-50">
                                        <div class="flex justify-between items-center mb-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 text-indigo-700 font-semibold">
                                                    COM
                                                </span>
                                                <span class="font-semibold text-gray-800">
                                                    {{ $comment->user->name ?? 'Usuario' }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] text-gray-500">
                                                {{ $entry['date']->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                        <div class="text-gray-700 whitespace-pre-line">
                                            {{ $comment->body }}
                                        </div>
                                    </div>
                                @else
                                    @php $activity = $entry['item']; @endphp
                                    {{-- Tarjeta de actividad --}}
                                    <div class="border border-gray-100 rounded-md p-2 bg-gray-50">
                                        <div class="flex justify-between mb-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold
                                                    @if($activity->type === 'call') bg-blue-100 text-blue-700
                                                    @elseif($activity->type === 'meeting') bg-purple-100 text-purple-700
                                                    @else bg-amber-100 text-amber-700 @endif">
                                                    {{ strtoupper($activity->type) }}
                                                </span>
                                                <span class="font-semibold text-gray-800">
                                                    {{ $activity->subject }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] text-gray-500">
                                                {{ $entry['date']->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                        @if($activity->notes)
                                            <div class="text-gray-700 whitespace-pre-line">
                                                {{ $activity->notes }}
                                            </div>
                                        @endif
                                        <div class="mt-1 text-[10px] text-gray-500">
                                            Creado por {{ $activity->user->name ?? 'Usuario' }}
                                            • Estado: {{ $activity->status }}
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <p class="text-[11px] text-gray-400">
                                    Aún no hay comentarios ni actividades en esta negociación.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>

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
                placeholder: '-- Sin contacto --',
                allowClear: true,
                width: '100%'
            });

            $('.select2-responsible').select2({
                placeholder: '-- Sin responsable --',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
</x-app-layout>
