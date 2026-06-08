@php
    $currentTz = $team->timezone ?: \App\Models\Team::DEFAULT_TIMEZONE;

    // Construye la lista de TODAS las zonas horarias del mundo con su offset GMT actual.
    $utc = new \DateTime('now', new \DateTimeZone('UTC'));
    $tzOptions = [];
    foreach (\DateTimeZone::listIdentifiers() as $tzId) {
        try {
            $offset = (new \DateTimeZone($tzId))->getOffset($utc);
        } catch (\Throwable $e) {
            continue;
        }
        $sign = $offset < 0 ? '-' : '+';
        $abs  = abs($offset);
        $h    = floor($abs / 3600);
        $m    = floor(($abs % 3600) / 60);
        $tzOptions[] = [
            'id'     => $tzId,
            'label'  => sprintf('(GMT%s%02d:%02d) %s', $sign, $h, $m, $tzId),
            'offset' => $offset,
        ];
    }
    usort($tzOptions, fn ($a, $b) => [$a['offset'], $a['id']] <=> [$b['offset'], $b['id']]);
@endphp

<div class="md:grid md:grid-cols-3 md:gap-6">
    <div class="md:col-span-1 flex justify-between">
        <div class="px-4 sm:px-0">
            <h3 class="text-lg font-medium text-gray-900">{{ __('Zona horaria') }}</h3>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Zona horaria de la cuenta. Las licencias activadas vencen a las 23:59 del día correspondiente en esta zona horaria.') }}
            </p>
        </div>
    </div>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form method="POST" action="{{ route('team.timezone.update', $team) }}">
            @csrf
            @method('PUT')

            <div class="px-4 py-5 bg-white sm:p-6 shadow rounded-tl-md rounded-tr-md">
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="timezone" value="{{ __('Zona horaria') }}" />

                        <select id="timezone" name="timezone"
                                @disabled(! Gate::check('update', $team))
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @foreach ($tzOptions as $tz)
                                <option value="{{ $tz['id'] }}" @selected($tz['id'] === $currentTz)>
                                    {{ $tz['label'] }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error for="timezone" class="mt-2" />

                        <p class="mt-2 text-xs text-gray-500">
                            {{ __('Por defecto: (GMT-05:00) America/Lima.') }}
                        </p>
                    </div>
                </div>
            </div>

            @if (Gate::check('update', $team))
                <div class="flex items-center justify-end px-4 py-3 bg-gray-50 text-end sm:px-6 shadow rounded-bl-md rounded-br-md">
                    @if (session('success'))
                        <span class="me-3 text-sm text-gray-600">{{ session('success') }}</span>
                    @endif

                    <x-button>
                        {{ __('Guardar') }}
                    </x-button>
                </div>
            @endif
        </form>
    </div>
</div>
