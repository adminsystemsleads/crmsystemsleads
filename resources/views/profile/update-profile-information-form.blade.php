@php
    // Lista de países (mismo set que en register.blade.php)
    $countries = [
        ['iso' => 'AR', 'name' => 'Argentina',         'prefix' => '+54'],
        ['iso' => 'BO', 'name' => 'Bolivia',           'prefix' => '+591'],
        ['iso' => 'BR', 'name' => 'Brasil',            'prefix' => '+55'],
        ['iso' => 'CA', 'name' => 'Canadá',            'prefix' => '+1'],
        ['iso' => 'CL', 'name' => 'Chile',             'prefix' => '+56'],
        ['iso' => 'CO', 'name' => 'Colombia',          'prefix' => '+57'],
        ['iso' => 'CR', 'name' => 'Costa Rica',        'prefix' => '+506'],
        ['iso' => 'CU', 'name' => 'Cuba',              'prefix' => '+53'],
        ['iso' => 'EC', 'name' => 'Ecuador',           'prefix' => '+593'],
        ['iso' => 'SV', 'name' => 'El Salvador',       'prefix' => '+503'],
        ['iso' => 'ES', 'name' => 'España',            'prefix' => '+34'],
        ['iso' => 'US', 'name' => 'Estados Unidos',    'prefix' => '+1'],
        ['iso' => 'GT', 'name' => 'Guatemala',         'prefix' => '+502'],
        ['iso' => 'HN', 'name' => 'Honduras',          'prefix' => '+504'],
        ['iso' => 'MX', 'name' => 'México',            'prefix' => '+52'],
        ['iso' => 'NI', 'name' => 'Nicaragua',         'prefix' => '+505'],
        ['iso' => 'PA', 'name' => 'Panamá',            'prefix' => '+507'],
        ['iso' => 'PY', 'name' => 'Paraguay',          'prefix' => '+595'],
        ['iso' => 'PE', 'name' => 'Perú',              'prefix' => '+51'],
        ['iso' => 'PR', 'name' => 'Puerto Rico',       'prefix' => '+1'],
        ['iso' => 'DO', 'name' => 'Rep. Dominicana',   'prefix' => '+1'],
        ['iso' => 'UY', 'name' => 'Uruguay',           'prefix' => '+598'],
        ['iso' => 'VE', 'name' => 'Venezuela',         'prefix' => '+58'],
    ];
@endphp

<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo (estilo burbuja con botón cámara superpuesto) -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div class="col-span-6 sm:col-span-4"
                 x-data="{ photoName: null, photoPreview: null }">

                <!-- File input oculto (Livewire lo procesa) -->
                <input type="file" id="photo" class="hidden"
                       wire:model.live="photo"
                       x-ref="photo"
                       x-on:change="
                           if (!$refs.photo.files[0]) return;
                           photoName = $refs.photo.files[0].name;
                           const reader = new FileReader();
                           reader.onload = (e) => { photoPreview = e.target.result; };
                           reader.readAsDataURL($refs.photo.files[0]);
                       ">

                <div class="flex items-center gap-5">
                    {{-- Burbuja circular --}}
                    <div class="relative">
                        {{-- Foto actual --}}
                        <img x-show="!photoPreview"
                             src="{{ $this->user->profile_photo_url }}"
                             alt="{{ $this->user->name }}"
                             class="rounded-full object-cover ring-4 ring-white shadow-md"
                             style="width:96px; height:96px;">

                        {{-- Preview de nueva foto --}}
                        <span x-show="photoPreview"
                              class="block rounded-full bg-cover bg-no-repeat bg-center ring-4 ring-white shadow-md"
                              style="width:96px; height:96px; display:none;"
                              x-bind:style="'background-image: url(\'' + photoPreview + '\');'"></span>

                        {{-- Botón cámara --}}
                        <button type="button"
                                x-on:click.prevent="$refs.photo.click()"
                                class="absolute rounded-full p-2 shadow-lg transition ring-2 ring-white"
                                style="background-color:#1E2E48; bottom:2px; right:2px;"
                                onmouseover="this.style.backgroundColor='#152139'"
                                onmouseout="this.style.backgroundColor='#1E2E48'"
                                title="Cambiar foto">
                            <svg style="width:14px;height:14px;color:#fff;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Acciones de foto --}}
                    <div class="flex flex-col gap-1">
                        <x-label for="photo" value="{{ __('Photo') }}" />
                        <div class="flex items-center gap-3 text-xs">
                            <button type="button"
                                    x-on:click.prevent="$refs.photo.click()"
                                    class="font-medium underline"
                                    style="color:#1E2E48;">
                                {{ __('Select A New Photo') }}
                            </button>
                            @if ($this->user->profile_photo_path)
                                <span class="text-gray-300">·</span>
                                <button type="button"
                                        wire:click="deleteProfilePhoto"
                                        class="font-medium underline text-red-600 hover:text-red-700">
                                    {{ __('Remove Photo') }}
                                </button>
                            @endif
                        </div>
                        <p class="text-[11px] text-gray-400">JPG o PNG · máx. 1 MB</p>
                        <x-input-error for="photo" class="mt-1" />
                    </div>
                </div>
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('Name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required autocomplete="name" />
            <x-input-error for="name" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required autocomplete="username" />
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                <p class="text-sm mt-2">
                    {{ __('Your email address is unverified.') }}

                    <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" wire:click.prevent="sendEmailVerification">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>

        <!-- Phone (con selector de código de país) -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="phone" value="{{ __('Phone') }}" />
            <div class="mt-1 flex" style="gap: .5rem;">
                <select wire:model="state.country_code"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white"
                        style="min-width: 9.5rem; padding-right: 1.75rem;">
                    @foreach ($countries as $c)
                        <option value="{{ $c['prefix'] }}">
                            {{ $c['name'] }} ({{ $c['prefix'] }})
                        </option>
                    @endforeach
                </select>
                <x-input id="phone" type="tel" class="block w-full" wire:model="state.phone"
                         inputmode="tel" autocomplete="tel" placeholder="987654321" />
            </div>
            <x-input-error for="phone" class="mt-2" />
            <x-input-error for="country_code" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
