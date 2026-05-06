<x-app-layout>
<div class="max-w-3xl mx-auto px-4 py-8">

  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('team.license.form', $team) }}" class="text-gray-400 hover:text-gray-600 transition">
      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-xl font-bold text-gray-900">Suscripción</h1>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @if(!$configured)
    <div class="mb-4 rounded-lg bg-amber-50 border border-amber-300 px-4 py-3 text-sm text-amber-800">
      <strong>⚠ Culqi no está configurado.</strong> Define <code class="font-mono">CULQI_PUBLIC_KEY</code> y
      <code class="font-mono">CULQI_SECRET_KEY</code> en el archivo <code class="font-mono">.env</code>.
    </div>
  @endif

  {{-- Suscripción activa --}}
  @if($subscription && $subscription->isActive())
    <div class="mb-6 bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-5">
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="size-2 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-xs uppercase font-bold tracking-wider text-green-700">{{ $subscription->status_label }}</span>
          </div>
          <h2 class="text-lg font-bold text-gray-900">{{ $subscription->plan->name }}</h2>
          <p class="text-sm text-gray-600 mt-1">
            {{ $subscription->plan->currency }} {{ number_format($subscription->plan->amount, 2) }}
            / {{ $subscription->plan->interval_label }}
          </p>
          @if($subscription->card_last4)
            <p class="text-xs text-gray-500 mt-1">
              💳 {{ $subscription->card_brand }} •••• {{ $subscription->card_last4 }}
            </p>
          @endif
          @if($subscription->current_period_end)
            <p class="text-xs text-gray-500 mt-1">
              Próximo cobro: <strong>{{ $subscription->current_period_end->format('d/m/Y') }}</strong>
            </p>
          @endif
        </div>
        <form method="POST" action="{{ route('payments.cancel', [$team, $subscription]) }}"
              onsubmit="return confirm('¿Cancelar la suscripción? La licencia seguirá activa hasta el fin del periodo actual.')">
          @csrf @method('DELETE')
          <button type="submit"
                  class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 text-xs font-medium hover:bg-red-50 transition">
            Cancelar suscripción
          </button>
        </form>
      </div>
    </div>
  @endif

  {{-- Planes disponibles --}}
  @if(!$subscription || !$subscription->isActive())
    <div class="space-y-4 mb-6">
      <h2 class="text-sm font-bold text-gray-700">Elige tu plan</h2>

      @forelse($plans as $plan)
        <label class="block bg-white border-2 border-gray-200 rounded-xl p-5 cursor-pointer hover:border-indigo-400 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50">
          <div class="flex items-start gap-3">
            <input type="radio" name="plan_radio" value="{{ $plan->id }}"
                   {{ $loop->first ? 'checked' : '' }}
                   onchange="document.getElementById('hiddenPlanId').value=this.value"
                   class="mt-1 text-indigo-600">
            <div class="flex-1">
              <div class="flex items-center justify-between flex-wrap gap-2">
                <h3 class="font-bold text-gray-900">{{ $plan->name }}</h3>
                <p class="text-2xl font-bold text-indigo-600">
                  {{ $plan->currency }} {{ number_format($plan->amount, 2) }}
                  <span class="text-xs font-normal text-gray-500">/ {{ $plan->interval_label }}</span>
                </p>
              </div>
              @if($plan->description)
                <p class="text-xs text-gray-500 mt-1">{{ $plan->description }}</p>
              @endif
              @if(is_array($plan->features) && count($plan->features))
                <ul class="mt-3 space-y-1">
                  @foreach($plan->features as $f)
                    <li class="flex items-center gap-1.5 text-xs text-gray-600">
                      <svg class="size-3.5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                      {{ $f }}
                    </li>
                  @endforeach
                </ul>
              @endif
            </div>
          </div>
        </label>
      @empty
        <div class="text-sm text-gray-400 py-8 text-center bg-white border border-dashed rounded-xl">
          No hay planes activos.
        </div>
      @endforelse
    </div>

    {{-- Datos del titular y formulario --}}
    @if($plans->isNotEmpty())
      <form id="subForm" method="POST" action="{{ route('payments.subscribe', $team) }}"
            class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
        @csrf
        <input type="hidden" name="token" id="culqiToken">
        <input type="hidden" name="plan_id" id="hiddenPlanId" value="{{ $plans->first()->id }}">

        <h2 class="text-sm font-bold text-gray-700">Datos del titular</h2>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nombres *</label>
            <input type="text" name="first_name" required maxlength="100"
                   value="{{ old('first_name', explode(' ', Auth::user()->name)[0] ?? '') }}"
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Apellidos *</label>
            <input type="text" name="last_name" required maxlength="100"
                   value="{{ old('last_name', explode(' ', Auth::user()->name, 2)[1] ?? Auth::user()->name) }}"
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Correo *</label>
            <input type="email" name="email" id="payerEmail" required
                   value="{{ old('email', Auth::user()->email) }}"
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Teléfono</label>
            <input type="text" name="phone" maxlength="30" value="{{ old('phone') }}"
                   placeholder="+51 999 999 999"
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div class="col-span-2">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Dirección</label>
            <input type="text" name="address" maxlength="200" value="{{ old('address', 'Av. Lima 123') }}"
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Ciudad</label>
            <input type="text" name="city" maxlength="100" value="{{ old('city', 'LIMA') }}"
                   class="w-full rounded-lg border-gray-200 text-sm py-2">
          </div>
        </div>

        <input type="hidden" name="country_code" value="PE">

        <button type="button" id="payBtn"
                @if(!$configured) disabled @endif
                class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
          <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h2m4 0h2m-9 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          Suscribirme y pagar con tarjeta
        </button>

        <p class="text-xs text-center text-gray-400">
          🔒 Pago y renovación automática gestionados por <a href="https://culqi.com" target="_blank" class="underline">Culqi</a>.
          Puedes cancelar cuando quieras.
        </p>
      </form>
    @endif
  @endif

  {{-- Historial de pagos --}}
  @if($payments->isNotEmpty())
    <div class="mt-8 bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
      <h2 class="text-sm font-bold text-gray-800 mb-3">Historial de pagos</h2>
      <table class="w-full text-xs">
        <thead>
          <tr class="bg-gray-50 border-b">
            <th class="px-3 py-2 text-left font-semibold text-gray-600">Fecha</th>
            <th class="px-3 py-2 text-left font-semibold text-gray-600">Concepto</th>
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
              <td class="px-3 py-2 text-gray-700">{{ $p->description ?: '—' }}</td>
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

