<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>QipuCRM — CRM inteligente para equipos de ventas</title>
  <meta name="description" content="QipuCRM centraliza tus negociaciones, contactos, WhatsApp y facturación electrónica en un solo sistema. Hecho para equipos de ventas en Perú.">

  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet"/>

  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  @endif

  <style>
    :root {
      --indigo: #4f46e5;
      --indigo-dark: #3730a3;
      --indigo-light: #eef2ff;
      --violet: #7c3aed;
      --slate-50: #f8fafc;
      --slate-100: #f1f5f9;
      --slate-200: #e2e8f0;
      --slate-600: #475569;
      --slate-900: #0f172a;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Instrument Sans', system-ui, sans-serif;
      background: var(--slate-50);
      color: var(--slate-900);
    }
    a { text-decoration: none; }

    /* Layout helpers */
    .container { max-width: 72rem; margin: 0 auto; padding: 0 1.25rem; }
    .flex       { display: flex; }
    .grid-2     { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
    .grid-3     { display: grid; grid-template-columns: repeat(3,1fr); gap: 1.5rem; }

    /* Hero gradient */
    .hero-bg {
      background: linear-gradient(135deg, #1e1b4b 0%, #312e81 35%, #4f46e5 70%, #7c3aed 100%);
    }

    /* Buttons */
    .btn-primary {
      display: inline-flex; align-items: center; justify-content: center; gap: .45rem;
      padding: .7rem 1.6rem; border-radius: 9999px; border: none;
      background: var(--indigo); color: #fff;
      font-weight: 600; font-size: .9rem; cursor: pointer;
      box-shadow: 0 8px 24px rgba(79,70,229,.35);
      transition: all .15s ease;
    }
    .btn-primary:hover { background: var(--indigo-dark); transform: translateY(-1px); box-shadow: 0 12px 30px rgba(79,70,229,.4); }

    .btn-ghost {
      display: inline-flex; align-items: center; justify-content: center;
      padding: .7rem 1.6rem; border-radius: 9999px;
      border: 1.5px solid rgba(255,255,255,.35);
      background: rgba(255,255,255,.08); color: #fff;
      font-weight: 500; font-size: .9rem;
      transition: all .15s ease;
    }
    .btn-ghost:hover { background: rgba(255,255,255,.16); }

    .btn-nav {
      display: inline-flex; align-items: center; justify-content: center;
      padding: .4rem 1.2rem; border-radius: 9999px;
      border: 1.5px solid var(--slate-200);
      background: #fff; color: var(--slate-900);
      font-size: .8rem; font-weight: 500;
      transition: all .15s ease;
    }
    .btn-nav:hover { background: var(--slate-100); }
    .btn-nav-solid {
      display: inline-flex; align-items: center; justify-content: center;
      padding: .4rem 1.2rem; border-radius: 9999px; border: none;
      background: var(--indigo); color: #fff;
      font-size: .8rem; font-weight: 600;
      transition: all .15s ease;
    }
    .btn-nav-solid:hover { background: var(--indigo-dark); }

    /* Cards */
    .card {
      background: #fff;
      border: 1px solid var(--slate-200);
      border-radius: 1rem;
      padding: 1.4rem;
      box-shadow: 0 1px 6px rgba(15,23,42,.06);
    }
    .card-icon {
      width: 2.4rem; height: 2.4rem; border-radius: .7rem;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: .85rem;
      background: var(--indigo-light);
    }

    /* Sections */
    section { padding: 4.5rem 0; }
    .section-label {
      display: inline-flex; align-items: center; gap: .4rem;
      padding: .3rem .9rem; border-radius: 9999px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.25);
      font-size: .7rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: .08em;
      color: #c7d2fe; margin-bottom: 1rem;
    }
    .section-title {
      font-size: clamp(1.6rem, 3vw, 2.2rem);
      font-weight: 800; line-height: 1.15;
    }
    .section-sub { font-size: .95rem; color: var(--slate-600); margin-top: .6rem; line-height: 1.7; }

    /* Badge */
    .badge {
      display: inline-block; padding: .2rem .7rem; border-radius: 9999px;
      font-size: .7rem; font-weight: 700;
    }
    .badge-indigo { background: var(--indigo-light); color: var(--indigo); }

    /* Feature pill list */
    .pill-list { list-style: none; display: flex; flex-direction: column; gap: .55rem; margin-top: .8rem; }
    .pill-list li {
      display: flex; align-items: center; gap: .6rem;
      font-size: .85rem; color: var(--slate-600);
    }
    .pill-list li::before {
      content: '';
      display: inline-block; width: .45rem; height: .45rem;
      border-radius: 9999px; background: var(--indigo); flex-shrink: 0;
    }

    /* Highlight number */
    .stat-number { font-size: 2rem; font-weight: 800; color: var(--indigo); }

    @media (max-width: 768px) {
      .grid-2, .grid-3 { grid-template-columns: 1fr; }
      .hide-mobile { display: none !important; }
    }
  </style>
</head>
<body>

<!-- ===================== HEADER ===================== -->
<header style="background:#fff; border-bottom:1px solid var(--slate-200); position:sticky; top:0; z-index:50;">
  <div class="container" style="padding-top:.85rem; padding-bottom:.85rem; display:flex; align-items:center; justify-content:space-between; gap:1rem;">

    {{-- Logo --}}
    <a href="{{ url('/') }}" style="display:flex; align-items:center; gap:.6rem;">
      <div style="width:2rem; height:2rem; border-radius:.6rem; background:linear-gradient(135deg,#4f46e5,#7c3aed); display:flex; align-items:center; justify-content:center;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 3.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
        </svg>
      </div>
      <span style="font-size:1.1rem; font-weight:800; color:var(--slate-900); letter-spacing:-.02em;">
        Qipu<span style="color:var(--indigo);">CRM</span>
      </span>
    </a>

    {{-- Nav links --}}
    <nav class="hide-mobile" style="display:flex; align-items:center; gap:1.5rem; font-size:.85rem; color:var(--slate-600);">
      <a href="#funcionalidades" style="color:var(--slate-600);">Funcionalidades</a>
      <a href="#integraciones"   style="color:var(--slate-600);">Integraciones</a>
      <a href="#faq"             style="color:var(--slate-600);">Preguntas</a>
      <a href="#demo" style="color:var(--indigo); font-weight:600;">📅 Agendar demo</a>
    </nav>

    {{-- Auth buttons --}}
    @if (Route::has('login'))
      <div style="display:flex; align-items:center; gap:.6rem;">
        @auth
          <a href="{{ url('/dashboard') }}" class="btn-nav">Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="btn-nav">Ingresar</a>
          @if (Route::has('register'))
            <a href="{{ route('register') }}" class="btn-nav-solid">Crear cuenta</a>
          @endif
        @endauth
      </div>
    @endif
  </div>
</header>

<!-- ===================== HERO ===================== -->
<section class="hero-bg" style="padding: 5rem 0 4rem;">
  <div class="container">
    <div style="display:flex; flex-direction:column; align-items:center; text-align:center; gap:1.5rem; max-width:700px; margin:0 auto;">

      <div class="section-label">
        <svg width="12" height="12" fill="#c7d2fe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
        CRM para equipos de ventas en Perú
      </div>

      <h1 style="font-size:clamp(2rem,5vw,3rem); font-weight:800; color:#fff; line-height:1.1; letter-spacing:-.02em;">
        Gestiona tus ventas,<br>
        <span style="color:#a5b4fc;">contactos y WhatsApp</span><br>
        desde un solo lugar
      </h1>

      <p style="font-size:1rem; color:#c7d2fe; line-height:1.75; max-width:560px;">
        QipuCRM es el sistema de gestión comercial hecho para equipos de ventas peruanos.
        Pipeline Kanban, contactos, conversaciones de WhatsApp con IA, productos y
        facturación electrónica SUNAT integrados en una sola plataforma.
      </p>

      <div style="display:flex; flex-wrap:wrap; gap:.75rem; justify-content:center;">
        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn-primary">
          Empezar gratis
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
          </svg>
        </a>
        <a href="#demo" class="btn-ghost">
          📅 Ver demo en vivo
        </a>
      </div>

      {{-- Mini stats --}}
      <div style="display:flex; flex-wrap:wrap; gap:1.5rem; justify-content:center; margin-top:.5rem;">
        @foreach([['Pipeline Kanban','visual e intuitivo'],['WhatsApp + IA','respuestas automáticas'],['Facturación SUNAT','facturas y boletas']] as [$t,$s])
          <div style="display:flex; align-items:center; gap:.5rem; font-size:.8rem; color:#c7d2fe;">
            <svg width="14" height="14" fill="#6ee7b7" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><strong style="color:#fff;">{{ $t }}</strong> — {{ $s }}</span>
          </div>
        @endforeach
      </div>

    </div>

    {{-- Mock UI card --}}
    <div style="margin-top:3rem; max-width:820px; margin-left:auto; margin-right:auto;">
      <div style="background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); border-radius:1.25rem; padding:1rem; backdrop-filter:blur(10px);">
        <div style="background:#fff; border-radius:.9rem; padding:1.25rem; box-shadow:0 20px 60px rgba(0,0,0,.25);">
          {{-- Fake kanban --}}
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
            <span style="font-size:.8rem; font-weight:700; color:var(--slate-900);">Pipeline de Ventas — Mayo 2025</span>
            <span style="font-size:.7rem; background:var(--indigo-light); color:var(--indigo); padding:.2rem .6rem; border-radius:9999px; font-weight:600;">12 negociaciones activas</span>
          </div>
          <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:.6rem;">
            @foreach([['Prospecto','3','#e0e7ff','#4338ca'],['Propuesta','4','#fef3c7','#d97706'],['Negociación','3','#dbeafe','#1d4ed8'],['Cerrado','2','#dcfce7','#15803d']] as [$col,$n,$bg,$tc])
              <div style="background:{{ $bg }}20; border:1px solid {{ $bg }}; border-radius:.6rem; padding:.6rem;">
                <p style="font-size:.65rem; font-weight:700; color:{{ $tc }}; margin-bottom:.4rem;">{{ $col }} ({{ $n }})</p>
                @for($i=0;$i<min((int)$n,2);$i++)
                  <div style="background:#fff; border-radius:.4rem; padding:.4rem .5rem; margin-bottom:.3rem; font-size:.6rem; color:var(--slate-600); border:1px solid var(--slate-200);">
                    Negociación #{{ rand(10,99) }} — S/ {{ rand(500,9999) }}
                  </div>
                @endfor
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== FUNCIONALIDADES ===================== -->
<section id="funcionalidades" style="background:var(--slate-50);">
  <div class="container">
    <div style="text-align:center; max-width:560px; margin:0 auto 3rem;">
      <span class="badge badge-indigo" style="margin-bottom:.75rem;">Funcionalidades</span>
      <h2 class="section-title">Todo lo que necesita tu equipo de ventas</h2>
      <p class="section-sub">Una plataforma completa, sin apps extra ni integraciones complicadas.</p>
    </div>

    <div class="grid-3">

      {{-- 1 --}}
      <div class="card">
        <div class="card-icon">
          <svg width="20" height="20" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
          </svg>
        </div>
        <h3 style="font-size:.95rem; font-weight:700; margin-bottom:.4rem;">Pipeline Kanban</h3>
        <p style="font-size:.83rem; color:var(--slate-600); line-height:1.65;">
          Visualiza tus negociaciones en etapas personalizadas. Mueve deals con drag & drop y sabe exactamente en qué fase está cada oportunidad.
        </p>
        <ul class="pill-list">
          <li>Etapas de venta configurables</li>
          <li>Asignación de responsables</li>
          <li>Múltiples pipelines por equipo</li>
        </ul>
      </div>

      {{-- 2 --}}
      <div class="card">
        <div class="card-icon">
          <svg width="20" height="20" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <h3 style="font-size:.95rem; font-weight:700; margin-bottom:.4rem;">Gestión de contactos</h3>
        <p style="font-size:.83rem; color:var(--slate-600); line-height:1.65;">
          Base de datos centralizada de clientes y prospectos con historial de interacciones, negociaciones asociadas y datos fiscales para facturación.
        </p>
        <ul class="pill-list">
          <li>Historial completo por contacto</li>
          <li>RUC / DNI para facturación</li>
          <li>Importación y exportación</li>
        </ul>
      </div>

      {{-- 3 --}}
      <div class="card">
        <div class="card-icon">
          <svg width="20" height="20" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
        </div>
        <h3 style="font-size:.95rem; font-weight:700; margin-bottom:.4rem;">WhatsApp integrado</h3>
        <p style="font-size:.83rem; color:var(--slate-600); line-height:1.65;">
          Bandeja de entrada de WhatsApp directamente en el CRM. Vincula conversaciones a negociaciones y responde desde un solo panel.
        </p>
        <ul class="pill-list">
          <li>Inbox unificado del equipo</li>
          <li>Conversaciones vinculadas a deals</li>
          <li>Respuestas rápidas plantilla</li>
        </ul>
      </div>

      {{-- 4 --}}
      <div class="card">
        <div class="card-icon">
          <svg width="20" height="20" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <h3 style="font-size:.95rem; font-weight:700; margin-bottom:.4rem;">Asistente IA en WhatsApp</h3>
        <p style="font-size:.83rem; color:var(--slate-600); line-height:1.65;">
          Activa un asistente con inteligencia artificial por conversación. Responde consultas frecuentes automáticamente y escala cuando es necesario.
        </p>
        <ul class="pill-list">
          <li>IA activable por conversación</li>
          <li>Personalización por cuenta WA</li>
          <li>Pausable en cualquier momento</li>
        </ul>
      </div>

      {{-- 5 --}}
      <div class="card">
        <div class="card-icon">
          <svg width="20" height="20" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
          </svg>
        </div>
        <h3 style="font-size:.95rem; font-weight:700; margin-bottom:.4rem;">Catálogo de productos</h3>
        <p style="font-size:.83rem; color:var(--slate-600); line-height:1.65;">
          Crea tu catálogo una vez y reutilízalo en cualquier negociación. Agrega líneas de producto con precios, cantidades y descuentos.
        </p>
        <ul class="pill-list">
          <li>Catálogo por equipo</li>
          <li>Precios en PEN o USD</li>
          <li>Totales automáticos en el deal</li>
        </ul>
      </div>

      {{-- 6 --}}
      <div class="card">
        <div class="card-icon">
          <svg width="20" height="20" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h3 style="font-size:.95rem; font-weight:700; margin-bottom:.4rem;">Facturación electrónica SUNAT</h3>
        <p style="font-size:.83rem; color:var(--slate-600); line-height:1.65;">
          Genera facturas y boletas de venta directamente desde la negociación. Envía a SUNAT, descarga el XML y obtén la respuesta CDR sin salir del CRM.
        </p>
        <ul class="pill-list">
          <li>Facturas y boletas electrónicas</li>
          <li>Envío directo a SUNAT</li>
          <li>Modo prueba para testear</li>
        </ul>
      </div>

    </div>
  </div>
</section>

<!-- ===================== CÓMO FUNCIONA ===================== -->
<section id="integraciones" style="background:#fff; border-top:1px solid var(--slate-200);">
  <div class="container">
    <div style="text-align:center; max-width:540px; margin:0 auto 3rem;">
      <span class="badge badge-indigo" style="margin-bottom:.75rem;">Flujo de trabajo</span>
      <h2 class="section-title">Del primer contacto a la factura,<br>sin salir del sistema</h2>
      <p class="section-sub">QipuCRM cubre todo el ciclo de ventas en un flujo continuo.</p>
    </div>

    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1.5rem; position:relative;">
      @php
        $steps = [
          ['01','Captura el lead','El prospecto llega por WhatsApp o lo creas manualmente. Se vincula al pipeline.','#dbeafe','#1d4ed8'],
          ['02','Gestiona el deal','Mueve la negociación en el Kanban, agrega productos, comentarios y actividades.','#ede9fe','#6d28d9'],
          ['03','Cierra la venta','La IA de WhatsApp ayuda a responder dudas. Haz seguimiento hasta el cierre.','#d1fae5','#065f46'],
          ['04','Emite el comprobante','Genera la factura o boleta desde la misma negociación y envíala a SUNAT.','#fef3c7','#92400e'],
        ];
      @endphp
      @foreach($steps as [$num,$title,$desc,$bg,$tc])
        <div style="text-align:center; padding:1.4rem 1rem;">
          <div style="width:2.8rem; height:2.8rem; border-radius:9999px; background:{{ $bg }}; border:2px solid {{ $tc }}20;
                      display:flex; align-items:center; justify-content:center; margin:0 auto .9rem; font-size:.85rem; font-weight:800; color:{{ $tc }};">
            {{ $num }}
          </div>
          <h3 style="font-size:.9rem; font-weight:700; margin-bottom:.4rem;">{{ $title }}</h3>
          <p style="font-size:.8rem; color:var(--slate-600); line-height:1.6;">{{ $desc }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ===================== VENTAJAS ===================== -->
<section style="background:linear-gradient(135deg,#1e1b4b,#312e81); color:#fff;">
  <div class="container">
    <div class="grid-2" style="align-items:center; gap:4rem;">
      <div>
        <span class="badge" style="background:rgba(255,255,255,.12);color:#c7d2fe; margin-bottom:1rem; display:inline-block; padding:.3rem .9rem; border-radius:9999px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em;">
          ¿Por qué QipuCRM?
        </span>
        <h2 style="font-size:clamp(1.5rem,3vw,2rem); font-weight:800; line-height:1.2; margin-bottom:1rem;">
          Diseñado para la realidad<br>del negocio peruano
        </h2>
        <p style="font-size:.9rem; color:#c7d2fe; line-height:1.75; margin-bottom:1.5rem;">
          No es una herramienta genérica traducida. QipuCRM incluye facturación electrónica SUNAT,
          integración con WhatsApp Business y todo en soles y dólares.
        </p>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:.8rem;">
          @foreach(['Multi-equipo con roles y permisos','Pipeline visual con etapas personalizadas','WhatsApp Business API integrada','Facturación SUNAT (facturas y boletas)','Asistente IA por conversación','Catálogo de productos reutilizable'] as $item)
            <li style="display:flex; align-items:center; gap:.7rem; font-size:.85rem; color:#e0e7ff;">
              <svg width="16" height="16" fill="#6ee7b7" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ $item }}
            </li>
          @endforeach
        </ul>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        @foreach([['Equipos','Multi-usuario con roles'],['Kanban','Pipelines ilimitados'],['WhatsApp','IA integrada'],['SUNAT','Facturación real']] as [$t,$s])
          <div style="background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:1rem; padding:1.2rem;">
            <p style="font-size:1.4rem; font-weight:800; color:#a5b4fc; margin-bottom:.25rem;">{{ $t }}</p>
            <p style="font-size:.78rem; color:#c7d2fe;">{{ $s }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

<!-- ===================== FAQ ===================== -->
<section id="faq" style="background:var(--slate-50); border-top:1px solid var(--slate-200);">
  <div class="container">
    <div style="text-align:center; max-width:500px; margin:0 auto 3rem;">
      <span class="badge badge-indigo" style="margin-bottom:.75rem;">Preguntas frecuentes</span>
      <h2 class="section-title">Respuestas rápidas</h2>
    </div>

    <div style="max-width:700px; margin:0 auto; display:flex; flex-direction:column; gap:1rem;">
      @foreach([
        ['¿Necesito instalar algo?','No. QipuCRM es 100% web. Funciona desde cualquier navegador en PC o celular, sin instalaciones.'],
        ['¿Puedo tener varios equipos o empresas?','Sí. Puedes crear múltiples equipos dentro de tu cuenta, cada uno con sus propios pipelines, contactos y configuración.'],
        ['¿La facturación electrónica requiere algo especial?','Solo necesitas tu RUC, usuario SOL y el certificado digital de SUNAT. También puedes activar el modo prueba para testear sin enviar documentos reales.'],
        ['¿El WhatsApp necesita el número oficial de mi empresa?','Sí, se conecta mediante la API oficial de WhatsApp Business (Meta). Solo funciona con números aprobados por Meta.'],
        ['¿La IA de WhatsApp responde sola o necesito supervisión?','Puedes activarla o pausarla por conversación. Cuando está activa responde automáticamente; cuando la pausas, toma el control el asesor humano.'],
      ] as [$q,$a])
        <div class="card" style="padding:1.1rem 1.3rem;">
          <h3 style="font-size:.88rem; font-weight:700; color:var(--slate-900); margin-bottom:.4rem;">{{ $q }}</h3>
          <p style="font-size:.83rem; color:var(--slate-600); line-height:1.65;">{{ $a }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ===================== DEMO EN VIVO ===================== -->
<section id="demo" style="background:var(--slate-50); border-top:1px solid var(--slate-200);">
  <div class="container">
    <div style="text-align:center; max-width:560px; margin:0 auto 2.5rem;">
      <span class="badge badge-indigo" style="margin-bottom:.75rem;">Demo gratuita</span>
      <h2 class="section-title">Agenda una demostración en vivo</h2>
      <p class="section-sub">
        Muéstrate el sistema con tus propios datos. Un especialista te guía en 30 minutos
        y responde todas tus preguntas sobre QipuCRM.
      </p>
    </div>

    <div style="max-width:820px; margin:0 auto; background:#fff; border:1px solid var(--slate-200); border-radius:1.25rem; padding:1.5rem; box-shadow:0 4px 20px rgba(15,23,42,.07);">
      <div id="calendar-bitrix-widget"></div>
    </div>
  </div>
</section>
<script>
(function(){
  var el = document.getElementById("calendar-bitrix-widget");
  var ifr = document.createElement("iframe");
  ifr.src = "https://connection.systemsleads.com/calendar/28a711a564e76dcd5ff19d5e678861776f7e6865e7a3756fa0aea2508953e428";
  ifr.style.cssText = "width:100%;height:700px;border:none;border-radius:8px;";
  ifr.setAttribute("allowtransparency","true");
  el.appendChild(ifr);
  window.addEventListener("message", function(e){
    if(e.data && e.data.type === "cal-resize") ifr.style.height = e.data.height + "px";
  });
})();
</script>

<!-- ===================== CTA FINAL ===================== -->
<section style="background:#fff; border-top:1px solid var(--slate-200);">
  <div class="container">
    <div style="background:linear-gradient(135deg,#1e1b4b,#4f46e5); border-radius:1.5rem; padding:3rem 2.5rem;
                display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:2rem;
                box-shadow:0 20px 50px rgba(79,70,229,.3);">
      <div style="max-width:500px;">
        <h2 style="font-size:clamp(1.4rem,3vw,1.9rem); font-weight:800; color:#fff; margin-bottom:.65rem; line-height:1.2;">
          Empieza a organizar tus ventas hoy mismo
        </h2>
        <p style="font-size:.9rem; color:#c7d2fe; line-height:1.7;">
          QipuCRM está listo para tu equipo. Pipeline, contactos, WhatsApp con IA y
          facturación electrónica en un solo sistema.
        </p>
      </div>
      <div style="display:flex; flex-direction:column; gap:.7rem; min-width:220px;">
        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn-primary" style="width:100%; justify-content:center;">
          Crear mi cuenta
        </a>
        <a href="{{ Route::has('login') ? route('login') : '#' }}" class="btn-ghost" style="width:100%; justify-content:center;">
          Ya tengo una cuenta
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ===================== FOOTER ===================== -->
<footer style="background:var(--slate-900); border-top:1px solid #1e293b; padding:1.5rem 0;">
  <div class="container" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem;">
    <div style="display:flex; align-items:center; gap:.5rem;">
      <div style="width:1.4rem; height:1.4rem; border-radius:.35rem; background:var(--indigo); display:flex; align-items:center; justify-content:center;">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 3.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
        </svg>
      </div>
      <span style="font-size:.82rem; font-weight:700; color:#e2e8f0; letter-spacing:-.01em;">
        Qipu<span style="color:#818cf8;">CRM</span>
      </span>
      <span style="font-size:.75rem; color:#64748b; margin-left:.4rem;">© {{ date('Y') }}</span>
    </div>
    <p style="font-size:.75rem; color:#64748b;">Desarrollado por Systems Leads</p>
  </div>
</footer>

</body>
</html>
