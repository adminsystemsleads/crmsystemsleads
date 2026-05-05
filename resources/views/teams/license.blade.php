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
@endphp


    <div class="mb-4 text-sm text-gray-700">
  <p><b>Estado:</b>
    <span class="{{ $isValid ? 'text-green-600' : 'text-red-600' }}">
      {{ $isValid ? ($reason === 'trial' ? 'Prueba' : 'Activa') : 'Vencida' }}
    </span>
  </p>

  <p><b>Inicio:</b>
    {{ optional($license?->starts_at)->format('Y-m-d') ?? '-' }}
  </p>

  <p><b>Vence:</b>
    {{ optional($license?->active_until)->format('Y-m-d') ?? '-' }}
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

    <details class="mb-3">
      <summary class="text-xs text-gray-500 cursor-pointer">¿Tienes una clave de licencia manual?</summary>

      <form method="POST" action="{{ route('team.license.activate', $team) }}" class="space-y-3 mt-3">
        @csrf
        <label class="block text-sm font-medium text-gray-700">Clave de licencia</label>
        <input type="text" name="license_key" class="w-full border rounded px-3 py-2" placeholder="Pega aquí tu clave" required>

        <label class="block text-sm font-medium text-gray-700">Meses a activar (opcional)</label>
        <input type="number" name="months" min="1" max="36" class="w-32 border rounded px-3 py-2" value="1">

        <button class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">
          Activar/Renovar manualmente
        </button>
      </form>
    </details>
  </div>
</x-app-layout>
