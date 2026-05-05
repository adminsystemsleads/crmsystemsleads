<x-app-layout>
<div class="max-w-3xl mx-auto px-4 py-8">

  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('team.license.form', $team) }}" class="text-gray-400 hover:text-gray-600 transition">
      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl font-bold text-gray-900">Pagar licencia con tarjeta</h1>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
  @endif

  @if(!$config['configured'])
    <div class="mb-4 rounded-lg bg-amber-50 border border-amber-300 px-4 py-3 text-sm text-amber-800">
      <strong>⚠ Culqi no está configurado.</strong> Define <code class="font-mono">CULQI_PUBLIC_KEY</code> y
      <code class="font-mono">CULQI_SECRET_KEY</code> en el archivo <code class="font-mono">.env</code>.
    </div>
  @endif

  {{-- Resumen del plan --}}
  <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6 shadow-sm">
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div>
        <p class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Plan</p>
        <p class="text-lg font-bold text-gray-900">{{ $config['plan_name'] }}</p>
      </div>
      <div class="text-right">
        <p class="text-xs text-gray-400">Total a pagar</p>
        <p class="text-2xl font-bold text-indigo-600" id="totalDisplay">
          {{ $config['currency'] }} {{ number_format($config['amount']/100, 2) }}
        </p>
      </div>
    </div>
  </div>

  {{-- Formulario de pago --}}
  <form id="paymentForm" method="POST" action="{{ route('payments.process', $team) }}"
        class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
    @csrf
    <input type="hidden" name="token" id="culqiToken">

    <div>
      <label class="block text-xs font-semibold text-gray-600 mb-1">Correo electrónico *</label>
      <input type="email" name="email" id="payerEmail" required
             value="{{ Auth::user()->email }}"
             class="w-full rounded-lg border-gray-200 text-sm py-2">
    </div>

    <div>
      <label class="block text-xs font-semibold text-gray-600 mb-1">Meses a pagar</label>
      <select name="months" id="monthsSelect"
              class="w-full rounded-lg border-gray-200 text-sm py-2">
        @foreach([1=>'1 mes', 3=>'3 meses', 6=>'6 meses', 12=>'12 meses'] as $val => $label)
          <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <button type="button" id="payBtn"
            @if(!$config['configured']) disabled @endif
            class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h2m4 0h2m-9 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
      </svg>
      Pagar con tarjeta (Culqi)
    </button>

    <p class="text-xs text-center text-gray-400">
      🔒 Pago seguro procesado por <a href="https://culqi.com" target="_blank" class="underline">Culqi</a>.
      Aceptamos Visa, Mastercard, Amex y Diners.
    </p>
  </form>

  {{-- Historial de pagos --}}
  @if($payments->isNotEmpty())
    <div class="mt-8 bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
      <h2 class="text-sm font-bold text-gray-800 mb-3">Historial de pagos</h2>
      <table class="w-full text-xs">
        <thead>
          <tr class="bg-gray-50 border-b">
            <th class="px-3 py-2 text-left font-semibold text-gray-600">Fecha</th>
            <th class="px-3 py-2 text-left font-semibold text-gray-600">Meses</th>
            <th class="px-3 py-2 text-right font-semibold text-gray-600">Monto</th>
            <th class="px-3 py-2 text-center font-semibold text-gray-600">Estado</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($payments as $p)
            @php
              $colors = ['paid'=>'bg-green-100 text-green-700','failed'=>'bg-red-100 text-red-700','pending'=>'bg-yellow-100 text-yellow-700','refunded'=>'bg-gray-100 text-gray-500'];
              $labels = ['paid'=>'Pagado','failed'=>'Fallido','pending'=>'Pendiente','refunded'=>'Reembolsado'];
            @endphp
            <tr>
              <td class="px-3 py-2 text-gray-600">{{ $p->created_at->format('d/m/Y H:i') }}</td>
              <td class="px-3 py-2 text-gray-600">{{ $p->months }}</td>
              <td class="px-3 py-2 text-right font-medium text-gray-900">
                {{ $p->currency }} {{ number_format($p->amount, 2) }}
              </td>
              <td class="px-3 py-2 text-center">
                <span class="inline-flex rounded-full px-2 py-0.5 font-semibold {{ $colors[$p->status] ?? 'bg-gray-100' }}">
                  {{ $labels[$p->status] ?? $p->status }}
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

@if($config['configured'])
<script src="https://checkout.culqi.com/js/v4"></script>
<script>
  const PLAN_AMOUNT   = {{ $config['amount'] }};      // céntimos
  const PLAN_CURRENCY = '{{ $config['currency'] }}';
  const PLAN_NAME     = @json($config['plan_name']);

  Culqi.publicKey = '{{ $config['public_key'] }}';

  function setupCulqi() {
    const months = parseInt(document.getElementById('monthsSelect').value, 10) || 1;
    const total  = PLAN_AMOUNT * months;
    Culqi.settings({
      title:    'QipuCRM',
      currency: PLAN_CURRENCY,
      description: PLAN_NAME + ' x ' + months + ' mes(es)',
      amount:   total,
    });
    document.getElementById('totalDisplay').textContent =
      PLAN_CURRENCY + ' ' + (total/100).toFixed(2);
  }

  document.getElementById('monthsSelect').addEventListener('change', setupCulqi);
  setupCulqi();

  document.getElementById('payBtn').addEventListener('click', () => {
    setupCulqi();
    Culqi.open();
  });

  // Callback global que Culqi invoca al cerrar el modal
  window.culqi = function () {
    if (Culqi.token) {
      document.getElementById('culqiToken').value = Culqi.token.id;
      document.getElementById('paymentForm').submit();
    } else if (Culqi.error) {
      alert('Error Culqi: ' + (Culqi.error.user_message || Culqi.error.merchant_message || 'Pago no completado'));
    }
  };
</script>
@endif
</x-app-layout>
