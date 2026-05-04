@props(['variant' => 'sidebar'])

@php
  $supported = config('app.supported_locales', ['es', 'en', 'pt']);
  $names     = config('app.locale_names', []);
  $current   = app()->getLocale();
  $currentInfo = $names[$current] ?? ['name' => strtoupper($current), 'flag' => '🌐'];
@endphp

<div x-data="{ openLang: false }" @click.away="openLang = false" class="relative">

  @if ($variant === 'sidebar')
    {{-- Sidebar variant: full-width button --}}
    <button type="button" @click="openLang = !openLang"
            class="w-full flex items-center gap-2 px-3 py-2 text-sm rounded-lg hover:bg-gray-100 text-gray-700 transition">
      <span class="text-base">{{ $currentInfo['flag'] }}</span>
      <span class="truncate">{{ $currentInfo['name'] }}</span>
      <svg class="size-4 ml-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
      </svg>
    </button>

    <div x-show="openLang"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="bg-white rounded-lg shadow-xl ring-1 ring-black/5 py-1"
         style="display: none; position: absolute; bottom: calc(100% + 4px); left: 0; right: 0; z-index: 60;">

      @foreach ($supported as $loc)
        @php $info = $names[$loc] ?? ['name' => strtoupper($loc), 'flag' => '🌐']; @endphp
        <form method="POST" action="{{ route('locale.update') }}">
          @csrf
          <input type="hidden" name="locale" value="{{ $loc }}">
          <button type="submit"
                  class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 transition
                         {{ $current === $loc ? 'text-indigo-700 font-semibold bg-indigo-50' : 'text-gray-700' }}">
            <span class="text-base">{{ $info['flag'] }}</span>
            <span class="truncate flex-1">{{ $info['name'] }}</span>
            @if ($current === $loc)
              <svg class="size-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            @endif
          </button>
        </form>
      @endforeach
    </div>

  @else
    {{-- Compact variant: icon button (header / standalone) --}}
    <button type="button" @click="openLang = !openLang"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition">
      <span class="text-base leading-none">{{ $currentInfo['flag'] }}</span>
      <span class="text-xs font-semibold uppercase">{{ $current }}</span>
      <svg class="size-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <div x-show="openLang"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute right-0 mt-1 w-44 bg-white rounded-lg shadow-xl ring-1 ring-black/5 py-1 z-50"
         style="display: none;">

      @foreach ($supported as $loc)
        @php $info = $names[$loc] ?? ['name' => strtoupper($loc), 'flag' => '🌐']; @endphp
        <form method="POST" action="{{ route('locale.update') }}">
          @csrf
          <input type="hidden" name="locale" value="{{ $loc }}">
          <button type="submit"
                  class="w-full text-left flex items-center gap-2 px-3 py-2 text-sm hover:bg-gray-100 transition
                         {{ $current === $loc ? 'text-indigo-700 font-semibold bg-indigo-50' : 'text-gray-700' }}">
            <span class="text-base">{{ $info['flag'] }}</span>
            <span class="flex-1 truncate">{{ $info['name'] }}</span>
            @if ($current === $loc)
              <svg class="size-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            @endif
          </button>
        </form>
      @endforeach
    </div>
  @endif

</div>
