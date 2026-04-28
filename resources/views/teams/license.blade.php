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

    <form method="POST" action="{{ route('team.license.activate', $team) }}" class="space-y-3">
      @csrf
      <label class="block text-sm font-medium text-gray-700">Clave de licencia</label>
      <input type="text" name="license_key" class="w-full border rounded px-3 py-2" placeholder="Pega aquí tu clave" required>

      <label class="block text-sm font-medium text-gray-700">Meses a activar (opcional)</label>
      <input type="number" name="months" min="1" max="36" class="w-32 border rounded px-3 py-2" value="1">

      <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
        Activar/Renovar
      </button>
    </form>
  </div>
</x-app-layout>
