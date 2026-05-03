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

            {{-- ====== SECCIÓN PRODUCTOS ====== --}}
            @php
              $currency = $deal->currency ?? 'PEN';
              $catalogJson = $catalogProducts->map(fn($p) => [
                  'id' => $p->id, 'name' => $p->name,
                  'price' => (float)$p->price, 'unit' => $p->unit, 'currency' => $p->currency,
              ])->values()->toJson();
            @endphp

            <div class="mt-6 bg-white shadow-sm sm:rounded-lg p-6"
                 x-data="dealProducts({{ $catalogJson }})">

              {{-- Cabecera --}}
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900">Productos / Servicios</h3>
                <div class="flex items-center gap-2">
                  <a href="{{ route('products.index') }}" target="_blank"
                     class="text-xs text-gray-400 hover:text-indigo-600 underline">Gestionar catálogo</a>
                  <button type="button" @click="addOpen = true"
                          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar
                  </button>
                </div>
              </div>

              {{-- Formulario agregar --}}
              <div x-show="addOpen" x-transition
                   class="mb-5 rounded-xl border border-indigo-100 bg-indigo-50 p-4 space-y-4">

                {{-- Buscador del catálogo --}}
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">
                    Buscar en catálogo
                    <span class="font-normal text-gray-400">(opcional — o escribe el nombre abajo)</span>
                  </label>
                  <input type="text" x-model="query" @input="filterCatalog()"
                         placeholder="Buscar producto..."
                         class="w-full rounded-lg border-gray-200 bg-white text-sm py-1.5 mb-2">

                  <div x-show="filtered.length > 0"
                       class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 max-h-40 overflow-y-auto">
                    <template x-for="p in filtered" :key="p.id">
                      <button type="button" @click="pick(p)"
                              :class="selId == p.id ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'bg-white hover:bg-gray-50'"
                              class="text-left rounded-lg border border-gray-200 px-3 py-2 transition">
                        <p class="text-xs font-semibold text-gray-800 truncate" x-text="p.name"></p>
                        <p class="text-[10px] text-gray-500" x-text="p.unit + ' · ' + p.currency + ' ' + p.price.toFixed(2)"></p>
                      </button>
                    </template>
                  </div>
                  <p x-show="query && filtered.length === 0"
                     class="text-xs text-gray-400 mt-1">Sin resultados en catálogo.</p>
                </div>

                <form method="POST" action="{{ route('deals.products.store', [$pipeline, $deal]) }}">
                  @csrf
                  <input type="hidden" name="product_id" x-bind:value="selId">

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="md:col-span-2">
                      <label class="block text-xs font-semibold text-gray-600 mb-1">Nombre *</label>
                      <input type="text" name="name" required maxlength="255" x-model="addName"
                             placeholder="Nombre del producto o servicio"
                             class="w-full rounded-lg border-gray-200 bg-white text-sm py-1.5">
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-600 mb-1">Unidad</label>
                      <input type="text" name="unit" maxlength="50" x-model="addUnit"
                             class="w-full rounded-lg border-gray-200 bg-white text-sm py-1.5">
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-600 mb-1">Cantidad *</label>
                      <input type="number" name="quantity" required min="0.01" step="0.01" x-model="qty"
                             class="w-full rounded-lg border-gray-200 bg-white text-sm py-1.5">
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-600 mb-1">Precio unitario *</label>
                      <input type="number" name="unit_price" required min="0" step="0.01" x-model="unitPrice"
                             class="w-full rounded-lg border-gray-200 bg-white text-sm py-1.5">
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-600 mb-1">Descuento %</label>
                      <input type="number" name="discount" min="0" max="100" step="0.01" x-model="disc"
                             class="w-full rounded-lg border-gray-200 bg-white text-sm py-1.5">
                    </div>
                  </div>

                  <div class="flex items-center justify-between mt-3">
                    <p class="text-sm font-semibold text-gray-700">
                      Total:
                      <span class="text-indigo-700"
                            x-text="'{{ $currency }} ' + (qty * unitPrice * (1 - disc / 100)).toFixed(2)">
                      </span>
                    </p>
                    <div class="flex gap-2">
                      <button type="submit"
                              class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
                        Agregar
                      </button>
                      <button type="button" @click="close()"
                              class="px-4 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-xs font-medium hover:bg-gray-200 transition">
                        Cancelar
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              {{-- Tabla líneas existentes --}}
              @if($dealProducts->isNotEmpty())
              <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500">
                      <th class="px-4 py-2 text-left">Producto</th>
                      <th class="px-3 py-2 text-center">Unidad</th>
                      <th class="px-3 py-2 text-right">Cant.</th>
                      <th class="px-3 py-2 text-right">P. Unit.</th>
                      <th class="px-3 py-2 text-right">Desc.%</th>
                      <th class="px-3 py-2 text-right">Total</th>
                      <th class="px-3 py-2"></th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    @foreach($dealProducts as $dp)
                    <tr class="hover:bg-gray-50">
                      <td class="px-4 py-2 font-medium text-gray-900">
                        {{ $dp->name }}
                        @if($dp->notes)
                          <p class="text-[10px] text-gray-400">{{ $dp->notes }}</p>
                        @endif
                      </td>
                      <td class="px-3 py-2 text-center text-xs text-gray-500">{{ $dp->unit }}</td>
                      <td class="px-3 py-2 text-right text-gray-700">{{ rtrim(rtrim(number_format($dp->quantity,2),'0'),'.') }}</td>
                      <td class="px-3 py-2 text-right text-gray-700">{{ number_format($dp->unit_price,2) }}</td>
                      <td class="px-3 py-2 text-right text-gray-500">{{ $dp->discount > 0 ? $dp->discount.'%' : '—' }}</td>
                      <td class="px-3 py-2 text-right font-semibold text-gray-900">
                        {{ $currency }} {{ number_format($dp->total,2) }}
                      </td>
                      <td class="px-3 py-2 text-right">
                        <form method="POST"
                              action="{{ route('deals.products.destroy', [$pipeline, $deal, $dp]) }}"
                              onsubmit="return confirm('¿Eliminar esta línea?')">
                          @csrf @method('DELETE')
                          <button type="submit"
                                  class="text-red-400 hover:text-red-600 transition font-medium">✕</button>
                        </form>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr class="bg-indigo-50 border-t-2 border-indigo-100">
                      <td colspan="5" class="px-4 py-2.5 text-right text-sm font-bold text-gray-700">
                        Total negociación
                      </td>
                      <td class="px-3 py-2.5 text-right text-sm font-bold text-indigo-700">
                        {{ $currency }} {{ number_format($dealProducts->sum('total'),2) }}
                      </td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
              @else
              <div class="rounded-xl border border-dashed border-gray-200 py-8 text-center text-sm text-gray-400">
                Sin productos. Haz clic en <strong>Agregar</strong> para añadir el primero.
              </div>
              @endif

            </div>
            {{-- FIN SECCIÓN PRODUCTOS --}}

            {{-- ====== SECCIÓN FACTURAS / BOLETAS ====== --}}
            <div class="mt-6 bg-white shadow-sm sm:rounded-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900">Comprobantes electrónicos</h3>
                <a href="{{ route('invoices.create', [$pipeline, $deal]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition">
                  <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Nueva factura / boleta
                </a>
              </div>

              @php $invoices = $deal->invoices()->with('items')->get(); @endphp

              @if($invoices->isNotEmpty())
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                  <table class="w-full text-sm">
                    <thead>
                      <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600">Número</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600">Tipo</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600">Emisión</th>
                        <th class="px-3 py-2.5 text-right text-xs font-semibold text-gray-600">Total</th>
                        <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-600">Estado</th>
                        <th class="px-3 py-2.5"></th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                      @foreach($invoices as $inv)
                        @php
                          $colors = ['draft'=>'bg-gray-100 text-gray-500','signed'=>'bg-blue-100 text-blue-700',
                                     'sent'=>'bg-yellow-100 text-yellow-700','accepted'=>'bg-green-100 text-green-700',
                                     'rejected'=>'bg-red-100 text-red-700','cancelled'=>'bg-gray-100 text-gray-400'];
                        @endphp
                        <tr class="hover:bg-gray-50">
                          <td class="px-3 py-2.5 font-mono text-xs text-gray-700">{{ $inv->numero }}</td>
                          <td class="px-3 py-2.5 text-gray-600">{{ $inv->tipo_nombre }}</td>
                          <td class="px-3 py-2.5 text-gray-500">{{ $inv->fecha_emision->format('d/m/Y') }}</td>
                          <td class="px-3 py-2.5 text-right font-semibold text-gray-900">
                            {{ $inv->moneda }} {{ number_format($inv->total, 2) }}
                          </td>
                          <td class="px-3 py-2.5 text-center">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $colors[$inv->estado] ?? 'bg-gray-100 text-gray-500' }}">
                              {{ $inv->estado_badge }}
                            </span>
                          </td>
                          <td class="px-3 py-2.5 text-right">
                            <a href="{{ route('invoices.show', $inv) }}"
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver</a>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <div class="rounded-xl border border-dashed border-gray-200 py-6 text-center text-sm text-gray-400">
                  Sin comprobantes. Haz clic en <strong>Nueva factura / boleta</strong> para generar el primero.
                </div>
              @endif
            </div>
            {{-- FIN SECCIÓN FACTURAS --}}

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

    <script>
    function dealProducts(catalog) {
      return {
        catalog: catalog,
        filtered: catalog,
        query: '',
        addOpen: false,
        // selección catálogo
        selId: '',
        // campos del form
        addName: '',
        addUnit: 'unidad',
        qty: 1,
        unitPrice: 0,
        disc: 0,

        filterCatalog() {
          const q = this.query.toLowerCase().trim();
          this.filtered = q ? this.catalog.filter(p => p.name.toLowerCase().includes(q)) : this.catalog;
        },

        pick(p) {
          this.selId    = p.id;
          this.addName  = p.name;
          this.addUnit  = p.unit;
          this.unitPrice = p.price;
          this.disc     = 0;
          this.qty      = 1;
        },

        close() {
          this.addOpen   = false;
          this.selId     = '';
          this.addName   = '';
          this.addUnit   = 'unidad';
          this.qty       = 1;
          this.unitPrice = 0;
          this.disc      = 0;
          this.query     = '';
          this.filtered  = this.catalog;
        }
      };
    }
    </script>
</x-app-layout>
