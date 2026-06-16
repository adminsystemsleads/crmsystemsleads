<x-app-layout>
<div class="max-w-4xl mx-auto px-4 py-8">

  {{-- Header --}}
  <div class="flex items-center gap-3 mb-6 flex-wrap">
    <a href="{{ route('invoices.index') }}" class="text-gray-400 hover:text-gray-600 transition">
      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl font-bold text-gray-900">{{ $invoice->tipo_nombre }} {{ $invoice->numero }}</h1>
    @php
      $colors = ['draft'=>'bg-gray-100 text-gray-600','signed'=>'bg-blue-100 text-blue-700',
                 'sent'=>'bg-yellow-100 text-yellow-700','accepted'=>'bg-green-100 text-green-700',
                 'rejected'=>'bg-red-100 text-red-700','cancelled'=>'bg-gray-100 text-gray-400'];
    @endphp
    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colors[$invoice->estado] ?? 'bg-gray-100 text-gray-600' }}">
      {{ $invoice->estado_badge }}
    </span>
    <div class="ml-auto flex gap-2 flex-wrap">
      @if($invoice->deal)
        <a href="{{ route('deals.edit', [$invoice->deal->pipeline_id, $invoice->deal]) }}"
           class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition">
          {{ __('Ver negociación') }}
        </a>
      @endif
      <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
         class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition">
        {{ __('Imprimir / PDF') }}
      </a>
      @if($invoice->xml_path)
        <a href="{{ route('invoices.download-xml', $invoice) }}"
           class="px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600 hover:bg-gray-50 transition">
          {{ __('Descargar XML') }}
        </a>
      @endif
    </div>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
  @endif

  @if($invoice->sunat_description)
    <div class="mb-4 rounded-lg {{ $invoice->estado === 'accepted' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-amber-50 border-amber-200 text-amber-800' }} border px-4 py-3 text-sm">
      <strong>SUNAT {{ $invoice->sunat_code }}:</strong> {{ $invoice->sunat_description }}
      @if($invoice->sunat_notes)
        <br><span class="text-xs">{{ $invoice->sunat_notes }}</span>
      @endif
    </div>
  @endif

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

    {{-- Info comprobante --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-2 text-sm">
      <h2 class="font-bold text-gray-800 border-b pb-2 text-xs uppercase tracking-wide text-gray-500">{{ __('Comprobante') }}</h2>
      <div class="flex justify-between"><span class="text-gray-500">{{ __('Número') }}</span><span class="font-mono font-semibold">{{ $invoice->numero }}</span></div>
      <div class="flex justify-between"><span class="text-gray-500">{{ __('Emisión') }}</span><span>{{ $invoice->fecha_emision->format('d/m/Y') }}</span></div>
      @if($invoice->fecha_vencimiento)
        <div class="flex justify-between"><span class="text-gray-500">{{ __('Vencimiento') }}</span><span>{{ $invoice->fecha_vencimiento->format('d/m/Y') }}</span></div>
      @endif
      <div class="flex justify-between"><span class="text-gray-500">{{ __('Moneda') }}</span><span>{{ $invoice->moneda }}</span></div>
      <div class="flex justify-between"><span class="text-gray-500">{{ __('IGV') }}</span><span>{{ $invoice->igv_porcentaje }}%</span></div>
    </div>

    {{-- Emisor --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-sm space-y-1">
      <h2 class="font-bold text-gray-800 border-b pb-2 text-xs uppercase tracking-wide text-gray-500">{{ __('Emisor') }}</h2>
      @if($config)
        <p class="font-semibold text-gray-900">{{ $config->razon_social }}</p>
        <p class="text-gray-500">{{ __('RUC:') }} {{ $config->ruc }}</p>
        <p class="text-gray-500 text-xs">{{ $config->direccion }}</p>
        <p class="text-gray-500 text-xs">{{ $config->distrito }}, {{ $config->provincia }}</p>
      @else
        <p class="text-gray-400 text-xs">{{ __('Sin configuración de emisor.') }}</p>
      @endif
    </div>

    {{-- Cliente --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-sm space-y-1">
      <h2 class="font-bold text-gray-800 border-b pb-2 text-xs uppercase tracking-wide text-gray-500">{{ __('Cliente') }}</h2>
      <p class="font-semibold text-gray-900">{{ $invoice->cliente_razon_social }}</p>
      <p class="text-gray-500">
        {{ match($invoice->cliente_tipo_doc) {'1'=>__('DNI'),'6'=>__('RUC'),'4'=>__('CE'),default=>__('Doc')} }}:
        {{ $invoice->cliente_num_doc }}
      </p>
      @if($invoice->cliente_direccion)
        <p class="text-gray-500 text-xs">{{ $invoice->cliente_direccion }}</p>
      @endif
    </div>
  </div>

  {{-- Items table --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-gray-50 border-b border-gray-200">
          <th class="px-4 py-3 text-left font-semibold text-gray-600">{{ __('Descripción') }}</th>
          <th class="px-4 py-3 text-center font-semibold text-gray-600">{{ __('Und.') }}</th>
          <th class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Cant.') }}</th>
          <th class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('P.Unit') }}</th>
          <th class="px-4 py-3 text-right font-semibold text-gray-600">{{ __('Total') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($invoice->items as $item)
          <tr>
            <td class="px-4 py-3 text-gray-900">{{ $item->descripcion }}</td>
            <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ $item->unidad }}</td>
            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->cantidad, 2) }}</td>
            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->precio_unitario, 2) }}</td>
            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($item->total, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Totales --}}
    <div class="border-t border-gray-200 p-4 space-y-1 text-sm">
      <div class="flex justify-between text-gray-600 max-w-xs ml-auto">
        <span>{{ __('Op. Gravadas') }}</span>
        <span>{{ $invoice->moneda }} {{ number_format($invoice->op_gravadas, 2) }}</span>
      </div>
      @if($invoice->op_exoneradas > 0)
        <div class="flex justify-between text-gray-600 max-w-xs ml-auto">
          <span>{{ __('Op. Exoneradas') }}</span>
          <span>{{ $invoice->moneda }} {{ number_format($invoice->op_exoneradas, 2) }}</span>
        </div>
      @endif
      @if($invoice->op_inafectas > 0)
        <div class="flex justify-between text-gray-600 max-w-xs ml-auto">
          <span>{{ __('Op. Inafectas') }}</span>
          <span>{{ $invoice->moneda }} {{ number_format($invoice->op_inafectas, 2) }}</span>
        </div>
      @endif
      <div class="flex justify-between text-gray-600 max-w-xs ml-auto">
        <span>{{ __('IGV') }} ({{ $invoice->igv_porcentaje }}%)</span>
        <span>{{ $invoice->moneda }} {{ number_format($invoice->igv, 2) }}</span>
      </div>
      <div class="flex justify-between font-bold text-gray-900 text-base max-w-xs ml-auto border-t pt-1">
        <span>{{ __('TOTAL') }}</span>
        <span>{{ $invoice->moneda }} {{ number_format($invoice->total, 2) }}</span>
      </div>
    </div>
  </div>

  @if($invoice->observaciones)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 text-sm text-gray-600">
      <strong class="text-gray-800">{{ __('Observaciones:') }}</strong> {{ $invoice->observaciones }}
    </div>
  @endif

  {{-- Acciones SUNAT --}}
  @if($config && $config->test_mode)
    <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
      <strong>{{ __('Modo prueba activo') }}</strong> {{ __('— los envíos a SUNAT son simulados. Desactívalo en') }}
      <a href="{{ route('invoice-config.edit') }}" class="underline font-semibold">{{ __('Configuración') }}</a> {{ __('para producción.') }}
    </div>
  @endif

  @if(!in_array($invoice->estado, ['accepted', 'cancelled']))
    <div class="flex gap-3 flex-wrap">
      @if($config && ($config->certificate_pem || $config->test_mode) && $invoice->estado === 'draft')
        <form method="POST" action="{{ route('invoices.sign', $invoice) }}">
          @csrf
          <button type="submit"
                  class="px-4 py-2 rounded-lg border border-blue-300 bg-blue-50 text-blue-700 text-sm font-medium hover:bg-blue-100 transition">
            {{ __('Firmar XML') }}
          </button>
        </form>
      @endif

      @if($config && ($config->sol_user || $config->test_mode) && in_array($invoice->estado, ['draft','signed','rejected']))
        <form method="POST" action="{{ route('invoices.send-sunat', $invoice) }}"
              onsubmit="return confirm('{{ __('¿Enviar a SUNAT?') }}')">
          @csrf
          <button type="submit"
                  class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
            {{ __('Enviar a SUNAT') }}
          </button>
        </form>
      @endif
    </div>
  @endif

</div>
</x-app-layout>
