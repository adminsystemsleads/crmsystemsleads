<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $invoice->tipo_nombre }} {{ $invoice->numero }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111; background: #fff; padding: 20px; }
    .doc { max-width: 750px; margin: 0 auto; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1e40af; padding-bottom: 16px; margin-bottom: 16px; }
    .company-name { font-size: 16px; font-weight: bold; color: #1e40af; }
    .doc-box { border: 2px solid #1e40af; padding: 12px 20px; text-align: center; min-width: 200px; }
    .doc-box .tipo { font-weight: bold; font-size: 13px; }
    .doc-box .num  { font-family: monospace; font-size: 14px; font-weight: bold; }
    .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
    .card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; }
    .card h3 { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: 6px; border-bottom: 1px solid #f3f4f6; padding-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table th { background: #f3f4f6; text-align: left; padding: 8px 10px; font-size: 11px; color: #374151; }
    table td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .totals { width: 260px; margin-left: auto; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; }
    .totals .row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 12px; color: #374151; }
    .totals .row.total { border-top: 1px solid #d1d5db; margin-top: 4px; padding-top: 6px; font-weight: bold; font-size: 14px; color: #111; }
    .footer { text-align: center; font-size: 10px; color: #9ca3af; margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    .badge-accepted { background: #dcfce7; color: #166534; }
    .badge-draft    { background: #f3f4f6; color: #374151; }
    @media print {
      body { padding: 0; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>
<div class="doc">

  {{-- Print button --}}
  <div class="no-print" style="text-align:right; margin-bottom:12px;">
    <button onclick="window.print()"
            style="padding:6px 16px; background:#1d4ed8; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:13px;">
      Imprimir / Guardar PDF
    </button>
  </div>

  {{-- Header --}}
  <div class="header">
    <div>
      @if($config)
        <div class="company-name">{{ $config->razon_social }}</div>
        @if($config->nombre_comercial)
          <div style="color:#4b5563; font-size:11px;">{{ $config->nombre_comercial }}</div>
        @endif
        <div style="color:#6b7280; font-size:11px; margin-top:4px;">RUC: {{ $config->ruc }}</div>
        <div style="color:#6b7280; font-size:11px;">{{ $config->direccion }}</div>
        <div style="color:#6b7280; font-size:11px;">{{ $config->distrito }}, {{ $config->provincia }}</div>
      @endif
    </div>
    <div class="doc-box">
      <div style="font-size:10px; color:#6b7280; margin-bottom:4px;">COMPROBANTE ELECTRÓNICO</div>
      <div class="tipo">{{ strtoupper($invoice->tipo_nombre) }}</div>
      <div class="num">{{ $invoice->numero }}</div>
      <div style="margin-top:8px; font-size:11px;">
        @if($invoice->estado === 'accepted')
          <span class="badge badge-accepted">ACEPTADO SUNAT</span>
        @else
          <span class="badge badge-draft">{{ strtoupper($invoice->estado_badge) }}</span>
        @endif
      </div>
    </div>
  </div>

  {{-- Dates --}}
  <div style="display:flex; gap:24px; margin-bottom:16px; font-size:12px;">
    <div><strong>Fecha emisión:</strong> {{ $invoice->fecha_emision->format('d/m/Y') }}</div>
    @if($invoice->fecha_vencimiento)
      <div><strong>Vencimiento:</strong> {{ $invoice->fecha_vencimiento->format('d/m/Y') }}</div>
    @endif
    <div><strong>Moneda:</strong> {{ $invoice->moneda }}</div>
  </div>

  {{-- Parties --}}
  <div class="parties">
    <div class="card">
      <h3>Emisor</h3>
      @if($config)
        <div><strong>{{ $config->razon_social }}</strong></div>
        <div>RUC: {{ $config->ruc }}</div>
      @else
        <div>—</div>
      @endif
    </div>
    <div class="card">
      <h3>Cliente</h3>
      <div><strong>{{ $invoice->cliente_razon_social }}</strong></div>
      <div>
        {{ match($invoice->cliente_tipo_doc) {'1'=>'DNI','6'=>'RUC','4'=>'CE',default=>'Doc'} }}:
        {{ $invoice->cliente_num_doc }}
      </div>
      @if($invoice->cliente_direccion)
        <div style="color:#6b7280; font-size:11px;">{{ $invoice->cliente_direccion }}</div>
      @endif
    </div>
  </div>

  {{-- Items --}}
  <table>
    <thead>
      <tr>
        <th>Descripción</th>
        <th class="text-center">Unidad</th>
        <th class="text-right">Cantidad</th>
        <th class="text-right">P. Unitario</th>
        <th class="text-right">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $item)
        <tr>
          <td>{{ $item->descripcion }}</td>
          <td class="text-center">{{ $item->unidad }}</td>
          <td class="text-right">{{ number_format($item->cantidad, 2) }}</td>
          <td class="text-right">{{ number_format($item->precio_unitario, 2) }}</td>
          <td class="text-right">{{ number_format($item->total, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Totales --}}
  <div class="totals">
    <div class="row"><span>Op. Gravadas</span><span>{{ $invoice->moneda }} {{ number_format($invoice->op_gravadas, 2) }}</span></div>
    @if($invoice->op_exoneradas > 0)
      <div class="row"><span>Op. Exoneradas</span><span>{{ $invoice->moneda }} {{ number_format($invoice->op_exoneradas, 2) }}</span></div>
    @endif
    @if($invoice->op_inafectas > 0)
      <div class="row"><span>Op. Inafectas</span><span>{{ $invoice->moneda }} {{ number_format($invoice->op_inafectas, 2) }}</span></div>
    @endif
    <div class="row"><span>IGV ({{ $invoice->igv_porcentaje }}%)</span><span>{{ $invoice->moneda }} {{ number_format($invoice->igv, 2) }}</span></div>
    <div class="row total"><span>TOTAL</span><span>{{ $invoice->moneda }} {{ number_format($invoice->total, 2) }}</span></div>
  </div>

  @if($invoice->observaciones)
    <div style="margin-top:16px; font-size:11px; color:#374151;">
      <strong>Observaciones:</strong> {{ $invoice->observaciones }}
    </div>
  @endif

  <div class="footer">
    Representación impresa del comprobante electrónico &bull; {{ $invoice->tipo_nombre }} {{ $invoice->numero }}
  </div>

</div>
</body>
</html>
