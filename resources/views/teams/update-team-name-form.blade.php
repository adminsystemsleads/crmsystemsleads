<x-form-section submit="updateTeamName">
    <x-slot name="title">
        {{ __('Team Name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('The team\'s name and owner information.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Team Owner Information -->
        <div class="col-span-6">
            <x-label value="{{ __('Team Owner') }}" />

            <div class="flex items-center mt-2">
                <img class="size-12 rounded-full object-cover" src="{{ $team->owner->profile_photo_url }}" alt="{{ $team->owner->name }}">

                <div class="ms-4 leading-tight">
                    <div class="text-gray-900">{{ $team->owner->name }}</div>
                    <div class="text-gray-700 text-sm">{{ $team->owner->email }}</div>
                </div>
            </div>
        </div>

        <!-- ID de la cuenta general (equipo) — solo lectura -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="team_id" value="{{ __('ID de la cuenta general') }}" />

            <x-input id="team_id"
                        type="text"
                        class="mt-1 block w-full bg-gray-100 text-gray-600 cursor-not-allowed font-mono"
                        :value="$team->id"
                        readonly
                        disabled />

            <p class="mt-1 text-xs text-gray-500">
                {{ __('Identificador único del equipo general. No editable.') }}
            </p>
        </div>

        <!-- Team Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('Team Name') }}" />

            <x-input id="name"
                        type="text"
                        class="mt-1 block w-full"
                        wire:model="state.name"
                        :disabled="! Gate::check('update', $team)" />

            <x-input-error for="name" class="mt-2" />
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
            <x-action-message class="me-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button>
                {{ __('Save') }}
            </x-button>
        </x-slot>
    @endif
</x-form-section>
