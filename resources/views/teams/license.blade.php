{{-- resources/views/teams/license.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Licencia — {{ $team->name }}
    </h2>
  </x-slot>

  <div class="max-w-2xl mx-auto mt-8 bg-white p-6 rounded-lg shadow">
    @if (session('error'))
      <div class="mb-3 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded">
        {{ session('error') }}
      </div>
    @endif
    {{-- Mensajes --}}
@if (session('success'))
  <div class="mb-3 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded">
    {{ session('success') }}
  </div>
@endif

@php
    // Variables de conveniencia
    $isValid = $status['valid'] ?? false;
    $reason  = $status['reason'] ?? 'paid'; // 'trial' | 'paid'
    $grant   = $license?->grant_type;        // 'license' | 'trial' | 'prorroga' | null

    // Días restantes hasta el vencimiento (puede ser float; negativo si ya venció)
    $remaining = $isValid ? $license?->remaining_days : null;
    $soonThreshold = 3; // "por vencer" cuando faltan 3 días o menos

    // Etiqueta base según el tipo de licencia
    $baseLabel = match ($grant) {
        'trial'    => 'Modo de Prueba activo',
        'prorroga' => 'Prórroga activa',
        'license'  => 'Licencia activa',
        default    => ($reason === 'trial' ? 'Modo de Prueba activo' : 'Licencia activa'),
    };

    if (! $isValid) {
        $estadoLabel = $license ? 'Licencia vencida' : 'Sin licencia activa';
        $estadoPill  = 'bg-red-100 text-red-700';
    } elseif ($remaining !== null && $remaining <= $soonThreshold) {
        $dias = (int) ceil($remaining);
        $venceTxt = $dias <= 0 ? 'vence hoy' : 'faltan ' . $dias . ' ' . ($dias === 1 ? 'día' : 'días');
        $estadoLabel = $baseLabel . ' · Por vencer (' . $venceTxt . ')';
        $estadoPill  = 'bg-amber-100 text-amber-700';
    } else {
        $estadoLabel = $baseLabel;
        $estadoPill  = 'bg-green-100 text-green-700';
    }
@endphp


    <div class="mb-4 text-sm text-gray-700">
  <p class="flex items-center gap-2"><b>Estado:</b>
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $estadoPill }}">
      {{ $estadoLabel }}
    </span>
  </p>

  <p class="mt-1"><b>Vence:</b>
    {{ $license?->expires_at ? $license->expires_at->copy()->setTimezone($team->effectiveTimezone())->format('Y-m-d H:i') : '-' }}
    <span class="text-xs text-gray-400">({{ $team->effectiveTimezone() }})</span>
  </p>
</div>

    {{-- Pagar con tarjeta (Culqi) --}}
    <div class="mb-6 p-4 rounded-lg border-2 border-indigo-200 bg-gradient-to-br from-indigo-50 to-violet-50">
      <div class="flex items-start gap-4">
        <div class="size-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white shrink-0">
          <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h2m4 0h2m-9 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-bold text-gray-900 mb-1">Pagar con tarjeta</h3>
          <p class="text-xs text-gray-600 mb-3">
            Renueva o activa tu licencia pagando directamente con tarjeta Visa, Mastercard, Amex o Diners.
            Pago seguro vía Culqi.
          </p>
          <a href="{{ route('payments.checkout', $team) }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
            Ir a pagar
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
          </a>
        </div>
      </div>
    </div>

    {{-- Activar con código de licencia / prueba --}}
    <div class="p-4 rounded-lg border-2 border-gray-200 bg-gray-50">
      <h3 class="text-sm font-bold text-gray-900 mb-1">Activar con un código</h3>
      <p class="text-xs text-gray-600 mb-3">
        Ingresa el código que recibiste para activar tu <b>licencia</b> o tu <b>periodo de prueba</b>.
      </p>

      <form method="POST" action="{{ route('team.license.activate', $team) }}" class="space-y-3">
        @csrf
        <label class="block text-sm font-medium text-gray-700">Código de licencia</label>
        <input type="text" name="license_key"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 font-mono uppercase tracking-wide"
               placeholder="SL-XXXX-XXXX-XXXX" required>

        <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-900 transition">
          Activar
        </button>
      </form>
    </div>
  </div>

  {{-- Reporte de códigos utilizados en esta cuenta --}}
  <div class="max-w-2xl mx-auto mt-6 bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
      <h3 class="text-sm font-bold text-gray-900">Códigos utilizados</h3>
      <p class="text-xs text-gray-500">Historial de códigos canjeados en esta cuenta.</p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
          <tr>
            <th class="text-left px-6 py-3 font-semibold">Código</th>
            <th class="text-left px-6 py-3 font-semibold">Tipo</th>
            <th class="text-left px-6 py-3 font-semibold">Canjeado</th>
            <th class="text-left px-6 py-3 font-semibold">Vence</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @php $tz = $team->effectiveTimezone(); @endphp
          @forelse ($redeemedCodes as $code)
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-3 font-mono font-semibold text-gray-900">{{ $code->code }}</td>
              <td class="px-6 py-3 text-gray-700">{{ $code->type_label }} · {{ $code->duration_label }}</td>
              <td class="px-6 py-3 text-gray-700">
                {{ $code->redeemed_at ? $code->redeemed_at->copy()->setTimezone($tz)->format('Y-m-d H:i') : '—' }}
              </td>
              <td class="px-6 py-3 text-gray-700">
                {{ optional($code->activated_until)->format('Y-m-d H:i') ?? '—' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm">
                Aún no se ha canjeado ningún código en esta cuenta.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</x-app-layout>
