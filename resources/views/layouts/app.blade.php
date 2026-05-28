<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>QipuCRM — Gestión comercial inteligente</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

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

    {{-- Estado global del sidebar (compartido entre layout y navigation-menu vía Alpine.store) --}}
    <script>
      document.addEventListener('alpine:init', () => {
        Alpine.store('sidebar', {
          open: true,
          init() {
            try {
              const saved = localStorage.getItem('sidebar-open');
              if (saved !== null) {
                this.open = saved === 'true';
              } else {
                this.open = window.matchMedia('(min-width: 768px)').matches;
              }
            } catch (e) {}
          },
          toggle() {
            this.open = !this.open;
            try { localStorage.setItem('sidebar-open', this.open); } catch (e) {}
          }
        });
      });
    </script>

    @livewireStyles
    <style>
      [x-cloak]{display:none !important}

      /* =========================================================
         Paleta navy + gold (override de indigo Tailwind por defecto).
         Estas reglas vienen después de Tailwind, así que ganan en
         especificidad de cascada sin necesidad de recompilar.
         ========================================================= */
      :root {
        --brand-navy:        #1E2E48;
        --brand-navy-dark:   #152139;
        --brand-navy-light:  #E8ECF2;
        --brand-gold:        #C9A961;
        --brand-gold-dark:   #A08544;
        --brand-gold-light:  #FBF7EC;
      }

      /* Backgrounds */
      .bg-indigo-50   { background-color: var(--brand-navy-light) !important; }
      .bg-indigo-100  { background-color: #D1D9E4 !important; }
      .bg-indigo-500  { background-color: var(--brand-navy) !important; }
      .bg-indigo-600  { background-color: var(--brand-navy) !important; }
      .bg-indigo-700  { background-color: var(--brand-navy-dark) !important; }
      .hover\:bg-indigo-50:hover   { background-color: var(--brand-navy-light) !important; }
      .hover\:bg-indigo-100:hover  { background-color: #D1D9E4 !important; }
      .hover\:bg-indigo-600:hover  { background-color: var(--brand-navy) !important; }
      .hover\:bg-indigo-700:hover  { background-color: var(--brand-navy-dark) !important; }

      /* Text */
      .text-indigo-400 { color: #5A6E8F !important; }
      .text-indigo-500 { color: var(--brand-navy) !important; }
      .text-indigo-600 { color: var(--brand-navy) !important; }
      .text-indigo-700 { color: var(--brand-navy-dark) !important; }
      .text-indigo-900 { color: #0d1726 !important; }
      .hover\:text-indigo-600:hover { color: var(--brand-navy) !important; }
      .hover\:text-indigo-700:hover { color: var(--brand-navy-dark) !important; }

      /* Borders */
      .border-indigo-500 { border-color: var(--brand-navy) !important; }
      .border-indigo-600 { border-color: var(--brand-navy) !important; }
      .focus\:border-indigo-500:focus { border-color: var(--brand-navy) !important; }
      .focus\:border-indigo-300:focus { border-color: #93A4BD !important; }

      /* Rings */
      .ring-indigo-100 { --tw-ring-color: rgba(30,46,72,.15) !important; }
      .ring-indigo-500 { --tw-ring-color: var(--brand-navy) !important; }
      .focus\:ring-indigo-500:focus { --tw-ring-color: var(--brand-navy) !important; }
      .focus\:ring-indigo-200:focus { --tw-ring-color: rgba(30,46,72,.25) !important; }

      /* Gradients (from-/to-/via-) */
      .from-indigo-500 { --tw-gradient-from: var(--brand-navy) !important; }
      .from-indigo-600 { --tw-gradient-from: var(--brand-navy) !important; }
      .to-indigo-500   { --tw-gradient-to:   var(--brand-navy) !important; }
      .to-indigo-600   { --tw-gradient-to:   var(--brand-navy) !important; }
      .via-indigo-500  { --tw-gradient-via:  var(--brand-navy) !important; }

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

    <div x-data
         class="min-h-screen bg-gray-100 dark:bg-slate-900"
         :style="$store.sidebar.open ? 'margin-left: 16rem; transition: margin-left 200ms ease;' : 'margin-left: 0; transition: margin-left 200ms ease;'">
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