@if($configured && (!$subscription || !$subscription->isActive()) && $plans->isNotEmpty())
<script src="https://checkout.culqi.com/js/v4"></script>
<script>
  Culqi.publicKey = @json($publicKey);
  const PLANS = @json($plans->map(fn($p) => [
    'id' => $p->id,
    'amount' => $p->amount_cents,
    'currency' => $p->currency,
    'name' => $p->name,
  ])->keyBy('id')->all());

  function getSelectedPlan() {
    const id = document.getElementById('hiddenPlanId').value;
    return PLANS[id];
  }

  function setupCulqi() {
    const plan = getSelectedPlan();
    if (!plan) return;
    Culqi.settings({
      title: 'QipuCRM',
      currency: plan.currency,
      description: plan.name + ' (suscripción)',
      amount: plan.amount,
    });
  }

  document.getElementById('payBtn').addEventListener('click', () => {
    setupCulqi();
    Culqi.open();
  });

  window.culqi = function () {
    if (Culqi.token) {
      document.getElementById('culqiToken').value = Culqi.token.id;
      document.getElementById('subForm').submit();
    } else if (Culqi.error) {
      alert('Error Culqi: ' + (Culqi.error.user_message || Culqi.error.merchant_message || 'Pago no completado'));
    }
  };
</script>
@endif
</x-app-layout>
