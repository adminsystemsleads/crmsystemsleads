<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Aplicar tema antes del render para evitar "flash" de color claro --}}
    <script>
      (function () {
        try {
          var t = localStorage.getItem('theme');
          if (t === 'dark') document.documentElement.classList.add('dark');
        } catch (e) {}
      })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    <style>
      [x-cloak]{display:none !important}
      /* Variables de tema oscuro para componentes que no usan Tailwind dinámico */
      .dark body { background-color: #0f172a; }
      .dark .bg-white { background-color: #1e293b !important; }
      .dark .bg-gray-50  { background-color: #0f172a !important; }
      .dark .bg-gray-100 { background-color: #0b1220 !important; }
      .dark .text-gray-900 { color: #f1f5f9 !important; }
      .dark .text-gray-800 { color: #e2e8f0 !important; }
      .dark .text-gray-700 { color: #cbd5e1 !important; }
      .dark .text-gray-600 { color: #94a3b8 !important; }
      .dark .text-gray-500 { color: #64748b !important; }
      .dark .border-gray-200 { border-color: #334155 !important; }
      .dark .border-gray-100 { border-color: #1e293b !important; }
      .dark .divide-gray-100 > * + * { border-color: #1e293b !important; }
      .dark .divide-gray-200 > * + * { border-color: #334155 !important; }
      .dark .ring-gray-200 { --tw-ring-color: #334155 !important; }
      .dark .shadow, .dark .shadow-sm, .dark .shadow-md, .dark .shadow-lg, .dark .shadow-xl {
        box-shadow: 0 1px 3px 0 rgba(0,0,0,.5), 0 1px 2px -1px rgba(0,0,0,.5) !important;
      }
      .dark input, .dark select, .dark textarea {
        background-color: #1e293b !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
      }
      .dark input::placeholder, .dark textarea::placeholder { color: #64748b !important; }
      .dark .hover\:bg-gray-50:hover  { background-color: #1e293b !important; }
      .dark .hover\:bg-gray-100:hover { background-color: #334155 !important; }
      .dark .bg-indigo-50  { background-color: rgba(99,102,241,.12) !important; }
      .dark .bg-green-50   { background-color: rgba(34,197,94,.12) !important; }
      .dark .bg-red-50     { background-color: rgba(239,68,68,.12) !important; }
      .dark .bg-amber-50, .dark .bg-yellow-50 { background-color: rgba(245,158,11,.12) !important; }
      .dark .bg-blue-50    { background-color: rgba(59,130,246,.12) !important; }
      .dark .bg-purple-50  { background-color: rgba(168,85,247,.12) !important; }
    </style>
  </head>

  <body class="font-sans antialiased dark:bg-slate-900 dark:text-slate-100">
    <x-banner />

    <div class="min-h-screen bg-gray-100 dark:bg-slate-900 lg:ms-64">
      @livewire('navigation-menu')

      @if (isset($header))
        <header class="bg-white shadow dark:bg-slate-800">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $header }}
          </div>
        </header>
      @endif

      <main>
        {{ $slot }}
      </main>
    </div>

    @stack('modals')
    @livewireScripts

    {{-- Toggle de tema (controla el botón #themeToggleBtn donde sea que aparezca) --}}
    <script>
      (function () {
        function applyTheme(t) {
          if (t === 'dark') document.documentElement.classList.add('dark');
          else document.documentElement.classList.remove('dark');
          // Actualizar label del botón sidebar si existe
          document.querySelectorAll('[data-theme-label]').forEach(el => {
            el.textContent = t === 'dark' ? 'Tema oscuro' : 'Tema claro';
          });
        }
        try { applyTheme(localStorage.getItem('theme') || 'light'); } catch(e){}

        document.addEventListener('click', function (e) {
          const btn = e.target.closest('#themeToggleBtn');
          if (!btn) return;
          e.preventDefault();
          const isDark = document.documentElement.classList.contains('dark');
          const next = isDark ? 'light' : 'dark';
          try { localStorage.setItem('theme', next); } catch(e){}
          applyTheme(next);
        });
      })();
    </script>
  </body>
</html>
