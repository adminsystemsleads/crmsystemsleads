<x-app-layout>
<div class="max-w-5xl mx-auto px-4 py-8">

  <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-xl font-bold text-gray-900">Catálogo de productos</h1>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('products.import.form') }}"
         class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        Importar CSV
      </a>
      <button onclick="document.getElementById('modalNewProduct').classList.remove('hidden')"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo producto
      </button>
    </div>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      <p class="font-semibold mb-1">Hay errores de validación:</p>
      <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-gray-50 border-b border-gray-200">
          <th class="px-4 py-3 text-left font-semibold text-gray-600 w-16"></th>
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
              @if($p->image_path)
                <img src="{{ $p->image_url }}" alt="{{ $p->name }}"
                     class="size-12 object-cover rounded-lg border border-gray-200">
              @else
                <div class="size-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-300">
                  <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
              @endif
            </td>
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
              <button onclick="openEditProduct({{ $p->id }}, @json($p->name), @json($p->description ?? ''), @json($p->unit), {{ $p->price }}, @json($p->currency), {{ $p->is_active ? 'true' : 'false' }}, @json($p->image_url))"
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
            <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Sin productos. Crea el primero.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Modal nuevo producto --}}
<div id="modalNewProduct" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="text-base font-bold text-gray-900 mb-4">Nuevo producto</h2>
    <form method="POST" action="{{ route('products.store') }}" class="space-y-3" enctype="multipart/form-data">
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
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="text-base font-bold text-gray-900 mb-4">Editar producto</h2>

    {{-- Errores inline (se llenan por JS al fallar el submit) --}}
    <div id="editProductErrors" class="hidden mb-3 rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-xs text-red-700"></div>

    <form id="editProductForm" class="space-y-3" enctype="multipart/form-data">
      @csrf
      <input type="hidden" id="editProductId" value="">
      @include('products._form', ['edit' => true])
      <div class="flex gap-2 pt-2">
        <button type="submit" id="editProductSubmit"
                class="flex-1 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition disabled:bg-gray-300">
          Actualizar
        </button>
        <button type="button" onclick="document.getElementById('modalEditProduct').classList.add('hidden')"
                class="flex-1 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditProduct(id, name, desc, unit, price, currency, active, imageUrl) {
  const form = document.getElementById('editProductForm');
  if (!form) { console.error('editProductForm no encontrado'); return; }

  document.getElementById('editProductId').value = id;
  document.getElementById('editProductErrors').classList.add('hidden');

  function setField(name, value) {
    const inputs = form.querySelectorAll(`[name="${name}"]`);
    inputs.forEach(inp => { if (inp.type !== 'hidden') inp.value = value; });
  }

  setField('name', name ?? '');
  setField('description', desc ?? '');
  setField('unit', unit ?? 'unidad');
  setField('price', price ?? 0);
  setField('currency', currency ?? 'PEN');

  const chk = form.querySelector('input[type=checkbox][name=is_active]');
  if (chk) chk.checked = !!active;

  const wrap = document.getElementById('currentImageWrap-edit');
  if (wrap) {
    wrap.innerHTML = imageUrl
      ? `<img src="${imageUrl}" class="size-20 object-cover rounded-lg border border-gray-200" alt="">`
      : '<p class="text-[10px] text-gray-400">Sin imagen actual.</p>';
  }
  const remove = form.querySelector('input[type=checkbox][name=remove_image]');
  if (remove) remove.checked = false;

  // Limpiar input de archivo (no se puede setValue por seguridad)
  const fileInp = form.querySelector('input[type=file][name=image]');
  if (fileInp) fileInp.value = '';

  document.getElementById('modalEditProduct').classList.remove('hidden');
}

// Submit por AJAX con fetch (evita problemas con PUT + multipart)
document.getElementById('editProductForm').addEventListener('submit', async function (e) {
  e.preventDefault();
  const form    = e.target;
  const id      = document.getElementById('editProductId').value;
  const errBox  = document.getElementById('editProductErrors');
  const submit  = document.getElementById('editProductSubmit');

  if (!id) { alert('No se identificó el producto a editar.'); return; }

  errBox.classList.add('hidden');
  submit.disabled = true;
  submit.textContent = 'Guardando…';

  const fd = new FormData(form);
  // Spoofing PUT con FormData
  fd.set('_method', 'PUT');
  // checkboxes desmarcados envían 0 (FormData no los incluye por defecto)
  ['is_active', 'remove_image'].forEach(n => {
    const cb = form.querySelector(`input[type=checkbox][name="${n}"]`);
    if (cb && !cb.checked) fd.set(n, '0');
  });

  try {
    const res = await fetch('{{ url('/products') }}/' + id, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: fd,
    });

    if (res.redirected || res.status === 200 || res.status === 302) {
      // Éxito → recargar para mostrar la lista actualizada
      window.location.href = '{{ route('products.index') }}';
      return;
    }

    if (res.status === 422) {
      const data = await res.json();
      const msgs = [];
      if (data.errors) {
        for (const k in data.errors) {
          (data.errors[k] || []).forEach(m => msgs.push(m));
        }
      } else if (data.message) {
        msgs.push(data.message);
      }
      errBox.innerHTML = msgs.length
        ? '<ul class="list-disc list-inside space-y-0.5">' + msgs.map(m => '<li>' + m.replace(/[<>&]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;'}[c])) + '</li>').join('') + '</ul>'
        : 'Error de validación.';
      errBox.classList.remove('hidden');
    } else {
      errBox.textContent = 'Error del servidor (' + res.status + '). Intenta de nuevo.';
      errBox.classList.remove('hidden');
    }
  } catch (err) {
    errBox.textContent = 'Error de red: ' + err.message;
    errBox.classList.remove('hidden');
  } finally {
    submit.disabled = false;
    submit.textContent = 'Actualizar';
  }
});
</script>
</x-app-layout>
