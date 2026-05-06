<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar cuenta WhatsApp</h2>
  </x-slot>

  <div class="py-8">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow-sm sm:rounded-lg p-6">

        <form method="POST" action="{{ route('whatsapp.accounts.update', $account) }}">
          @csrf
          @method('PUT')

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Nombre interno</label>
            <input name="name" value="{{ old('name', $account->name) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
            @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Phone Number ID</label>
            <input name="phone_number_id" value="{{ old('phone_number_id', $account->phone_number_id) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
            @error('phone_number_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <div class="flex items-center justify-between gap-2 mb-1">
                <label class="block text-sm font-medium text-gray-700">WABA ID *</label>
                <button type="button" id="autoDetectWaba"
                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-1">
                  <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                  </svg>
                  Auto-detectar
                </button>
              </div>
              <input id="wabaIdInput" name="waba_id" value="{{ old('waba_id', $account->waba_id) }}"
                     placeholder="Ej: 123456789012345"
                     class="w-full border-gray-300 rounded-md shadow-sm">
              <p id="autoDetectMsg" class="text-xs mt-1 hidden"></p>
              <p class="text-[11px] text-gray-400 mt-1">⚠ NO es lo mismo que el Phone Number ID. Click "Auto-detectar" para obtenerlo automáticamente.</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Business ID (opcional)</label>
              <input name="business_id" value="{{ old('business_id', $account->business_id) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
            </div>
          </div>

          <script>
          document.getElementById('autoDetectWaba')?.addEventListener('click', async function () {
            const phoneId = document.querySelector('[name=phone_number_id]')?.value?.trim();
            const token   = document.querySelector('[name=access_token]')?.value?.trim();
            const msgEl   = document.getElementById('autoDetectMsg');
            const wabaInp = document.getElementById('wabaIdInput');

            msgEl.className = 'text-xs mt-1 text-gray-500';
            msgEl.textContent = 'Consultando Meta…';
            msgEl.classList.remove('hidden');

            if (!phoneId || !token) {
              msgEl.className = 'text-xs mt-1 text-red-600';
              msgEl.textContent = 'Completa Phone Number ID y Access Token primero.';
              return;
            }

            try {
              const res = await fetch('{{ route("whatsapp.accounts.detect-waba") }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'Accept':       'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                },
                body: JSON.stringify({ phone_number_id: phoneId, access_token: token }),
              });
              const data = await res.json();
              if (data.ok) {
                wabaInp.value = data.waba_id;
                msgEl.className = 'text-xs mt-1 text-green-600';
                msgEl.textContent = '✓ WABA ID detectado: ' + data.waba_id +
                  (data.verified_name ? ' (' + data.verified_name + ')' : '');
              } else {
                msgEl.className = 'text-xs mt-1 text-red-600';
                msgEl.textContent = '❌ ' + (data.message || 'No se pudo detectar.');
              }
            } catch (err) {
              msgEl.className = 'text-xs mt-1 text-red-600';
              msgEl.textContent = '❌ Error: ' + err.message;
            }
          });
          </script>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Access Token</label>
            <textarea name="access_token" rows="3" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('access_token', $account->access_token) }}</textarea>
            @error('access_token') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Verify Token</label>
            <input name="verify_token" value="{{ old('verify_token', $account->verify_token) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
            @error('verify_token') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Pipeline destino</label>
            <select name="pipeline_id" class="mt-1 w-full border-gray-300 rounded-md shadow-sm">
              @foreach($pipelines as $p)
                <option value="{{ $p->id }}" {{ old('pipeline_id', $account->pipeline_id) == $p->id ? 'selected' : '' }}>
                  {{ $p->name }}
                </option>
              @endforeach
            </select>
            @error('pipeline_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Asignación equitativa de negociaciones --}}
          <div class="mb-6 p-4 rounded-lg border border-indigo-100 bg-indigo-50/40">
            <label class="block text-sm font-semibold text-gray-800 mb-1">Usuarios para asignar negociaciones</label>
            <p class="text-xs text-gray-500 mb-3">
              Las nuevas negociaciones que entren por este WhatsApp se asignarán automáticamente
              <strong>de forma equitativa (round-robin)</strong> entre los usuarios marcados.
            </p>

            @if($teamMembers->isEmpty())
              <p class="text-xs text-gray-400">No hay miembros en este equipo todavía.</p>
            @else
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto pr-1">
                @foreach($teamMembers as $member)
                  @php
                    $checked = in_array($member->id, old('assignee_ids', $assignedUserIds ?? []));
                  @endphp
                  <label class="flex items-center gap-2 px-3 py-2 rounded-md bg-white border border-gray-200 cursor-pointer hover:border-indigo-300 transition">
                    <input type="checkbox" name="assignee_ids[]" value="{{ $member->id }}"
                           {{ $checked ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600">
                    <span class="text-sm text-gray-700 truncate">{{ $member->name }}</span>
                  </label>
                @endforeach
              </div>
            @endif
          </div>

          <div class="mb-6 flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
            <span class="text-sm text-gray-700">Activo</span>
          </div>

          <div class="flex justify-end gap-2">
            <a href="{{ route('whatsapp.accounts.index') }}" class="px-4 py-2 border rounded-md text-gray-700">Volver</a>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Guardar</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</x-app-layout>
