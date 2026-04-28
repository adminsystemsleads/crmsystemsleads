<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Listado de Gastos</h2>
  </x-slot>

  <div class="py-10">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow rounded-lg p-6">

        {{-- 🔍 Filtros en una sola fila --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-6">

  {{-- Año --}}
  <div class="flex flex-col">
    <label class="text-xs text-gray-600 font-medium mb-1">Año</label>
    <input type="number" name="anio" value="{{ $request->anio }}" placeholder="2025"
           class="w-24 border rounded px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
  </div>

  {{-- Mes --}}
  <div class="flex flex-col">
    <label class="text-xs text-gray-600 font-medium mb-1">Mes</label>
    <select name="mes" class="w-32 border rounded px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
      <option value="">Todos</option>
      @foreach (range(1,12) as $m)
        <option value="{{ $m }}" {{ (string)$request->mes === (string)$m ? 'selected' : '' }}>
          {{ ucfirst(\Carbon\Carbon::create()->month($m)->monthName) }}
        </option>
      @endforeach
    </select>
  </div>

  {{-- Categoría --}}
  <div class="flex flex-col">
    <label class="text-xs text-gray-600 font-medium mb-1">Categoría</label>
    <select name="categoria_id" class="w-48 border rounded px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
      <option value="">Todas</option>
      @foreach ($categorias as $cat)
        <option value="{{ $cat->id }}" {{ (string)$request->categoria_id === (string)$cat->id ? 'selected' : '' }}>
          {{ $cat->nombre }}
        </option>
      @endforeach
    </select>
  </div>

  {{-- Unidad / Perfil --}}
  <div class="flex flex-col">
    <label class="text-xs text-gray-600 font-medium mb-1">Unidad / Perfil</label>
    <select name="perfil" class="w-48 border rounded px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
      <option value="">Todos</option>
      <option value="general" {{ $request->perfil === 'general' ? 'selected' : '' }}>General (sin unidad)</option>
      @foreach ($perfiles as $perfil)
        <option value="{{ $perfil->id }}" {{ (string)$request->perfil === (string)$perfil->id ? 'selected' : '' }}>
          {{ $perfil->unidad }}
        </option>
      @endforeach
    </select>
  </div>

  {{-- Botón Filtrar --}}
  <div class="flex flex-col">
    <label class="text-xs text-transparent mb-1">.</label>
    <button class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 transition">
      Filtrar
    </button>
  </div>

</form>

        {{-- Mensajes --}}
        @if (session('success'))
          <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded">
            ✅ {{ session('success') }}
          </div>
        @endif

        {{-- Tabla --}}
        <div class="overflow-x-auto">
          <table class="min-w-full border text-sm">
  <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
    <tr>
      <th class="border px-3 py-2">Unidad</th>
      <th class="border px-3 py-2">Mes/Año</th>
      <th class="border px-3 py-2">Categoría</th>
      <th class="border px-3 py-2">Código Pago</th>
      <th class="border px-3 py-2">Monto</th>
      <th class="border px-3 py-2">Descripción</th>
      <th class="border px-3 py-2">Día de Pago</th>
      <th class="border px-3 py-2">Voucher</th>
      <th class="border px-3 py-2">Verificado</th>
      <th class="border px-3 py-2 text-center">Acciones</th>
    </tr>
  </thead>

  <tbody>
    @forelse ($gastos as $g)
      <tr>
        {{-- Unidad --}}
        <td class="border px-3 py-2">{{ optional($g->memberProfile)->unidad ?? 'General' }}</td>

        {{-- Mes/Año --}}
        <td class="border px-3 py-2">{{ $g->mes }}/{{ $g->anio }}</td>

        {{-- Categoría --}}
        <td class="border px-3 py-2">{{ $g->categoria->nombre ?? '-' }}</td>

        {{-- Código de pago --}}
        <td class="border px-3 py-2">{{ $g->codigopago ?? '-' }}</td>

        {{-- Monto --}}
        <td class="border px-3 py-2 text-right">
          S/ {{ number_format($g->monto_pagar, 2) }}
        </td>

        {{-- Descripción --}}
        <td class="border px-3 py-2">{{ $g->descripcion ?? '-' }}</td>

        {{-- Día de pago --}}
        <td class="border px-3 py-2">
          {{ $g->dia_pago ? \Carbon\Carbon::parse($g->dia_pago)->format('d/m/Y') : '-' }}
        </td>

        {{-- Link voucher --}}
        <td class="border px-3 py-2">
          @if($g->link_vaucher)
            <a href="{{ $g->link_vaucher }}" target="_blank"
               class="text-indigo-600 hover:underline">Ver</a>
          @else
            -
          @endif
        </td>

        {{-- Pago verificado --}}
        <td class="border px-3 py-2 text-center">
          @if($g->pago_verificado)
            <span class="text-green-600 font-semibold">✔</span>
          @else
            <span class="text-gray-400">✖</span>
          @endif
        </td>

        {{-- Acciones --}}
        <td class="border px-3 py-2 text-center space-x-2">
  {{-- BOTÓN EDITAR: dispara el evento con todos los datos necesarios --}}
  <button
    type="button"
    class="text-blue-600 hover:underline"
    x-data
    @click="
      $dispatch('edit-gasto', {
        id:            {{ $g->id }},
        categoria_id:  {{ $g->categoria_id ?? 'null' }},
        member_profile_id: {{ $g->team_member_profile_id ?? 'null' }},
        mes:           '{{ $g->mes }}',
        anio:          '{{ $g->anio }}',
        codigopago:    @js($g->codigopago),
        descripcion:   @js($g->descripcion),
        dia_pago:      '{{ $g->dia_pago ?? '' }}',
        link_vaucher:  @js($g->link_vaucher),
        monto_pagar:   '{{ $g->monto_pagar }}',
        pago_verificado: {{ $g->pago_verificado ? 'true' : 'false' }},
        update_url:    '{{ route('gastos.update', $g) }}'
      })
    "
  >
    Editar
  </button>

  {{-- Eliminar (igual que antes) --}}
  <form action="{{ route('gastos.destroy', $g) }}" method="POST" class="inline"
        onsubmit="return confirm('¿Eliminar este gasto?');">
    @csrf @method('DELETE')
    <button class="text-red-600 hover:underline">Eliminar</button>
  </form>
</td>

      </tr>
    @empty
      <tr><td colspan="10" class="text-center py-4 text-gray-500">No hay registros.</td></tr>
    @endforelse
  </tbody>
</table>
{{-- MODAL EDITAR GASTO --}}
<div
  x-data="gastoEditor()"
  x-on:edit-gasto.window="open($event.detail)"
  x-cloak
>
  {{-- Backdrop --}}
  <div
    x-show="openModal"
    x-transition.opacity
    class="fixed inset-0 z-[60] bg-black/40"
    @click="close()"
  ></div>

  {{-- Modal --}}
  <div
    x-show="openModal"
    x-transition
    class="fixed z-[70] inset-0 grid place-items-center p-4"
    aria-modal="true"
    role="dialog"
  >
    <div class="w-full max-w-2xl bg-white rounded-xl shadow-xl">
      <div class="flex items-center justify-between px-5 py-4 border-b">
        <h3 class="text-lg font-semibold">Editar gasto</h3>
        <button class="p-2 rounded hover:bg-gray-100" @click="close()">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <form :action="form.update_url" method="POST" class="p-5 space-y-4">
        @csrf
        @method('PUT')

        {{-- Fila 1: Unidad / Categoría --}}
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Unidad / Perfil</label>
            <select name="team_member_profile_id" x-model="form.member_profile_id"
                    class="w-full border rounded px-3 py-2">
              <option :value="null">General (sin unidad)</option>
              @foreach ($perfiles as $perfil)
                <option value="{{ $perfil->id }}">{{ $perfil->unidad }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Categoría</label>
            <select name="categoria_id" x-model="form.categoria_id"
                    class="w-full border rounded px-3 py-2" required>
              <option value="">Seleccione…</option>
              @foreach ($categorias as $c)
                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Fila 2: Mes / Año --}}
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Mes</label>
            <select name="mes" x-model="form.mes" class="w-full border rounded px-3 py-2" required>
              @foreach (range(1,12) as $m)
                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->monthName }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Año</label>
            <input type="number" name="anio" x-model="form.anio"
                   class="w-full border rounded px-3 py-2" min="2000" max="2100" required>
          </div>
        </div>

        {{-- Fila 3: Monto / Código --}}
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Monto (S/)</label>
            <input type="number" step="0.01" name="monto_pagar" x-model="form.monto_pagar"
                   class="w-full border rounded px-3 py-2 text-right" required>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Código de pago</label>
            <input type="text" name="codigopago" x-model="form.codigopago"
                   class="w-full border rounded px-3 py-2">
          </div>
        </div>

        {{-- Fila 4: Día / Voucher --}}
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Día de pago</label>
            <input type="date" name="dia_pago" x-model="form.dia_pago"
                   class="w-full border rounded px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Link del voucher</label>
            <input type="url" name="link_vaucher" x-model="form.link_vaucher"
                   class="w-full border rounded px-3 py-2" placeholder="https://…">
          </div>
        </div>

        {{-- Descripción --}}
        <div>
          <label class="block text-sm font-medium mb-1">Descripción</label>
          <textarea name="descripcion" x-model="form.descripcion"
                    class="w-full border rounded px-3 py-2" rows="3"
                    placeholder="Detalles del gasto (opcional)"></textarea>
        </div>

        {{-- Verificado --}}
        {{-- Verificado --}}
