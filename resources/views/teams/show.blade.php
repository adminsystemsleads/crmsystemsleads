<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <!-- En tu barra superior -->
<form action="{{ route('locale.update') }}" method="POST" class="inline">
  @csrf
  <select name="locale"
          onchange="this.form.submit()"
          class="border rounded px-2 py-1 text-sm">
      @foreach(config('app.supported_locales') as $loc)
        <option value="{{ $loc }}" {{ app()->getLocale() === $loc ? 'selected' : '' }}>
          {{ strtoupper($loc) }}
        </option>
      @endforeach
  </select>
</form>
            @livewire('teams.update-team-name-form', ['team' => $team])

            @livewire('teams.team-member-manager', ['team' => $team])

            @if (Gate::check('delete', $team) && ! $team->personal_team)
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.delete-team-form', ['team' => $team])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
