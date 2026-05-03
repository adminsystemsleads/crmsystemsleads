<x-app-layout>
<div class="max-w-5xl mx-auto px-4 py-8">

  <div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-900">Comprobantes electrónicos</h1>
    <a href="{{ route('invoice-config.edit') }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
      <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      Configurar
    </a>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
  @endif

  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-gray-50 border-b border-gray-200">
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Número</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Tipo</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Cliente</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Emisión</th>
          <th class="px-4 py-3 text-right font-semibold text-gray-600">Total</th>
          <th class="px-4 py-3 text-center font-semibold text-gray-600">Estado</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($invoices as $inv)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $inv->numero }}</td>
            <td class="px-4 py-3 text-gray-600">{{ $inv->tipo_nombre }}</td>
            <td class="px-4 py-3">
              <p class="text-gray-900 font-medium">{{ $inv->cliente_razon_social }}</p>
              <p class="text-xs text-gray-400">{{ $inv->cliente_num_doc }}</p>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ $inv->fecha_emision->format('d/m/Y') }}</td>
            <td class="px-4 py-3 text-right font-medium text-gray-900">
              {{ $inv->moneda }} {{ number_format($inv->total, 2) }}
            </td>
            <td class="px-4 py-3 text-center">
              @php
                $colors = [
                  'draft'     => 'bg-gray-100 text-gray-600',
                  'signed'    => 'bg-blue-100 text-blue-700',
                  'sent'      => 'bg-yellow-100 text-yellow-700',
                  'accepted'  => 'bg-green-100 text-green-700',
                  'rejected'  => 'bg-red-100 text-red-700',
                  'cancelled' => 'bg-gray-100 text-gray-500',
                ];
              @endphp
              <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $colors[$inv->estado] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $inv->estado_badge }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <a href="{{ route('invoices.show', $inv) }}"
                 class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">
              Sin comprobantes. Crea uno desde una negociación.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($invoices->hasPages())
    <div class="mt-4">{{ $invoices->links() }}</div>
  @endif
</div>
</x-app-layout>
