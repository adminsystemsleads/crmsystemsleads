<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'MiComuniApp') }} - Gestión de condominios y edificios</title>

        <meta name="description" content="MiComuniApp es el sistema de gestión para condominios, edificios y residenciales que centraliza pagos, incidencias, comunicación con vecinos y reportes en un solo lugar.">
        <meta name="keywords" content="condominios, edificios, gestión de condominios, administración, residentes, MiComuniApp">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- CSS de tu app (Tailwind de Laravel Breeze) -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <!-- Estilos propios de marca (NO dependen de Tailwind) -->
        <style>
            :root {
                --brand-orange: #F28A1E;
                --brand-navy:   #0F3555;
            }

            body {
                font-family: 'Instrument Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                background-color: #f8fafc;
                color: #0f172a;
            }

            .bg-gradient-hero{
                background: radial-gradient(circle at top left, var(--brand-orange) 0, var(--brand-navy) 40%, #020617 100%);
            }
            .bg-orange-brand{ background-color: var(--brand-orange) !important; }
            .text-orange-brand{ color: var(--brand-orange) !important; }
            .bg-navy-brand{ background-color: var(--brand-navy) !important; }
            .text-navy-brand{ color: var(--brand-navy) !important; }
            .bg-orange-soft{ background-color: rgba(242,138,30,0.06) !important; }
            .bg-navy-soft{ background-color: rgba(15,53,85,0.06) !important; }

            /* Botones de marca */
            .btn-brand-primary{
                display:inline-flex;
                align-items:center;
                justify-content:center;
                padding:0.7rem 1.7rem;
                border-radius:9999px;
                border:none;
                background-color:var(--brand-orange);
                color:#ffffff;
                font-weight:600;
                font-size:0.9rem;
                text-decoration:none;
                transition:all .15s ease;
                box-shadow:0 10px 25px rgba(0,0,0,0.12);
            }
            .btn-brand-primary:hover{
                background-color:#d97311;
                transform:translateY(1px);
                box-shadow:0 16px 35px rgba(0,0,0,0.18);
            }

            .btn-brand-outline{
                display:inline-flex;
                align-items:center;
                justify-content:center;
                padding:0.7rem 1.7rem;
                border-radius:9999px;
                border:1px solid rgba(255,255,255,0.4);
                background-color:rgba(255,255,255,0.08);
                color:#ffffff;
                font-weight:500;
                font-size:0.9rem;
                text-decoration:none;
                transition:all .15s ease;
            }
            .btn-brand-outline:hover{
                background-color:rgba(255,255,255,0.16);
            }

            .btn-header-outline{
                display:inline-flex;
                align-items:center;
                justify-content:center;
                padding:0.45rem 1.3rem;
                border-radius:9999px;
                border:1px solid #e2e8f0;
                background-color:#ffffff;
                color:#0f172a;
                font-size:0.8rem;
                font-weight:500;
                text-decoration:none;
                transition:all .15s ease;
            }
            .btn-header-outline:hover{
                background-color:#f8fafc;
            }

            .btn-header-solid{
                display:inline-flex;
                align-items:center;
                justify-content:center;
                padding:0.45rem 1.3rem;
                border-radius:9999px;
                border:none;
                background-color:var(--brand-orange);
                color:#ffffff;
                font-size:0.8rem;
                font-weight:600;
                text-decoration:none;
                transition:all .15s ease;
                box-shadow:0 8px 18px rgba(0,0,0,0.12);
            }
            .btn-header-solid:hover{
                background-color:#d97311;
                transform:translateY(1px);
            }

            /* Pequeños ajustes genericos por si Tailwind no está */
            .max-w-6xl{max-width:72rem;margin-left:auto;margin-right:auto;}
            .max-w-xl{max-width:36rem;}
            .max-w-md{max-width:28rem;}
            .px-4{padding-left:1rem;padding-right:1rem;}
            .px-8{padding-left:2rem;padding-right:2rem;}
            .py-4{padding-top:1rem;padding-bottom:1rem;}
            .py-6{padding-top:1.5rem;padding-bottom:1.5rem;}
            .py-8{padding-top:2rem;padding-bottom:2rem;}
            .py-12{padding-top:3rem;padding-bottom:3rem;}
            .py-16{padding-top:4rem;padding-bottom:4rem;}
            .rounded-2xl{border-radius:1rem;}
            .rounded-3xl{border-radius:1.5rem;}
            .shadow-md{box-shadow:0 4px 6px rgba(15,23,42,0.12);}
            .shadow-lg{box-shadow:0 10px 25px rgba(15,23,42,0.18);}
            .border{border:1px solid #e2e8f0;}
            .border-t{border-top:1px solid #e2e8f0;}
            .text-center{text-align:center;}
        </style>

        <style>
            /* Tamaño por defecto (móvil / tablet) */
            .micomuniapp-logo {
                height: 80px;
                width: auto;
            }

            /* En escritorio (>= 1024px) se ve como te gustó: ~120px */
            @media (min-width: 1024px) {
                .micomuniapp-logo {
                    height: 120px;
                }
            }
        </style>
    </head>

    <body class="min-h-screen bg-slate-50 text-slate-900 flex flex-col">
        <!-- HEADER -->
        <header class="bg-white border-b border-slate-200">
            <div class="max-w-6xl mx-auto px-4 lg:px-8 py-2 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                        <!-- Logo más grande -->
                        <img src="https://systemsleads.com/wp-content/uploads/2025/11/logo1_micomuniapp.png"
                            alt="MiComuniApp"
                            class="block micomuniapp-logo">

                    </a>
                    <div class="hidden lg:block">
                        <p class="text-xs uppercase tracking-wide text-navy-brand font-semibold">
                            GESTIÓN INTEGRAL DE CONDOMINIOS, EDIFICIOS Y RESIDENCIALES
                        </p>
                    </div>
                </div>

                @if (Route::has('login'))
                    <nav class="flex items-center justify-end gap-3 text-sm">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-header-outline">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-header-outline">
                                Ingresar
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-header-solid">
                                    Crear cuenta
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <!-- MAIN -->
        <main class="flex-1">
            <!-- HERO -->
            <section class="bg-gradient-hero text-white">
                <div class="max-w-6xl mx-auto px-4 lg:px-8 py-12 lg:py-16 flex flex-col lg:flex-row items-center gap-10">
                    <div class="flex-1 max-w-xl">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-soft text-xs font-semibold uppercase tracking-wide text-orange-brand mb-4" style="border:1px solid rgba(255,255,255,0.4);">
                            Plataforma para condominios, edificios y residenciales
                        </span>
                        <h1 class="text-3xl lg:text-4xl font-bold leading-tight mb-3">
                            Controla tu condominio<br>
                            <span class="text-orange-brand">en una sola plataforma</span>
                        </h1>
                        <p class="text-sm lg:text-base text-slate-100" style="opacity:0.9; margin-bottom:1.5rem;">
                            MiComuniApp centraliza pagos, incidencias, reservas, comunicaciones y reportes
                            para administradores y residentes. Menos caos en WhatsApp, más orden y transparencia
                            en tu comunidad.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-3 mb-4">
                            <a
                                href="{{ Route::has('register') ? route('register') : '#' }}"
                                class="btn-brand-primary"
                            >
                                Probar MiComuniApp
                            </a>
                            <a
                                href="#planes"
                                class="btn-brand-outline"
                            >
                                Ver planes y módulos
                            </a>
                        </div>

                        <div class="flex flex-wrap gap-4 text-xs text-slate-100" style="opacity:0.9;">
                            <div class="flex items-center gap-2">
                                <span style="width:8px;height:8px;border-radius:9999px;background-color:#22c55e;"></span>
                                <span>Pagos y morosidad en tiempo real</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span style="width:8px;height:8px;border-radius:9999px;background-color:var(--brand-orange);"></span>
                                <span>Incidencias y mantenimiento trazables</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span style="width:8px;height:8px;border-radius:9999px;background-color:#38bdf8;"></span>
                                <span>App pensada para administradores y vecinos</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 w-full max-w-md">
                        <div class="bg-white/10 border border-white/20 rounded-3xl p-4 lg:p-5 shadow-lg backdrop-blur-sm">
                            <div class="bg-white text-slate-900 rounded-2xl p-4 lg:p-5 shadow-md">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <p class="text-xs text-slate-500 mb-1">Resumen del condominio</p>
                                        <p class="text-lg font-semibold text-navy-brand">Residencial Los Olivos</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium" style="background-color:#ecfdf3;color:#047857;">
                                        92% pagos al día
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="rounded-xl bg-orange-soft p-3">
                                        <p class="text-xs text-slate-500 mb-1">Cuotas cobradas</p>
                                        <p class="text-xl font-bold text-orange-brand">S/ 38,450</p>
                                        <p class="text-[0.7rem] text-slate-500 mt-1">Este mes</p>
                                    </div>
                                    <div class="rounded-xl bg-navy-soft p-3">
                                        <p class="text-xs text-slate-500 mb-1">Incidencias abiertas</p>
                                        <p class="text-xl font-bold text-navy-brand">08</p>
                                        <p class="text-[0.7rem] text-slate-500 mt-1">3 en mantenimiento</p>
                                    </div>
                                </div>

                                <div class="border border-slate-100 rounded-xl p-3 mb-3">
                                    <p class="text-xs font-semibold text-slate-600 mb-2">Comunicados recientes</p>
                                    <ul class="space-y-1.5 text-xs text-slate-600">
                                        <li>• Corte programado de agua – Torre B</li>
                                        <li>• Actualización de reglamento interno</li>
                                        <li>• Nuevo horario de uso de piscina</li>
                                    </ul>
                                </div>

                                <button
                                    type="button"
                                    class="w-full mt-2 btn-header-solid"
                                    style="justify-content:center;"
                                >
                                    Ver panel de administrador
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- BENEFICIOS PRINCIPALES -->
            <section id="funcionalidades" class="max-w-6xl mx-auto px-4 lg:px-8 py-12">
                <div class="text-center mb-8 max-w-xl mx-auto">
                    <h2 class="text-2xl lg:text-3xl font-bold text-navy-brand mb-2">
                        Todo lo que necesita tu condominio<br> en un mismo sistema
                    </h2>
                    <p class="text-sm text-slate-600">
                        Reduce la carga operativa del administrador, mejora la transparencia con los vecinos
                        y centraliza la información clave de tu edificio o residencial.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Card 1 -->
                    <div class="bg-white rounded-2xl shadow-md border p-5">
                        <div class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-orange-soft mb-3">
                            <span class="text-orange-brand text-lg">₿</span>
                        </div>
                        <h3 class="text-base font-semibold text-navy-brand mb-1">Pagos y morosidad</h3>
                        <p class="text-sm text-slate-600 mb-2">
                            Registra cuotas, mantenimientos y otros cargos. Visualiza quién está al día,
                            quién está en mora y envía recordatorios.
                        </p>
                        <ul class="text-xs text-slate-500 space-y-1">
                            <li>• Estado de cuenta por departamento</li>
                            <li>• Reportes por periodo y tipo de gasto</li>
                            <li>• Exportación de datos para contabilidad</li>
                        </ul>
                    </div>

                    <!-- Card 2 -->
                    <div class="bg-white rounded-2xl shadow-md border p-5">
                        <div class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-navy-soft mb-3">
                            <span class="text-navy-brand text-lg">🛠</span>
                        </div>
                        <h3 class="text-base font-semibold text-navy-brand mb-1">Incidencias y mantenimiento</h3>
                        <p class="text-sm text-slate-600 mb-2">
                            Registra problemas de áreas comunes, asigna responsables y da seguimiento
                            hasta su solución.
                        </p>
                        <ul class="text-xs text-slate-500 space-y-1">
                            <li>• Alta de incidencias por administrador o vecino</li>
                            <li>• Estados: abierto, en gestión, resuelto</li>
                            <li>• Historial y evidencia fotográfica</li>
                        </ul>
                    </div>

                    <!-- Card 3 -->
                    <div class="bg-white rounded-2xl shadow-md border p-5">
                        <div class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-orange-soft mb-3">
                            <span class="text-orange-brand text-lg">📣</span>
                        </div>
                        <h3 class="text-base font-semibold text-navy-brand mb-1">Comunicados y avisos</h3>
                        <p class="text-sm text-slate-600 mb-2">
                            Publica avisos oficiales, acuerdos y documentos importantes en un muro
                            digital que los vecinos pueden consultar en cualquier momento.
                        </p>
                        <ul class="text-xs text-slate-500 space-y-1">
                            <li>• Filtros por torre, edificio o bloque</li>
                            <li>• Adjuntos: PDFs, imágenes y reglamentos</li>
                            <li>• Historial de comunicados</li>
                        </ul>
                    </div>

                    <!-- Card 4 -->
                    <div class="bg-white rounded-2xl shadow-md border p-5">
                        <div class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-navy-soft mb-3">
                            <span class="text-navy-brand text-lg">📅</span>
                        </div>
                        <h3 class="text-base font-semibold text-navy-brand mb-1">Reservas de áreas comunes</h3>
                        <p class="text-sm text-slate-600 mb-2">
                            Controla el uso de áreas como sala de usos múltiples, parrillas, gimnasio o piscina
                            con un calendario claro y ordenado.
                        </p>
                        <ul class="text-xs text-slate-500 space-y-1">
                            <li>• Reglas de horarios y aforos</li>
                            <li>• Registro de responsables por reserva</li>
                            <li>• Reporte de uso por periodo</li>
                        </ul>
                    </div>

                    <!-- Card 5 -->
                    <div class="bg-white rounded-2xl shadow-md border p-5">
                        <div class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-orange-soft mb-3">
                            <span class="text-orange-brand text-lg">🧑‍💼</span>
                        </div>
                        <h3 class="text-base font-semibold text-navy-brand mb-1">Gestión de residentes</h3>
                        <p class="text-sm text-slate-600 mb-2">
                            Ten siempre actualizado el padrón de propietarios e inquilinos, contactos de
                            emergencia y datos clave de cada unidad.
                        </p>
                        <ul class="text-xs text-slate-500 space-y-1">
                            <li>• Unidades por torre, edificio o bloque</li>
                            <li>• Propietario, inquilino y contacto alterno</li>
                            <li>• Etiquetas para clasificar residentes</li>
                        </ul>
                    </div>

                    <!-- Card 6 -->
                    <div class="bg-white rounded-2xl shadow-md border p-5">
                        <div class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-navy-soft mb-3">
                            <span class="text-navy-brand text-lg">📊</span>
                        </div>
                        <h3 class="text-base font-semibold text-navy-brand mb-1">Reportes y transparencia</h3>
                        <p class="text-sm text-slate-600 mb-2">
                            Entrega información clara a la asamblea: ingresos, egresos, saldos por unidad
                            y avance de proyectos.
                        </p>
                        <ul class="text-xs text-slate-500 space-y-1">
                            <li>• Panel de indicadores para administración</li>
                            <li>• Reportes descargables en Excel / PDF</li>
                            <li>• Soporte para auditorías internas</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- ADMINISTRADORES vs RESIDENTES -->
            <section class="max-w-6xl mx-auto px-4 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div class="space-y-4">
                        <h2 class="text-2xl font-bold text-navy-brand">
                            Pensado para administradores
                        </h2>
                        <p class="text-sm text-slate-600">
                            Reduce llamadas, correos y mensajes dispersos. Con MiComuniApp, toda la operación
                            del condominio se gestiona desde un panel claro y centralizado.
                        </p>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li>• Visualiza el estado de todos tus condominios y edificios.</li>
                            <li>• Historial de pagos, acuerdos e incidencias.</li>
                            <li>• Comunicación directa con el consejo directivo.</li>
                            <li>• Documentación lista para cambios de administración.</li>
                        </ul>
                    </div>
                    <div class="space-y-4">
                        <h2 class="text-2xl font-bold text-navy-brand">
                            Fácil para residentes
                        </h2>
                        <p class="text-sm text-slate-600">
                            Los vecinos tienen un espacio único donde ver avisos, pagos pendientes, reservas
                            y reglas del condominio, sin perderse en chats infinitos.
                        </p>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li>• Acceso rápido a comunicados y documentos oficiales.</li>
                            <li>• Consulta de deudas y pagos realizados.</li>
                            <li>• Registro de incidencias con fotos y comentarios.</li>
                            <li>• Mayor transparencia y trazabilidad en la gestión.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- PLANES Y MÓDULOS -->
            <section id="planes" class="bg-white border-t border-slate-200">
                <div class="max-w-6xl mx-auto px-4 lg:px-8 py-12">
                    <div class="text-center mb-8 max-w-xl mx-auto">
                        <h2 class="text-2xl lg:text-3xl font-bold text-navy-brand mb-2">
                            Planes y módulos para tu comunidad
                        </h2>
                        <p class="text-sm text-slate-600">
                            Elige el plan que mejor se adapta al tamaño de tu condominio o edificio.
                            Todos los planes incluyen soporte y mejoras continuas.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Plan Básico -->
                        <div class="bg-slate-50 rounded-2xl border shadow-md p-6 flex flex-col">
                            <p class="text-xs font-semibold text-navy-brand uppercase tracking-wide mb-2">
                                Plan Básico
                            </p>
                            <p class="text-2xl font-bold text-navy-brand mb-1">Pequeños condominios</p>
                            <p class="text-xs text-slate-500 mb-4">
                                Ideal para edificios con pocas unidades que quieren ordenar pagos y comunicados.
                            </p>
                            <p class="text-3xl font-bold text-orange-brand mb-4">US$ XX<span class="text-base text-slate-500"> / mes</span></p>
                            <ul class="text-xs text-slate-600 space-y-2 mb-6">
                                <li>• Hasta X unidades registradas</li>
                                <li>• Gestión de cuotas y morosidad</li>
                                <li>• Comunicados y documentos básicos</li>
                                <li>• Soporte por correo</li>
                            </ul>
                            <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn-brand-primary" style="margin-top:auto;">
                                Empezar con el Plan Básico
                            </a>
                        </div>

                        <!-- Plan Recomendado -->
                        <div class="bg-navy-brand rounded-2xl shadow-lg p-6 border border-orange-brand flex flex-col">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-orange-brand uppercase tracking-wide">
                                    Plan Recomendado
                                </p>
                                <span class="px-2 py-1 rounded-full text-[0.7rem] font-semibold bg-orange-soft text-orange-brand">
                                    Más elegido
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-white mb-1">Condominios en crecimiento</p>
                            <p class="text-xs text-slate-100 mb-4" style="opacity:0.9;">
                                Para residenciales con varias torres o bloques que requieren más control y reportes.
                            </p>
                            <p class="text-3xl font-bold text-orange-brand mb-4">US$ YY<span class="text-base text-slate-200"> / mes</span></p>
                            <ul class="text-xs text-slate-100 space-y-2 mb-6" style="opacity:0.95;">
                                <li>• Hasta XX unidades registradas</li>
                                <li>• Pagos, morosidad e incidencias</li>
                                <li>• Reservas de áreas comunes</li>
                                <li>• Reportes financieros detallados</li>
                                <li>• Soporte prioritario</li>
                            </ul>
                            <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn-brand-primary" style="background-color:#F28A1E;color:#fff;margin-top:auto;">
                                Contratar plan recomendado
                            </a>
                        </div>

                        <!-- Plan Completo -->
                        <div class="bg-slate-50 rounded-2xl border shadow-md p-6 flex flex-col">
                            <p class="text-xs font-semibold text-navy-brand uppercase tracking-wide mb-2">
                                Plan Complejo Residencial
                            </p>
                            <p class="text-2xl font-bold text-navy-brand mb-1">Grandes complejos</p>
                            <p class="text-xs text-slate-500 mb-4">
                                Para condominios con muchas unidades, varios accesos y alta rotación de residentes.
                            </p>
                            <p class="text-3xl font-bold text-orange-brand mb-4">US$ ZZ<span class="text-base text-slate-500"> / mes</span></p>
                            <ul class="text-xs text-slate-600 space-y-2 mb-6">
                                <li>• Unidades ilimitadas</li>
                                <li>• Múltiples administradores y roles</li>
                                <li>• Módulos avanzados de reportes</li>
                                <li>• Integraciones a medida</li>
                                <li>• Acompañamiento en la implementación</li>
                            </ul>
                            <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn-brand-primary" style="margin-top:auto;">
                                Hablar con ventas
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section class="max-w-6xl mx-auto px-4 lg:px-8 py-12">
                <div class="text-center mb-8 max-w-xl mx-auto">
                    <h2 class="text-2xl lg:text-3xl font-bold text-navy-brand mb-2">
                        Preguntas frecuentes
                    </h2>
                    <p class="text-sm text-slate-600">
                        Resolvemos las dudas más comunes de administradores y juntas de propietarios.
                    </p>
                </div>

                <div class="space-y-4 max-w-3xl mx-auto">
                    <div class="bg-white border rounded-2xl shadow-sm p-4">
                        <h3 class="text-sm font-semibold text-navy-brand mb-1">
                            ¿MiComuniApp reemplaza los grupos de WhatsApp?
                        </h3>
                        <p class="text-sm text-slate-600">
                            No elimina los grupos, pero te permite centralizar avisos oficiales, pagos, incidencias
                            y reportes en un sistema ordenado. Así, los acuerdos importantes no se pierden en el chat.
                        </p>
                    </div>

                    <div class="bg-white border rounded-2xl shadow-sm p-4">
                        <h3 class="text-sm font-semibold text-navy-brand mb-1">
                            ¿Puedo usar MiComuniApp si administro varios condominios?
                        </h3>
                        <p class="text-sm text-slate-600">
                            Sí. Desde un mismo usuario puedes gestionar varios edificios o residenciales,
                            con información y reportes separados por cada uno.
                        </p>
                    </div>

                    <div class="bg-white border rounded-2xl shadow-sm p-4">
                        <h3 class="text-sm font-semibold text-navy-brand mb-1">
                            ¿Los residentes necesitan una capacitación especial?
                        </h3>
                        <p class="text-sm text-slate-600">
                            No. La interfaz está pensada para que puedan consultar avisos, pagos y reservas
                            de forma muy simple, incluso desde el móvil.
                        </p>
                    </div>

                    <div class="bg-white border rounded-2xl shadow-sm p-4">
                        <h3 class="text-sm font-semibold text-navy-brand mb-1">
                            ¿Qué soporte ofrecen si tengo dudas o incidencias?
                        </h3>
                        <p class="text-sm text-slate-600">
                            Dependiendo del plan, tienes soporte por correo, chat o acompañamiento en la implementación.
                            Nuestro objetivo es que la puesta en marcha sea rápida y ordenada.
                        </p>
                    </div>
                </div>
            </section>

            <!-- CTA FINAL -->
            <section class="bg-white border-t border-slate-200">
                <div class="max-w-6xl mx-auto px-4 lg:px-8 py-12">
                    <div class="bg-navy-brand text-white rounded-2xl px-6 py-8 lg:px-8 lg:py-10 flex flex-col lg:flex-row items-center justify-between gap-6 shadow-lg">
                        <div class="max-w-xl">
                            <h2 class="text-2xl font-bold mb-2 leading-tight">
                                Da el siguiente paso en la gestión de tu condominio
                            </h2>
                            <p class="text-sm text-slate-100" style="opacity:0.9;margin-bottom:0.75rem;">
                                MiComuniApp te ayuda a organizar la operación diaria, mejorar la comunicación con vecinos
                                y tener información financiera clara y actualizada.
                            </p>
                            <p class="text-xs text-slate-200" style="opacity:0.85;">
                                Ideal para condominios, edificios multifamiliares, residenciales y complejos cerrados.
                            </p>
                        </div>
                        <div class="flex flex-col items-stretch lg:items-end gap-3 w-full max-w-xs">
                            <a
                                href="{{ Route::has('register') ? route('register') : '#' }}"
                                class="btn-brand-primary"
                                style="width:100%;text-align:center;"
                            >
                                Crear cuenta de administrador
                            </a>
                            <a
                                href="{{ Route::has('login') ? route('login') : '#' }}"
                                class="btn-brand-outline"
                                style="width:100%;text-align:center;border-color:rgba(255,255,255,0.5);"
                            >
                                Ya tengo una cuenta
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- FOOTER -->
        <footer class="border-t border-slate-200 bg-white">
            <div class="max-w-6xl mx-auto px-4 lg:px-8 py-4 flex flex-col lg:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                <p>© {{ date('Y') }} MiComuniApp. Plataforma de gestión de condominios y edificios.</p>
                <p class="text-right">
                    Desarrollado por Systems Leads
                </p>
            </div>
        </footer>
    </body>
</html>
