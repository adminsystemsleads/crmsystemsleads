<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>QipuCRM — {{ __('Gestión comercial inteligente') }}</title>

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

      /* Botón de despliegue del menú (flotante e inline): tamaño consistente.
         (Media query en vez de variantes sm: para no depender de recompilar CSS.) */
      .menu-toggle-btn svg { width: 1.5rem; height: 1.5rem; }
      /* Botón flotante centrado verticalmente a la altura del título de la cabecera
         (top = centro del header ~73px/2; translateY centra sin importar su tamaño). */
      .menu-toggle-btn.fixed { top: 2.25rem !important; transform: translateY(-50%) !important; }
      @media (max-width: 640px) {
        .menu-toggle-btn { padding: .4rem !important; }
        .menu-toggle-btn svg { width: 1.25rem !important; height: 1.25rem !important; }
      }

      /* Cabeceras de página: en móvil apila el título y los botones de acción
         para que el título no se comprima, y mantiene todo alineado y simétrico. */
      @media (max-width: 640px) {
        .page-head { flex-direction: column; align-items: flex-start !important; gap: .65rem !important; }
        .page-head .page-head-actions { width: 100%; flex-wrap: wrap; }
        .page-head .page-head-title { font-size: 1.125rem !important; line-height: 1.4; }
      }

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

      /* =========================================================
         Barra lateral (sidebar) y cabecera en azul navy, con texto
         blanco y la opción seleccionada en dorado. (Diseño de marca.)
         ========================================================= */
      .app-sidebar { background: var(--brand-navy) !important; border-right-color: rgba(255,255,255,.08) !important; }
      .app-sidebar .border-gray-100,
      .app-sidebar .border-gray-200,
      .app-sidebar .border-b,
      .app-sidebar .border-t { border-color: rgba(255,255,255,.08) !important; }

      /* Texto general del menú -> blanco */
      .app-sidebar .text-gray-900,
      .app-sidebar .text-gray-800,
      .app-sidebar .text-gray-700,
      .app-sidebar .text-gray-600 { color: #fff !important; }
      .app-sidebar .text-gray-500,
      .app-sidebar .text-gray-400 { color: rgba(255,255,255,.55) !important; }

      /* Hover de los enlaces del menú */
      .app-sidebar .hover\:bg-gray-100:hover,
      .app-sidebar .hover\:bg-gray-50:hover { background: rgba(255,255,255,.08) !important; }

      /* Opción SELECCIONADA -> dorado (activo usa bg-indigo-50 / text-indigo-700 / text-indigo-500) */
      .app-sidebar .text-indigo-700,
      .app-sidebar .text-indigo-600,
      .app-sidebar .text-indigo-500 { color: var(--brand-gold) !important; }
      /* Fondo dorado translúcido + barra vertical dorada en el borde izquierdo del activo */
      .app-sidebar .bg-indigo-50 {
        background: rgba(201,169,97,.15) !important;
        box-shadow: inset 3px 0 0 0 var(--brand-gold) !important;
      }

      /* Popups blancos del sidebar (drop-up de Cuenta / Configuración): mantener legibles */
      .app-sidebar .bg-white { background: #fff !important; }
      .app-sidebar .bg-white .text-gray-900,
      .app-sidebar .bg-white .text-gray-800,
      .app-sidebar .bg-white .text-gray-700,
      .app-sidebar .bg-white .text-gray-600 { color: #374151 !important; }
      .app-sidebar .bg-white .text-gray-500,
      .app-sidebar .bg-white .text-gray-400 { color: #9ca3af !important; }
      .app-sidebar .bg-white .text-indigo-700,
      .app-sidebar .bg-white .text-indigo-600 { color: var(--brand-navy) !important; }
      .app-sidebar .bg-white .bg-indigo-50 { background: var(--brand-navy-light) !important; box-shadow: none !important; }
      .app-sidebar .bg-white .border-gray-100,
      .app-sidebar .bg-white .border-gray-200 { border-color: #e5e7eb !important; }
      .app-sidebar .bg-white .hover\:bg-gray-100:hover { background: #f3f4f6 !important; }

      /* Empuje del contenido SOLO en escritorio: en móvil el menú se superpone
         (overlay) en vez de comprimir la vista de la derecha. */
      .main-wrap { transition: margin-left 200ms ease; }
      @media (min-width: 768px) {
        .main-wrap.sidebar-open { margin-left: 16rem; }
      }
      /* Fondo oscuro detrás del menú en móvil (cierra al tocar). Oculto en escritorio. */
      .sidebar-backdrop { position: fixed; inset: 0; z-index: 40; background: rgba(0,0,0,.45); }
      @media (min-width: 768px) { .sidebar-backdrop { display: none !important; } }

      /* Cabecera (header) en navy con título blanco */
      .app-header { background: var(--brand-navy) !important; box-shadow: 0 1px 3px rgba(0,0,0,.25) !important; }
      .app-header h1, .app-header h2, .app-header h3 { color: #fff !important; }
      .app-header .page-head-title { color: #fff !important; }
      /* Subtítulos / texto gris secundario del header -> blanco translúcido */
      .app-header p,
      .app-header .text-gray-400,
      .app-header .text-gray-500,
      .app-header .text-gray-600 { color: rgba(255,255,255,.72) !important; }
      .app-header .menu-toggle-btn { color: #fff !important; border-color: rgba(255,255,255,.3) !important; }
      .app-header .menu-toggle-btn:hover { background: rgba(255,255,255,.1) !important; }

      /* Barra superior del inbox de WhatsApp (layout propio de pantalla completa) en navy */
      .wa-topbar { background: var(--brand-navy) !important; border-bottom-color: rgba(255,255,255,.1) !important; }
      .wa-topbar .text-gray-900 { color: #fff !important; }
      .wa-topbar .text-gray-400 { color: rgba(255,255,255,.6) !important; }
      .wa-topbar .menu-toggle-btn { color: #fff !important; border-color: rgba(255,255,255,.3) !important; }
      .wa-topbar .hover\:text-gray-600:hover { color: #fff !important; }
      .wa-topbar { padding-right: 3.5rem !important; } /* reserva espacio para la campana */

      /* ===== Campana de notificaciones (fija arriba a la derecha) ===== */
      .notif-bell { position: fixed; top: 2.25rem; right: 1rem; z-index: 40; transform: translateY(-50%); }
      .notif-bell-btn { position: relative; display: inline-flex; align-items: center; justify-content: center;
        padding: .5rem; border-radius: .6rem; color: #fff; background: transparent; border: none; cursor: pointer; transition: background .15s; }
      .notif-bell-btn:hover { background: rgba(255,255,255,.14); }
      .notif-bell-btn svg { width: 1.4rem; height: 1.4rem; }
      .notif-badge { position: absolute; top: 1px; right: 1px; min-width: 1.05rem; height: 1.05rem;
        padding: 0 .25rem; border-radius: 999px; background: #dc2626; color: #fff; font-size: .62rem;
        font-weight: 700; line-height: 1; display: inline-flex; align-items: center; justify-content: center;
        box-shadow: 0 0 0 2px var(--brand-navy); }
      .notif-panel { position: absolute; top: calc(100% + .5rem); right: 0; width: 20rem; max-width: calc(100vw - 1.5rem);
        max-height: 24rem; overflow-y: auto; background: #fff; color: #374151; border: 1px solid #e5e7eb;
        border-radius: .75rem; box-shadow: 0 12px 28px rgba(15,23,42,.18); }
      .notif-head { position: sticky; top: 0; background: #fff; display: flex; align-items: center; justify-content: space-between;
        gap: .5rem; padding: .65rem .85rem; border-bottom: 1px solid #f3f4f6; font-size: .82rem; font-weight: 700; color: var(--brand-navy); }
      .notif-head button { font-size: .68rem; font-weight: 600; color: var(--brand-gold-dark); background: none; border: none; cursor: pointer; white-space: nowrap; }
      .notif-empty { padding: 1.75rem 1rem; text-align: center; font-size: .8rem; color: #9ca3af; }
      .notif-item { display: block; padding: .6rem .85rem; border-bottom: 1px solid #f3f4f6; text-decoration: none; transition: background .12s; }
      .notif-item:hover { background: #f9fafb; }
      .notif-unread { background: var(--brand-gold-light); }
      .notif-title { font-size: .8rem; font-weight: 600; color: var(--brand-navy); }
      .notif-sub { font-size: .7rem; color: #6b7280; margin-top: .12rem; }
      .notif-client { font-weight: 600; color: #4b5563; }
      .notif-head-actions { display: inline-flex; align-items: center; gap: .25rem; }
      .notif-icon-btn { display: inline-flex; align-items: center; justify-content: center; padding: .25rem; border-radius: .35rem; background: none; border: none; cursor: pointer; color: #64748b; }
      .notif-icon-btn:hover { background: #f3f4f6; color: var(--brand-navy); }
      .notif-icon-btn svg { width: 1.05rem; height: 1.05rem; }
      .notif-link { background: none; border: none; cursor: pointer; font-size: .7rem; font-weight: 600; color: var(--brand-gold-dark); white-space: nowrap; }
      .notif-subhead { padding: .4rem .85rem; border-bottom: 1px solid #f3f4f6; text-align: right; }
      .notif-settings { padding: .35rem 0 .5rem; }
      .notif-row { display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .5rem .9rem; font-size: .78rem; color: #374151; cursor: pointer; }
      .notif-row:hover { background: #f9fafb; }
      .notif-row input[type="checkbox"] { width: 1rem; height: 1rem; flex-shrink: 0; accent-color: var(--brand-navy); cursor: pointer; }
      .notif-pipe { padding: .35rem .9rem .35rem 1.4rem; font-size: .76rem; color: #4b5563; }
      .notif-sep { height: 1px; background: #f3f4f6; margin: .25rem 0; }
      .notif-settings-foot { padding: .6rem .9rem .25rem; }
      .notif-save { width: 100%; padding: .5rem; border: none; border-radius: .5rem; background: var(--brand-navy);
        color: #fff; font-size: .78rem; font-weight: 600; cursor: pointer; }
      .notif-save:hover { background: var(--brand-navy-dark); }
    </style>
  </head>

  <body class="font-sans antialiased dark:bg-slate-900 dark:text-slate-100">
    <x-banner />

    @php
      // Aviso de licencia próxima a vencer / prórroga activa (cuenta del cliente).
      $licenseBanner = null;
      $bu = Auth::user();
      $bt = $bu?->currentTeam;
      if ($bt && !$bu->isSuperAdmin() && !($bt->owner && $bt->owner->isSuperAdmin())) {
          $bl = \App\Models\TeamLicense::where('team_id', $bt->id)->first();
          if ($bl && $bl->is_active && !$bl->is_expired) {
              $brem  = $bl->remaining_days;
              $bdays = $brem !== null ? max(0, (int) ceil($brem)) : null;
              $btoday = now()->setTimezone($bt->effectiveTimezone())->format('Y-m-d');
              if ($bl->grant_type === 'prorroga') {
                  $licenseBanner = ['type' => 'prorroga', 'days' => $bdays, 'key' => "licbanner-{$bt->id}-prorroga-{$btoday}"];
              } elseif ($bdays !== null && $bdays >= 1 && $bdays <= 7) {
                  $licenseBanner = ['type' => 'soon', 'days' => $bdays, 'key' => "licbanner-{$bt->id}-soon-{$btoday}"];
              }
          }
      }
    @endphp

    <div x-data
         class="main-wrap min-h-screen bg-gray-100 dark:bg-slate-900"
         :class="$store.sidebar.open ? 'sidebar-open' : ''">
      @livewire('navigation-menu')

      @include('partials.notification-bell')

      {{-- Aviso elegante de licencia (próxima a vencer / prórroga), cerrable por día --}}
      @if ($licenseBanner)
        @php
          $lbDays   = $licenseBanner['days'];
          $lbDayTxt = $lbDays === 1 ? __('1 día') : __(':days días', ['days' => $lbDays]);
          if ($licenseBanner['type'] === 'prorroga') {
              $lbBg='#fee2e2'; $lbFg='#991b1b'; $lbBtn='#dc2626'; $lbBorder='#fecaca';
              $lbTitle=__('Periodo de prórroga activo');
              $lbMsg=__('Tu licencia finalizó y se activó una prórroga de la cual te queda(n) :days para que exportes tu data o realices el proceso que desees. Al finalizar la prórroga, tu cuenta quedará bloqueada automáticamente y perderás el acceso. Comunícate con soporte para renovar tu servicio o recibir la ayuda que necesites.', ['days' => $lbDayTxt]);
          } else {
              $lbBg='#fef3c7'; $lbFg='#92400e'; $lbBtn='#d97706'; $lbBorder='#fde68a';
              $lbTitle=__('Tu licencia está próxima a vencer');
              $lbMsg=__('La licencia de esta cuenta vence pronto: te queda(n) :days. Comunícate con soporte para renovar tu servicio y evitar que tu cuenta se bloquee.', ['days' => $lbDayTxt]);
          }
        @endphp
        <div x-data="{ show: (function(){ try { return localStorage.getItem('{{ $licenseBanner['key'] }}') !== '1'; } catch(e){ return true; } })() }"
             x-show="show" x-cloak
             style="background:{{ $lbBg }};border-bottom:1px solid {{ $lbBorder }};">
          <div style="max-width:1180px;margin:0 auto;padding:11px 18px;display:flex;align-items:center;gap:14px;">
            <svg style="width:24px;height:24px;color:{{ $lbBtn }};flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
            </svg>
            <p style="flex:1;min-width:0;color:{{ $lbFg }};font-size:13px;line-height:1.45;margin:0;">
              <span style="font-weight:800;">{{ $lbTitle }}.</span>
              {{ $lbMsg }}
            </p>
            <a href="{{ route('soporte') }}"
               style="flex-shrink:0;display:inline-flex;align-items:center;gap:6px;background:{{ $lbBtn }};color:#fff;font-size:12.5px;font-weight:700;padding:8px 15px;border-radius:9px;text-decoration:none;white-space:nowrap;">
              <svg style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 11-12.728 0M12 3v6m-3.536 1.464a5 5 0 107.072 0"/>
              </svg>
              {{ __('Comunicarme con soporte') }}
            </a>
            <button type="button"
                    @click="show=false; try{ localStorage.setItem('{{ $licenseBanner['key'] }}','1'); }catch(e){}"
                    title="{{ __('Cerrar') }}"
                    style="flex-shrink:0;background:transparent;border:none;cursor:pointer;color:{{ $lbFg }};opacity:.65;padding:4px;line-height:0;">
              <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      @endif

      @if (isset($header))
        <header class="app-header shadow">
          {{-- Reserva espacio a la izquierda cuando el menú está minimizado
               para que el botón flotante de despliegue no tape el título. --}}
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"
               :style="(!$store.sidebar.open ? 'padding-left:3.75rem;' : '') + 'padding-right:3.5rem;'">
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
            el.textContent = t === 'dark' ? @json(__('Tema oscuro')) : @json(__('Tema claro'));
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
