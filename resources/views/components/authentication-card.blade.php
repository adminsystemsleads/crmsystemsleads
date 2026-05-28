<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4"
     style="background:
              radial-gradient(ellipse at 80% 10%, rgba(201,169,97,.18) 0%, transparent 55%),
              radial-gradient(ellipse at 10% 90%, rgba(201,169,97,.10) 0%, transparent 50%),
              linear-gradient(135deg, #0f172a 0%, #1E2E48 50%, #2a3f5f 100%);">

    {{-- Logo --}}
    <div class="mb-2 drop-shadow-lg">
        {{ $logo }}
    </div>

    {{-- Card con acento dorado superior --}}
    <div class="w-full sm:max-w-md mt-6 bg-white shadow-2xl sm:rounded-2xl overflow-hidden"
         style="border-top: 4px solid #C9A961;">
        <div class="px-6 py-7 sm:px-10 sm:py-9">
            {{ $slot }}
        </div>
    </div>

    {{-- Footer sutil --}}
    <p class="mt-6 text-xs" style="color: rgba(255,255,255,.55);">
        &copy; {{ date('Y') }} {{ config('app.name', 'QipuCRM') }} &middot; Desarrollado por Systems Leads
    </p>
</div>
