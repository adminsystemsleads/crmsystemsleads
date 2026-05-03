<x-app-layout>
<div class="max-w-4xl mx-auto px-4 py-8">

  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('deals.edit', [$pipeline, $deal]) }}"
       class="text-gray-400 hover:text-gray-600 transition">
      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl font-bold text-gray-900">Nueva Factura / Boleta</h1>
    <span class="text-sm text-gray-400">— {{ $deal->title }}</span>
  </div>

  @if(!$config)
    <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 px-4 py-4 text-sm text-amber-800">
      <strong>Falta configuración:</strong> antes de emitir comprobantes debes
      <a href="{{ route('invoice-config.edit') }}" class="font-semibold underline">configurar tus datos SUNAT</a>.
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('invoices.store', [$pipeline, $deal]) }}"
        x-data="invoiceForm()" @submit.prevent="submitForm">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

      {{-- Columna izquierda --}}
      <div class="space-y-5">

        {{-- Tipo de comprobante --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-4">
          <h2 class="text-sm font-bold text-gray-800">Comprobante</h2>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo *</label>
              <select name="tipo_doc" x-model="tipoDoc" class="w-full rounded-lg border-gray-200 text-sm py-2">
                <option value="01">Factura</option>
                <option value="03">Boleta de Venta</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Moneda *</label>
              <select name="moneda" x-model="moneda" class="w-full rounded-lg border-gray-200 text-sm py-2">
                <option value="PEN">PEN — Soles</option>
                <option value="USD">USD — Dólares</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Fecha emisión *</label>
              <input type="date" name="fecha_emision" x-model="fechaEmision" required
                     class="w-full rounded-lg border-gray-200 text-sm py-2">
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Fecha vencimiento</label>
              <input type="date" name="fecha_vencimiento"
                     class="w-full rounded-lg border-gray-200 text-sm py-2">
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">% IGV</label>
            <input type="number" name="igv_porcentaje" x-model="igvPct"
                   min="0" max="100" step="0.01" @change="recalculate()"
                   class="w-full rounded-lg border-gray-200 text-sm py-2" value="18">
          </div>
        </div>

        {{-- Datos del cliente --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-4">
          <h2 class="text-sm font-bold text-gray-800">Datos del cliente</h2>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo doc *</label>
              <select name="cliente_tipo_doc" x-model="clienteTipoDoc"
                      class="w-full rounded-lg border-gray-200 text-sm py-2">
                <option value="1">DNI</option>
                <option value="6">RUC</option>
                <option value="4">CE</option>
                <option value="0">Otros</option>
              </select>
            </div>
            <div class="col-span-2">
              <label class="block text-xs font-semibold text-gray-600 mb-1">N° documento *</label>
              <input type="text" name="cliente_num_doc" x-model="clienteNumDoc"
                     required maxlength="15"
                     class="w-full rounded-lg border-gray-200 text-sm py-2">
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Razón social / Nombre *</label>
            <input type="text" name="cliente_razon_social" x-model="clienteRazonSocial"
                   required maxlength="250"
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Dirección</label>
            <input type="text" name="cliente_direccion" maxlength="250"
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Observaciones</label>
            <textarea name="observaciones" rows="2" maxlength="1000"
                      class="w-full rounded-lg border-gray-200 text-sm py-2"></textarea>
          </div>
        </div>
      </div>

      {{-- Columna derecha —— Items --}}
      <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-gray-800">Líneas del comprobante</h2>
            <button type="button" @click="addItem()"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
              + Agregar línea
            </button>
          </div>

          <div class="space-y-3">
            <template x-for="(item, idx) in items" :key="idx">
              <div class="border border-gray-200 rounded-lg p-3 space-y-2 bg-gray-50">
                <div class="flex items-start gap-2">
                  <div class="flex-1">
                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Descripción *</label>
                    <input type="text" :name="`items[${idx}][descripcion]`" x-model="item.descripcion"
                           required maxlength="250"
                           class="w-full rounded border-gray-200 text-xs py-1.5">
                  </div>
                  <button type="button" @click="removeItem(idx)"
                          class="mt-5 text-red-400 hover:text-red-600 transition shrink-0">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>

                <div class="grid grid-cols-4 gap-2">
                  <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Unidad</label>
                    <input type="text" :name="`items[${idx}][unidad]`" x-model="item.unidad"
                           maxlength="10" class="w-full rounded border-gray-200 text-xs py-1.5">
                    <input type="hidden" :name="`items[${idx}][cod_producto]`" x-model="item.cod_producto">
                  </div>
                  <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Cantidad</label>
                    <input type="number" :name="`items[${idx}][cantidad]`" x-model="item.cantidad"
                           min="0.01" step="0.01" @change="recalculate()"
                           class="w-full rounded border-gray-200 text-xs py-1.5">
                  </div>
                  <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">P.Unit (c/IGV)</label>
                    <input type="number" :name="`items[${idx}][precio_unitario]`" x-model="item.precio_unitario"
                           min="0" step="0.01" @change="recalculate()"
                           class="w-full rounded border-gray-200 text-xs py-1.5">
                  </div>
                  <div>
                    <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Afectación</label>
                    <select :name="`items[${idx}][tip_afe_igv]`" x-model="item.tip_afe_igv" @change="recalculate()"
                            class="w-full rounded border-gray-200 text-xs py-1.5">
                      <option value="10">Gravado</option>
                      <option value="20">Exonerado</option>
                      <option value="30">Inafecto</option>
                    </select>
                  </div>
                </div>

                <div class="text-right text-xs text-gray-500">
                  Subtotal: <span class="font-semibold text-gray-800" x-text="moneda + ' ' + itemTotal(item).toFixed(2)"></span>
                </div>
              </div>
            </template>

            <template x-if="items.length === 0">
              <p class="text-center text-xs text-gray-400 py-4">Sin líneas. Agrega al menos una.</p>
            </template>
          </div>

          {{-- Totales --}}
          <div class="mt-4 border-t pt-3 space-y-1 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>Op. Gravadas</span>
              <span x-text="moneda + ' ' + opGravadas.toFixed(2)"></span>
            </div>
            <div class="flex justify-between text-gray-600" x-show="opExoneradas > 0">
              <span>Op. Exoneradas</span>
              <span x-text="moneda + ' ' + opExoneradas.toFixed(2)"></span>
            </div>
            <div class="flex justify-between text-gray-600" x-show="opInafectas > 0">
              <span>Op. Inafectas</span>
              <span x-text="moneda + ' ' + opInafectas.toFixed(2)"></span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>IGV (<span x-text="igvPct"></span>%)</span>
              <span x-text="moneda + ' ' + totalIgv.toFixed(2)"></span>
            </div>
            <div class="flex justify-between font-bold text-gray-900 text-base border-t pt-1">
              <span>TOTAL</span>
              <span x-text="moneda + ' ' + grandTotal.toFixed(2)"></span>
            </div>
          </div>
        </div>

        <div class="flex gap-3 justify-end">
          <a href="{{ route('deals.edit', [$pipeline, $deal]) }}"
             class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">
            Cancelar
          </a>
          <button type="submit"
                  class="px-6 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
            Generar comprobante
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
function invoiceForm() {
  const contact = @json($deal->contact);
  const dealProducts = @json($deal->dealProducts ?? []);

  // Pre-fill items from deal products
  const preItems = dealProducts.map(p => ({
    descripcion:     p.name || '',
    unidad:          p.unit || 'NIU',
    cantidad:        parseFloat(p.quantity) || 1,
    precio_unitario: parseFloat(p.unit_price) || 0,
    tip_afe_igv:     '10',
    cod_producto:    'ZZZ9999999AA',
  }));

  // Pre-fill client from contact
  let tipoDoc   = (contact && contact.tipo_doc) ? contact.tipo_doc : '1';
  let numDoc    = (contact && contact.num_doc)  ? contact.num_doc  : '';
  let razonSoc  = '';
  if (contact) {
    if (contact.razon_social) razonSoc = contact.razon_social;
    else if (contact.company)  razonSoc = contact.company;
    else razonSoc = contact.name || '';
  }

  return {
    tipoDoc:          '01',
    moneda:           '{{ $deal->currency ?? "PEN" }}',
    fechaEmision:     '{{ now()->format("Y-m-d") }}',
    igvPct:           18,
    clienteTipoDoc:   tipoDoc,
    clienteNumDoc:    numDoc,
    clienteRazonSocial: razonSoc,
    items:            preItems.length ? preItems : [{
      descripcion: '', unidad: 'NIU', cantidad: 1, precio_unitario: 0, tip_afe_igv: '10', cod_producto: 'ZZZ9999999AA',
    }],
    opGravadas:   0,
    opExoneradas: 0,
    opInafectas:  0,
    totalIgv:     0,
    grandTotal:   0,

    addItem() {
      this.items.push({ descripcion: '', unidad: 'NIU', cantidad: 1, precio_unitario: 0, tip_afe_igv: '10', cod_producto: 'ZZZ9999999AA' });
      this.recalculate();
    },
    removeItem(idx) {
      this.items.splice(idx, 1);
      this.recalculate();
    },
    itemTotal(item) {
      return parseFloat(item.cantidad) * parseFloat(item.precio_unitario) || 0;
    },
    recalculate() {
      let grav = 0, exon = 0, inaf = 0, igv = 0;
      const pct = parseFloat(this.igvPct) / 100;
      for (const item of this.items) {
        const qty   = parseFloat(item.cantidad) || 0;
        const price = parseFloat(item.precio_unitario) || 0;
        const total = qty * price;
        if (item.tip_afe_igv === '10') {
          const valorVenta = total / (1 + pct);
          grav += valorVenta;
          igv  += valorVenta * pct;
        } else if (item.tip_afe_igv === '20') {
          exon += total;
        } else {
          inaf += total;
        }
      }
      this.opGravadas   = Math.round(grav * 100) / 100;
      this.opExoneradas = Math.round(exon * 100) / 100;
      this.opInafectas  = Math.round(inaf * 100) / 100;
      this.totalIgv     = Math.round(igv  * 100) / 100;
      this.grandTotal   = Math.round((grav + exon + inaf + igv) * 100) / 100;
    },
    submitForm() {
      if (this.items.length === 0) {
        alert('Debes agregar al menos una línea.');
        return;
      }
      this.$el.submit();
    },
    init() { this.recalculate(); },
  };
}
</script>
</x-app-layout>
