<x-guest-layout>
    @php
        // Lista de países (ISO 3166-1 alfa-2 → nombre en español + prefijo telefónico)
        // El orden pone primero los más relevantes para el mercado objetivo
        $countries = [
            ['iso' => 'PE', 'name' => 'Perú',              'prefix' => '+51'],
            ['iso' => 'CO', 'name' => 'Colombia',          'prefix' => '+57'],
            ['iso' => 'MX', 'name' => 'México',            'prefix' => '+52'],
            ['iso' => 'ES', 'name' => 'España',            'prefix' => '+34'],
            ['iso' => 'AR', 'name' => 'Argentina',         'prefix' => '+54'],
            ['iso' => 'CL', 'name' => 'Chile',             'prefix' => '+56'],
            ['iso' => 'EC', 'name' => 'Ecuador',           'prefix' => '+593'],
            ['iso' => 'BO', 'name' => 'Bolivia',           'prefix' => '+591'],
            ['iso' => 'VE', 'name' => 'Venezuela',         'prefix' => '+58'],
            ['iso' => 'UY', 'name' => 'Uruguay',           'prefix' => '+598'],
            ['iso' => 'PY', 'name' => 'Paraguay',          'prefix' => '+595'],
            ['iso' => 'PA', 'name' => 'Panamá',            'prefix' => '+507'],
            ['iso' => 'CR', 'name' => 'Costa Rica',        'prefix' => '+506'],
            ['iso' => 'GT', 'name' => 'Guatemala',         'prefix' => '+502'],
            ['iso' => 'HN', 'name' => 'Honduras',          'prefix' => '+504'],
            ['iso' => 'SV', 'name' => 'El Salvador',       'prefix' => '+503'],
            ['iso' => 'NI', 'name' => 'Nicaragua',         'prefix' => '+505'],
            ['iso' => 'CU', 'name' => 'Cuba',              'prefix' => '+53'],
            ['iso' => 'DO', 'name' => 'Rep. Dominicana',   'prefix' => '+1'],
            ['iso' => 'PR', 'name' => 'Puerto Rico',       'prefix' => '+1'],
            ['iso' => 'BR', 'name' => 'Brasil',            'prefix' => '+55'],
            ['iso' => 'US', 'name' => 'Estados Unidos',    'prefix' => '+1'],
            ['iso' => 'CA', 'name' => 'Canadá',            'prefix' => '+1'],
        ];
        $defaultPrefix = old('country_code', '+51');
    @endphp

    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="phone" value="{{ __('Phone') }}" />
                <div class="mt-1 flex" style="gap: .5rem;">
                    <select name="country_code" id="country_code"
                            data-default-iso="PE"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white"
                            style="min-width: 9.5rem; padding-right: 1.75rem;"
                            required>
                        @foreach ($countries as $c)
                            <option value="{{ $c['prefix'] }}"
                                    data-iso="{{ $c['iso'] }}"
                                    @selected($defaultPrefix === $c['prefix'])>
                                {{ $c['name'] }} ({{ $c['prefix'] }})
                            </option>
                        @endforeach
                    </select>
                    <x-input id="phone" class="block w-full" type="tel" name="phone"
                             :value="old('phone')" required inputmode="tel" autocomplete="tel"
                             placeholder="987654321" />
                </div>
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ms-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>

    {{-- Autoselección del código de país según la IP del visitante --}}
    <script>
      (function () {
        var select = document.getElementById('country_code');
        if (!select) return;

        // Si el usuario ya tocó el select (old() de validación) no sobreescribir
        if (select.dataset.userSelected === '1') return;

        function selectByIso(iso) {
          if (!iso) return false;
          var opt = select.querySelector('option[data-iso="' + iso.toUpperCase() + '"]');
          if (opt) {
            opt.selected = true;
            select.dispatchEvent(new Event('change'));
            return true;
          }
          return false;
        }

        // Marca que el usuario eligió manualmente (para no pisar su selección si la página recarga via AJAX)
        select.addEventListener('change', function () { select.dataset.userSelected = '1'; });

        // 1) Intenta cache de localStorage para no llamar al API en cada visita
        try {
          var cached = localStorage.getItem('detected-country-iso');
          var cachedAt = parseInt(localStorage.getItem('detected-country-at') || '0', 10);
          var fresh = (Date.now() - cachedAt) < (1000 * 60 * 60 * 24 * 7); // 7 días
          if (cached && fresh) {
            selectByIso(cached);
            return;
          }
        } catch (e) {}

        // 2) Llamada a ipapi.co (gratis sin API key, ~1k req/día por IP)
        fetch('https://ipapi.co/json/', { cache: 'no-store' })
          .then(function (r) { return r.ok ? r.json() : null; })
          .then(function (data) {
            if (!data || !data.country_code) return;
            try {
              localStorage.setItem('detected-country-iso', data.country_code);
              localStorage.setItem('detected-country-at', String(Date.now()));
            } catch (e) {}
            selectByIso(data.country_code);
          })
          .catch(function () { /* deja el default seleccionado */ });
      })();
    </script>
</x-guest-layout>
