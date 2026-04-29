<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center gap-3">
      <a href="{{ route('contacts.index') }}"
         class="text-gray-400 hover:text-gray-600 transition">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <h2 class="text-lg font-semibold text-gray-800">
        {{ $contact ? 'Editar contacto' : 'Nuevo contacto' }}
      </h2>
      @if($contact)
        <span class="text-sm text-gray-400">{{ $contact->name }}</span>
      @endif
    </div>
  </x-slot>

  <div class="py-6 px-4 sm:px-6 lg:px-8 {{ $contact ? 'max-w-7xl' : 'max-w-2xl' }} mx-auto">

    @if(session('status'))
      <div class="mb-5 flex items-center gap-2 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        <svg class="size-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('status') }}
      </div>
    @endif

    <div class="{{ $contact ? 'grid grid-cols-1 lg:grid-cols-5 gap-6' : '' }}">

      {{-- ══════════════════════════════
           COLUMNA IZQUIERDA – Formulario
           ══════════════════════════════ --}}
      <div class="{{ $contact ? 'lg:col-span-3' : '' }}">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

          {{-- Avatar / cabecera --}}
          @if($contact)
            @php
              $colors = ['bg-indigo-100 text-indigo-700','bg-green-100 text-green-700','bg-amber-100 text-amber-700',
                         'bg-rose-100 text-rose-700','bg-purple-100 text-purple-700','bg-blue-100 text-blue-700'];
              $avatarCls = $colors[abs(crc32($contact->name)) % count($colors)];
              $initials  = strtoupper(mb_substr($contact->name, 0, 1) . (mb_substr($contact->name, 1, 1) ?: ''));
            @endphp
            <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-4 bg-gray-50">
              <div class="size-14 rounded-full flex items-center justify-center text-xl font-bold {{ $avatarCls }}">
                {{ $initials }}
              </div>
              <div>
                <p class="font-semibold text-gray-900">{{ $contact->name }}</p>
                <p class="text-sm text-gray-500">
                  {{ $contact->company ?? '' }}{{ $contact->company && $contact->position ? ' · ' : '' }}{{ $contact->position ?? '' }}
                </p>
                @if($contact->owner)
                  <p class="text-xs text-gray-400 mt-0.5">Creado por {{ $contact->owner->name }}</p>
                @endif
              </div>
            </div>
          @endif

          <form method="POST"
                action="{{ $contact ? route('contacts.update', $contact) : route('contacts.store') }}"
                class="p-6 space-y-5">
            @csrf
            @if($contact) @method('PUT') @endif

            {{-- Nombre --}}
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="first_name"
                       value="{{ old('first_name', $contact?->first_name) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-400 focus:border-indigo-400"
                       placeholder="Nombre">
                @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                <input type="text" name="last_name"
                       value="{{ old('last_name', $contact?->last_name) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-400 focus:border-indigo-400"
                       placeholder="Apellido">
              </div>
            </div>

            {{-- Email y teléfono --}}
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email"
                       value="{{ old('email', $contact?->email) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-400 focus:border-indigo-400"
                       placeholder="correo@ejemplo.com">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" name="phone"
                       value="{{ old('phone', $contact?->phone) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-400 focus:border-indigo-400"
                       placeholder="+51 999 999 999">
                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
              </div>
            </div>

            {{-- Empresa y cargo --}}
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                <input type="text" name="company"
                       value="{{ old('company', $contact?->company) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-400 focus:border-indigo-400"
                       placeholder="Nombre de la empresa">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                <input type="text" name="position"
                       value="{{ old('position', $contact?->position) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-400 focus:border-indigo-400"
                       placeholder="Ej. Gerente, Director…">
              </div>
            </div>

            {{-- Estado y origen --}}
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="status" class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                  @foreach(['nuevo' => 'Nuevo', 'activo' => 'Activo', 'cliente' => 'Cliente', 'inactivo' => 'Inactivo', 'perdido' => 'Perdido'] as $val => $label)
                    <option value="{{ $val }}" {{ old('status', $contact?->status ?? 'nuevo') === $val ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origen</label>
                <select name="source" class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-400 focus:border-indigo-400">
                  <option value="">— Sin especificar —</option>
                  @foreach(['whatsapp' => 'WhatsApp', 'crm' => 'CRM', 'web' => 'Web', 'referido' => 'Referido', 'publicidad' => 'Publicidad', 'otro' => 'Otro'] as $val => $label)
                    <option value="{{ $val }}" {{ old('source', $contact?->source) === $val ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            {{-- Notas --}}
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
              <textarea name="notes" rows="3"
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-400 focus:border-indigo-400"
                        placeholder="Información adicional sobre el contacto…">{{ old('notes', $contact?->notes) }}</textarea>
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
              @if($contact)
                <form method="POST" action="{{ route('contacts.destroy', $contact) }}"
                      onsubmit="return confirm('¿Eliminar este contacto?')">
                  @csrf @method('DELETE')
                  <button type="submit"
                          class="text-sm text-red-500 hover:text-red-700 transition flex items-center gap-1.5">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar contacto
                  </button>
                </form>
              @else
                <div></div>
              @endif

              <button type="submit"
                      class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $contact ? 'Guardar cambios' : 'Crear contacto' }}
              </button>
            </div>

          </form>
        </div>
      </div>

      {{-- ══════════════════════════════
           COLUMNA DERECHA – Info lateral
           ══════════════════════════════ --}}
      <div class="lg:col-span-2 space-y-4">

        {{-- Negocios vinculados --}}
        @if($contact)
          <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-800">Negocios</span>
              <span class="text-xs text-gray-400">{{ $contact->deals->count() }}</span>
            </div>

            @if($contact->deals->isEmpty())
              <div class="px-4 py-6 text-center text-xs text-gray-400">
                Sin negocios vinculados.
              </div>
            @else
              <ul class="divide-y divide-gray-50">
                @foreach($contact->deals as $deal)
                  @php
                    $dealStatusCls = match($deal->status) {
                      'won'  => 'bg-green-100 text-green-700',
                      'lost' => 'bg-red-100 text-red-600',
                      default => 'bg-blue-100 text-blue-700',
                    };
                  @endphp
                  <li class="px-4 py-3">
                    <div class="flex items-start gap-2">
                      <div class="min-w-0 flex-1">
                        <a href="{{ route('deals.edit', [$deal->pipeline_id, $deal->id]) }}"
                           class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">
                          {{ $deal->title }}
                        </a>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $deal->pipeline->name ?? '—' }} · {{ $deal->stage->name ?? '—' }}</p>
                        @if($deal->amount)
                          <p class="text-xs text-gray-400 mt-0.5">{{ number_format($deal->amount, 2) }} {{ $deal->currency }}</p>
                        @endif
                      </div>
                      <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold shrink-0 {{ $dealStatusCls }}">
                        {{ ucfirst($deal->status) }}
                      </span>
                    </div>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>

          {{-- Acceso rápido --}}
          <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-3">Acciones rápidas</p>

            @if($contact->phone)
              <a href="https://wa.me/{{ preg_replace('/\D/', '', $contact->phone) }}"
                 target="_blank" rel="noopener"
                 class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition border border-gray-100">
                <svg class="size-4 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                </svg>
                Enviar WhatsApp
              </a>
            @endif

            @if($contact->email)
              <a href="mailto:{{ $contact->email }}"
                 class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition border border-gray-100">
                <svg class="size-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Enviar email
              </a>
            @endif

            <a href="{{ route('whatsapp.inbox.index') }}?q={{ urlencode($contact->phone ?? '') }}"
               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition border border-gray-100">
              <svg class="size-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
              Ver conversaciones
            </a>
          </div>
        @endif

      </div>
    </div>
  </div>
</x-app-layout>