<div class="flex items-center gap-2">
  {{-- Siempre envía 0 si el checkbox está desmarcado --}}
  <input type="hidden" name="pago_verificado" value="0">
  <input id="pago_verificado"
         type="checkbox"
         name="pago_verificado"
         value="1"
         x-model="form.pago_verificado"
         class="rounded border-gray-300">
  <label for="pago_verificado" class="text-sm">Pago verificado</label>
</div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" class="px-4 py-2 rounded border hover:bg-gray-50" @click="close()">Cancelar</button>
          <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
            Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Alpine state --}}
  <script>
    function gastoEditor() {
      return {
        openModal: false,
        form: {
          id: null,
          update_url: '',
          categoria_id: '',
          member_profile_id: null,
          mes: '',
          anio: '',
          codigopago: '',
          descripcion: '',
          dia_pago: '',
          link_vaucher: '',
          monto_pagar: '',
          pago_verificado: false,
        },
        open(payload) {
          // Copia los datos al form
          this.form = {
            id: payload.id ?? null,
            update_url: payload.update_url ?? '',
            categoria_id: payload.categoria_id ?? '',
            member_profile_id: payload.member_profile_id ?? null,
            mes: String(payload.mes ?? ''),
            anio: String(payload.anio ?? ''),
            codigopago: payload.codigopago ?? '',
            descripcion: payload.descripcion ?? '',
            dia_pago: payload.dia_pago ?? '',
            link_vaucher: payload.link_vaucher ?? '',
            monto_pagar: payload.monto_pagar ?? '',
            pago_verificado: !!payload.pago_verificado,
          };
          this.openModal = true;
        },
        close() {
          this.openModal = false;
        },
      }
    }
  </script>
</div>


        </div>

        <div class="mt-4">{{ $gastos->links() }}</div>

      </div>
    </div>
  </div>
</x-app-layout>
