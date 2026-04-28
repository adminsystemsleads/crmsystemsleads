<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kanban – {{ $pipeline->name }}
            </h2>

            
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Toggle de vista --}}
            <div class="inline-flex rounded-xl border border-gray-200 bg-white p-1 text-sm">
                <a href="{{ route('pipelines.kanban', $pipeline) }}"
                   class="px-3 py-1.5 rounded-lg {{ ($viewMode ?? 'kanban') !== 'table' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                    Kanban
                </a>
                <a href="{{ route('pipelines.kanban', [$pipeline, 'view' => 'table']) }}"
                   class="px-3 py-1.5 rounded-lg {{ ($viewMode ?? 'kanban') === 'table' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                    Tabla
                </a>
            </div>
            
            <div class="mb-4 flex justify-between items-center">
                
                <p class="text-sm text-gray-600">
                    Vista de negociaciones por fases del pipeline.
                </p>

                <div class="flex items-center gap-2">
                    <a href="{{ route('pipelines.edit', $pipeline) }}"
                       class="text-indigo-100 text-xs px-3 py-1 rounded-full bg-indigo-600/80 hover:bg-indigo-700">
                        Configurar fases
                    </a>
                </div>
            </div>

            {{-- ===============================
                 VISTA TABLA
                 =============================== --}}
            @if(($viewMode ?? 'kanban') === 'table')
                @php
                    // Aplanar deals para tabla
                    $allDeals = collect();
                    foreach ($stages as $stage) {
                        $deals = $dealsByStage[$stage->id] ?? collect();
                        foreach ($deals as $d) {
                            $allDeals->push([
                                'deal'  => $d,
                                'stage' => $stage,
                            ]);
                        }
                    }
                @endphp

                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
                                <tr>
                                    <th class="py-2 pr-4">Título</th>
                                    <th class="py-2 pr-4">Contacto</th>
                                    <th class="py-2 pr-4">Responsable</th>
                                    <th class="py-2 pr-4">Fase</th>
                                    <th class="py-2 pr-4">Monto</th>
                                    <th class="py-2 pr-4">Cierre</th>
                                    <th class="py-2 pr-2 text-right">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y">
                                @forelse($allDeals as $row)
                                    @php
                                        $deal  = $row['deal'];
                                        $stage = $row['stage'];
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 pr-4">
                                            <div class="font-semibold text-gray-800">
                                                {{ $deal->title }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                #{{ $deal->id }}
                                            </div>
                                        </td>

                                        <td class="py-2 pr-4">
                                            @if($deal->contact)
                                                <div class="text-indigo-700">{{ $deal->contact->name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $deal->contact->company ?? '' }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="py-2 pr-4">
                                            {{-- Si tienes relación responsible() en Deal --}}
                                            @if(method_exists($deal, 'responsible') && $deal->responsible)
                                                {{ $deal->responsible->name }}
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="py-2 pr-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                                                {{ $stage->name }}
                                            </span>
                                        </td>

                                        <td class="py-2 pr-4">
                                            @if($deal->amount)
                                                <span class="text-gray-800 font-semibold">
                                                    {{ $deal->currency ?? 'PEN' }} {{ number_format($deal->amount, 2) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="py-2 pr-4">
                                            @if($deal->close_date)
                                                <span class="text-gray-700">
                                                    {{ \Carbon\Carbon::parse($deal->close_date)->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>

                                        <td class="py-2 pr-2 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('deals.edit', [$pipeline, $deal]) }}"
                                                   class="text-gray-500 hover:text-indigo-600 text-sm"
                                                   title="Editar">
                                                    ✏
                                                </a>

                                                <form action="{{ route('deals.destroy', [$pipeline, $deal]) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('¿Eliminar esta negociación?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="text-gray-500 hover:text-red-600 text-sm"
                                                            title="Eliminar">
                                                        🗑
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 text-center text-gray-400">
                                            No hay negociaciones en este embudo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
            {{-- ===============================
                 VISTA KANBAN (TU CÓDIGO)
                 =============================== --}}

            {{-- Contenedor principal del Kanban (sin fondo blanco) --}}
            <div class="rounded-3xl p-5">
                <div class="flex space-x-5 overflow-x-auto pb-3">

                    @foreach($stages as $stage)
                        @php
                            $deals = $dealsByStage[$stage->id] ?? collect();
                            $totalAmount = $deals->sum('amount');
                            $currency = $deals->count() ? ($deals->first()->currency ?? 'PEN') : 'PEN';
                        @endphp

                        {{-- Columna --}}
                        <div class="flex-shrink-0 w-72"
                             data-stage-id="{{ $stage->id }}"
                             data-pipeline-id="{{ $pipeline->id }}">
                            <div class="rounded-2xl flex flex-col h-[78vh] border border-gray-200 bg-transparent">

                                {{-- Header --}}
                                <div class="rounded-t-2xl px-4 pt-3 pb-2 bg-transparent">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-900">
                                        {{ $stage->name }}
                                        <span class="text-[10px] opacity-80 text-gray-700" data-stage-count>
                                            ({{ $deals->count() }})
                                        </span>
                                    </div>

                                    <div class="mt-2 text-lg font-bold text-gray-900">
                                        <span data-stage-total-currency>{{ $currency }}</span>
                                        <span data-stage-total>
                                            {{ number_format($totalAmount, 0, '.', ',') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Botón + --}}
                                <div class="px-4 pb-2">
                                    <a href="{{ route('deals.create', [$pipeline, 'stage' => $stage->id]) }}"
                                       class="w-full text-xs py-1.5 rounded-full border border-dashed border-gray-300 text-gray-500 hover:bg-gray-50 flex items-center justify-center">
                                        + Nueva negociación
                                    </a>
                                </div>

                                {{-- Cards --}}
                                <div class="flex-1 px-3 pb-3 overflow-y-auto space-y-3" data-stage-body>
                                    @foreach($deals as $deal)
                                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-3 py-3 text-xs space-y-1 kanban-card"
                                             draggable="true"
                                             data-deal-id="{{ $deal->id }}"
                                             data-amount="{{ $deal->amount ?? 0 }}"
                                             data-currency="{{ $deal->currency ?? 'PEN' }}">
                                            <div class="flex justify-between items-start">
                                                <div class="font-semibold text-gray-800 text-sm line-clamp-2">
                                                    {{ $deal->title }}
                                                </div>

                                                <div class="flex items-center space-x-1">
                                                    <a href="{{ route('deals.edit', [$pipeline, $deal]) }}"
                                                       class="text-gray-400 hover:text-indigo-600 text-xs"
                                                       title="Editar">
                                                        ✏
                                                    </a>
                                                    <form action="{{ route('deals.destroy', [$pipeline, $deal]) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('¿Eliminar esta negociación?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="text-gray-400 hover:text-red-600 text-xs"
                                                                title="Eliminar">
                                                            🗑
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            @if($deal->contact)
                                                <div class="text-[11px] text-indigo-700">
                                                    {{ $deal->contact->name }}
                                                </div>
                                            @endif

                                            @if($deal->amount)
                                                <div class="text-[11px] text-gray-700">
                                                    {{ $deal->currency }} {{ number_format($deal->amount, 2) }}
                                                </div>
                                            @endif

                                            <div class="flex items-center justify-between mt-1">
                                                @if($deal->close_date)
                                                    <div class="text-[10px] text-gray-500">
                                                        Cierre:
                                                        {{ \Carbon\Carbon::parse($deal->close_date)->format('d M Y') }}
                                                    </div>
                                                @endif

                                                <div class="flex items-center space-x-3 text-gray-700 mt-2">
                                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[11px] font-semibold text-gray-700">
                                                        @
                                                    </span>
                                                    <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[11px] font-semibold text-gray-700">
                                                        ☎
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <p data-empty-message
                                       class="text-[1px] text-gray-400 mt-1 italic {{ $deals->isEmpty() ? '' : 'hidden' }}">
                                        Sin negociaciones
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            @endif

        </div>
    </div>

    {{-- JS SOLO PARA KANBAN --}}
    @if(($viewMode ?? 'kanban') !== 'table')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : '';

            let draggedCard = null;

            function recalcColumn(column) {
                const body = column.querySelector('[data-stage-body]');
                if (!body) return;

                const cards = body.querySelectorAll('.kanban-card[data-deal-id]');
                const emptyMsg = column.querySelector('[data-empty-message]');
                const countSpan = column.querySelector('[data-stage-count]');
                const totalCurrencySpan = column.querySelector('[data-stage-total-currency]');
                const totalSpan = column.querySelector('[data-stage-total]');

                if (emptyMsg) emptyMsg.classList.toggle('hidden', cards.length > 0);
                if (countSpan) countSpan.textContent = `(${cards.length})`;

                let totalAmount = 0;
                let currency = 'PEN';

                cards.forEach(card => {
                    const amount = parseFloat(card.dataset.amount || '0');
                    if (!isNaN(amount)) totalAmount += amount;
                    if (card.dataset.currency) currency = card.dataset.currency;
                });

                if (totalCurrencySpan) totalCurrencySpan.textContent = currency;
                if (totalSpan) {
                    totalSpan.textContent = totalAmount.toLocaleString('es-PE', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            }

            function recalcAllColumns() {
                document.querySelectorAll('[data-stage-id][data-pipeline-id]').forEach(col => recalcColumn(col));
            }

            function initCards() {
                document.querySelectorAll('.kanban-card[data-deal-id]').forEach(card => {
                    card.addEventListener('dragstart', e => {
                        draggedCard = card;
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', card.dataset.dealId);
                        setTimeout(() => card.classList.add('opacity-50'), 0);
                    });

                    card.addEventListener('dragend', () => {
                        card.classList.remove('opacity-50');
                        draggedCard = null;
                    });
                });
            }

            function initColumns() {
                document.querySelectorAll('[data-stage-id][data-pipeline-id]').forEach(column => {
                    column.addEventListener('dragover', e => {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        column.classList.add('ring-2', 'ring-indigo-400');
                    });

                    column.addEventListener('dragleave', () => {
                        column.classList.remove('ring-2', 'ring-indigo-400');
                    });

                    column.addEventListener('drop', e => {
                        e.preventDefault();
                        column.classList.remove('ring-2', 'ring-indigo-400');

                        const dealId = e.dataTransfer.getData('text/plain');
                        const stageId = column.dataset.stageId;
                        const pipelineId = column.dataset.pipelineId;

                        if (!dealId || !stageId || !pipelineId) return;

                        const body = column.querySelector('[data-stage-body]');
                        if (draggedCard && body) body.prepend(draggedCard);

                        recalcAllColumns();

                        fetch(`/pipelines/${pipelineId}/deals/${dealId}/move`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ stage_id: stageId })
                        }).then(r => {
                            if (!r.ok) throw new Error('Error');
                            return r.json();
                        }).catch(() => window.location.reload());
                    });
                });
            }

            initCards();
            initColumns();
            recalcAllColumns();
        });
    </script>
    @endif
</x-app-layout>
