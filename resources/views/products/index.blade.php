<x-app-layout>
<div class="max-w-5xl mx-auto px-4 py-8">

  <div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-900">Catálogo de productos</h1>
    <button onclick="document.getElementById('modalNewProduct').classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
      <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Nuevo producto
    </button>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-gray-50 border-b border-gray-200">
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Nombre</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Unidad</th>
          <th class="px-4 py-3 text-right font-semibold text-gray-600">Precio</th>
          <th class="px-4 py-3 text-center font-semibold text-gray-600">Activo</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($products as $p)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <p class="font-medium text-gray-900">{{ $p->name }}</p>
              @if($p->description)
                <p class="text-xs text-gray-400 truncate max-w-xs">{{ $p->description }}</p>
              @endif
            </td>
            <td class="px-4 py-3 text-gray-600">{{ $p->unit }}</td>
            <td class="px-4 py-3 text-right font-medium text-gray-900">
              {{ $p->currency }} {{ number_format($p->price, 2) }}
            </td>
            <td class="px-4 py-3 text-center">
              @if($p->is_active)
                <span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-semibold">Sí</span>
              @else
                <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-500 px-2 py-0.5 text-xs font-semibold">No</span>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              <button onclick="openEditProduct({{ $p->id }}, @json($p->name), @json($p->description ?? ''), @json($p->unit), {{ $p->price }}, @json($p->currency), {{ $p->is_active ? 'true' : 'false' }})"
                      class="text-xs text-indigo-600 hover:text-indigo-800 font-medium mr-3">Editar</button>
              <form method="POST" action="{{ route('products.destroy', $p) }}" class="inline"
                    onsubmit="return confirm('¿Eliminar producto?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Sin productos. Crea el primero.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Modal nuevo producto --}}
<div id="modalNewProduct" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
    <h2 class="text-base font-bold text-gray-900 mb-4">Nuevo producto</h2>
    <form method="POST" action="{{ route('products.store') }}" class="space-y-3">
      @csrf
      @include('products._form')
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">Guardar</button>
        <button type="button" onclick="document.getElementById('modalNewProduct').classList.add('hidden')"
                class="flex-1 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">Cancelar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal editar producto --}}
<div id="modalEditProduct" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
    <h2 class="text-base font-bold text-gray-900 mb-4">Editar producto</h2>
    <form id="editProductForm" method="POST" action="" class="space-y-3">
      @csrf @method('PUT')
      @include('products._form', ['edit' => true])
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">Actualizar</button>
        <button type="button" onclick="document.getElementById('modalEditProduct').classList.add('hidden')"
                class="flex-1 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditProduct(id, name, desc, unit, price, currency, active) {
  const form = document.getElementById('editProductForm');
  form.action = '/products/' + id;
  form.querySelector('[name=name]').value        = name;
  form.querySelector('[name=description]').value = desc;
  form.querySelector('[name=unit]').value        = unit;
  form.querySelector('[name=price]').value       = price;
  form.querySelector('[name=currency]').value    = currency;
  form.querySelector('[name=is_active]').checked = active;
  document.getElementById('modalEditProduct').classList.remove('hidden');
}
</script>
</x-app-layout>
