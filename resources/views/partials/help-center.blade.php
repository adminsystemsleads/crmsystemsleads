{{-- =====================================================================
     Centro de Ayuda Qipu (estilo Helpdesk).
     Botón fijo arriba a la derecha, a la izquierda de la campana de
     notificaciones. Abre un panel lateral deslizante con categorías,
     buscador y guías paso a paso con imágenes de apoyo.

     Las imágenes se cargan desde public/img/help/<archivo>.png. Si una
     imagen no existe todavía, se muestra un recuadro punteado con el
     texto de apoyo (no rompe la vista). Ver public/img/help/README.txt.
     ===================================================================== --}}
@php
  // Helper para renderizar una figura con imagen de apoyo. Si el archivo no
  // existe en public/img/help/, el JS le añade la clase .hc-fig-missing y el
  // CSS muestra un recuadro punteado con el texto (data-label). Ver README.
  $__hcFig = function ($file, $label) {
      $src = asset('img/help/' . $file);
      $lbl = e($label);
      return '<figure class="hc-fig" data-label="'.$lbl.'">'
           . '<img src="'.$src.'" alt="'.$lbl.'" loading="lazy">'
           . '<figcaption>'.$lbl.'</figcaption></figure>';
  };

  // Registro de categorías (para el listado de inicio y el buscador).
  $hcCats = [
    'inicio'  => ['name' => __('Primeros pasos'),   'emoji' => '🚀'],
    'contactos' => ['name' => __('Contactos'),      'emoji' => '👥'],
    'crm'     => ['name' => __('CRM y Embudos'),     'emoji' => '📊'],
    'whatsapp'=> ['name' => __('WhatsApp'),          'emoji' => '💬'],
    'admin'   => ['name' => __('Administración'),     'emoji' => '⚙️'],
    'cuenta'  => ['name' => __('Tu cuenta'),          'emoji' => '🔔'],
  ];

  // Registro plano de artículos: id, categoría, título y palabras clave
  // (para el buscador). El CONTENIDO de cada artículo se escribe abajo,
  // en los bloques <section data-art="...">.
  $hcArticles = [
    ['id' => 'bienvenida',   'cat' => 'inicio',   'title' => __('Bienvenido a Qipu'),                   'kw' => 'inicio empezar panel principal dashboard resumen'],
    ['id' => 'preferencias', 'cat' => 'inicio',   'title' => __('Idioma, tema y tu perfil'),            'kw' => 'idioma tema oscuro claro perfil foto contraseña preferencias seguridad autenticacion dos pasos telefono correo'],
    ['id' => 'crear-contacto','cat'=> 'contactos', 'title' => __('Crear y editar un contacto'),          'kw' => 'contacto cliente crear nuevo editar telefono correo'],
    ['id' => 'importar-contactos','cat'=>'contactos','title'=> __('Importar contactos desde Excel'),     'kw' => 'importar excel csv masivo cargar contactos lista'],
    ['id' => 'kanban',       'cat' => 'crm',      'title' => __('Embudos y etapas (Kanban)'),           'kw' => 'embudo pipeline etapa kanban tablero arrastrar mover'],
    ['id' => 'negociacion',  'cat' => 'crm',      'title' => __('Crear y gestionar una negociación'),   'kw' => 'negociacion deal oportunidad venta crear monto etapa'],
    ['id' => 'actividades',  'cat' => 'crm',      'title' => __('Actividades y recordatorios'),         'kw' => 'actividad tarea recordatorio seguimiento llamada reunion vencer'],
    ['id' => 'productos',    'cat' => 'crm',      'title' => __('Catálogo de productos'),               'kw' => 'producto catalogo precio servicio agregar'],
    ['id' => 'wa-bandeja',   'cat' => 'whatsapp', 'title' => __('Usar la bandeja de entrada'),          'kw' => 'whatsapp bandeja chat conversacion mensajes responder inbox'],
    ['id' => 'wa-responder', 'cat' => 'whatsapp', 'title' => __('Responder, adjuntar y respuestas rápidas'),'kw' => 'responder adjuntar imagen audio archivo respuesta rapida plantilla texto'],
    ['id' => 'wa-ventana',   'cat' => 'whatsapp', 'title' => __('Ventana de 24 horas y plantillas'),    'kw' => 'ventana 24 horas plantilla vencida meta whatsapp aprobada enviar'],
    ['id' => 'wa-ia',        'cat' => 'whatsapp', 'title' => __('Asistente de IA en WhatsApp'),          'kw' => 'ia inteligencia artificial asistente bot automatico pausar activar'],
    ['id' => 'admin-usuarios','cat'=> 'admin',    'title' => __('Invitar usuarios y permisos'),          'kw' => 'usuario invitar agregar permiso rol acceso crm equipo'],
    ['id' => 'admin-wa',     'cat' => 'admin',    'title' => __('Conectar una cuenta de WhatsApp'),     'kw' => 'conectar cuenta whatsapp numero meta api token telefono'],
    ['id' => 'admin-campos', 'cat' => 'admin',    'title' => __('Campos personalizados'),               'kw' => 'campo personalizado custom field adicional formulario'],
    ['id' => 'notificaciones','cat'=> 'cuenta',   'title' => __('Configurar notificaciones'),           'kw' => 'notificacion alerta sonido campana avisos embudo'],
    ['id' => 'licencia',     'cat' => 'cuenta',   'title' => __('Licencia y cambio de cuenta'),         'kw' => 'licencia vencimiento cuenta cambiar equipo plan'],
  ];
@endphp

<div class="hc" x-data="helpCenter()" x-init="init()">

  {{-- Botón flotante --}}
  <button type="button" class="hc-fab" @click="toggle()" :aria-expanded="open" title="{{ __('Centro de ayuda') }}">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round"
            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
  </button>

  {{-- Fondo oscuro --}}
  <div x-show="open" x-cloak @click="close()"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
       class="hc-backdrop"></div>

  {{-- Panel lateral --}}
  <aside class="hc-panel" :class="open ? 'hc-open' : ''" x-cloak>

    {{-- Cabecera --}}
    <div class="hc-head">
      <button type="button" class="hc-back" x-show="view === 'article'" @click="goHome()" title="{{ __('Volver') }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <div class="hc-head-title">
        <span class="hc-brand">Qipu</span>
        <span class="hc-head-sub">{{ __('Centro de ayuda') }}</span>
      </div>
      <button type="button" class="hc-close" @click="close()" title="{{ __('Cerrar') }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    {{-- Buscador (solo en inicio) --}}
    <div class="hc-search" x-show="view === 'home'">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
      <input type="text" x-model="q" placeholder="{{ __('Buscar en la ayuda…') }}">
      <button type="button" x-show="q" @click="q=''" class="hc-search-clear">&times;</button>
    </div>

    {{-- ===================== VISTA INICIO ===================== --}}
    <div class="hc-body" x-show="view === 'home'">

      {{-- Listado por categorías (sin búsqueda) --}}
      <div x-show="!q.trim()">
        <p class="hc-intro">{{ __('Guías rápidas para sacarle el máximo a Qipu. Elige un tema o usa el buscador.') }}</p>
        @foreach ($hcCats as $catId => $cat)
          <div class="hc-cat">
            <div class="hc-cat-head"><span class="hc-cat-emoji">{{ $cat['emoji'] }}</span>{{ $cat['name'] }}</div>
            @foreach ($hcArticles as $a)
              @if ($a['cat'] === $catId)
                <button type="button" class="hc-row" @click="openArticle('{{ $a['id'] }}')">
                  <span>{{ $a['title'] }}</span>
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
              @endif
            @endforeach
          </div>
        @endforeach
      </div>

      {{-- Resultados de búsqueda --}}
      <div x-show="q.trim()">
        <p class="hc-results-label" x-text="resultsLabel()"></p>
        @foreach ($hcArticles as $a)
          <button type="button" class="hc-row" data-kw="{{ $a['cat'].' '.strtolower($a['title']).' '.$a['kw'] }}"
                  x-show="match('{{ $a['cat'].' '.strtolower($a['title']).' '.$a['kw'] }}')"
                  @click="openArticle('{{ $a['id'] }}')">
            <span>{{ $a['title'] }}</span>
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>
        @endforeach
        <div class="hc-empty" x-show="visibleCount() === 0">
          {{ __('No encontramos resultados. Intenta con otra palabra.') }}
        </div>
      </div>
    </div>

    {{-- ===================== VISTA ARTÍCULO ===================== --}}
    <div class="hc-body hc-article" x-show="view === 'article'">

      {{-- BIENVENIDA --}}
      <section data-art="bienvenida" x-show="current === 'bienvenida'">
        <h2>{{ __('Bienvenido a Qipu') }}</h2>
        <p>{{ __('Qipu es tu CRM para gestionar clientes, ventas y conversaciones de WhatsApp en un solo lugar. Así está organizado:') }}</p>
        <ul class="hc-bullets">
          <li><b>{{ __('Panel Principal') }}</b> — {{ __('resumen de tu actividad, negociaciones y pendientes del día.') }}</li>
          <li><b>{{ __('Contactos') }}</b> — {{ __('tu base de clientes y prospectos.') }}</li>
          <li><b>{{ __('CRM') }}</b> — {{ __('embudos, negociaciones y productos para seguir tus ventas.') }}</li>
          <li><b>{{ __('WhatsApp') }}</b> — {{ __('bandeja de entrada para chatear con tus clientes.') }}</li>
        </ul>
        {!! $__hcFig('inicio-panel.png', __('Vista del Panel Principal')) !!}
        <div class="hc-tip">💡 {{ __('El menú lateral izquierdo te lleva a cada sección. En celular, ábrelo con el botón ☰.') }}</div>
        <a href="{{ route('dashboard') }}" class="hc-cta">{{ __('Ir al Panel Principal') }}</a>
      </section>

      {{-- PREFERENCIAS --}}
      <section data-art="preferencias" x-show="current === 'preferencias'">
        <h2>{{ __('Idioma, tema y tu perfil') }}</h2>
        <p>{{ __('Personaliza Qipu a tu gusto desde el menú lateral.') }}</p>

        <p><b>{{ __('Tema e idioma') }}</b></p>
        <ol class="hc-steps">
          <li>{{ __('Abre el menú lateral izquierdo y baja hasta el final.') }}</li>
          <li>{{ __('En «Tema» eliges entre claro y oscuro.') }}</li>
          <li>{{ __('En «Idioma» abres el selector y eliges Español, English o Português.') }}</li>
        </ol>
        {!! $__hcFig('perfil-tema-idioma.png', __('Secciones de Tema e Idioma en el menú')) !!}
        {!! $__hcFig('perfil-idioma-menu.png', __('Selector de idioma: Español, English, Português')) !!}

        <p><b>{{ __('Tu perfil') }}</b></p>
        <ol class="hc-steps">
          <li>{{ __('Haz clic en tu nombre (abajo del todo, junto a «Cerrar sesión»).') }}</li>
          <li>{{ __('En «Información del perfil» cambias tu foto, nombre, correo y teléfono. Guarda con «GUARDAR».') }}</li>
          <li>{{ __('En «Actualizar contraseña» cambias tu clave: escribe la actual y la nueva dos veces.') }}</li>
          <li>{{ __('En «Autenticación en dos pasos» puedes activar un código extra al iniciar sesión, para más seguridad.') }}</li>
        </ol>
        {!! $__hcFig('perfil-datos.png', __('Pantalla de perfil: datos, contraseña y seguridad')) !!}
        <div class="hc-tip">💡 {{ __('La foto debe ser JPG o PNG de máximo 1 MB.') }}</div>
        <a href="{{ route('profile.show') }}" class="hc-cta">{{ __('Ir a mi perfil') }}</a>
      </section>

      {{-- CREAR CONTACTO --}}
      <section data-art="crear-contacto" x-show="current === 'crear-contacto'">
        <h2>{{ __('Crear y editar un contacto') }}</h2>
        <p>{{ __('Los contactos son tu base de clientes. Puedes crearlos uno a uno:') }}</p>
        <ol class="hc-steps">
          <li>{{ __('En el menú lateral entra a «Contactos».') }}</li>
          <li>{{ __('Haz clic en el botón «Nuevo contacto» (arriba a la derecha).') }}</li>
          <li>{{ __('Completa nombre, teléfono y correo. El teléfono con código de país te sirve luego para WhatsApp.') }}</li>
          <li>{{ __('Guarda. Para editar, abre el contacto y usa «Editar».') }}</li>
        </ol>
        {!! $__hcFig('contactos-crear.png', __('Formulario para crear un contacto')) !!}
        <div class="hc-tip">💡 {{ __('Escribe el teléfono en formato internacional (ej. 51 999 888 777) para poder abrir el chat de WhatsApp desde el contacto.') }}</div>
        <a href="{{ route('contacts.index') }}" class="hc-cta">{{ __('Ir a Contactos') }}</a>
      </section>

      {{-- IMPORTAR CONTACTOS --}}
      <section data-art="importar-contactos" x-show="current === 'importar-contactos'">
        <h2>{{ __('Importar contactos desde Excel') }}</h2>
        <p>{{ __('¿Ya tienes una lista? Cárgala de una sola vez.') }}</p>
        <ol class="hc-steps">
          <li>{{ __('Entra a «Contactos» y busca la opción «Importar».') }}</li>
          <li>{{ __('Descarga la plantilla de ejemplo para ver qué columnas se esperan (nombre, teléfono, correo…).') }}</li>
          <li>{{ __('Completa tu archivo Excel/CSV respetando esas columnas.') }}</li>
          <li>{{ __('Sube el archivo y confirma. Revisa el resumen de filas importadas.') }}</li>
        </ol>
        {!! $__hcFig('contactos-importar.png', __('Pantalla de importación de contactos')) !!}
        <div class="hc-warn">⚠️ {{ __('Evita duplicados: si un teléfono ya existe, revisa antes de volver a importarlo.') }}</div>
      </section>

      {{-- KANBAN --}}
      <section data-art="kanban" x-show="current === 'kanban'">
        <h2>{{ __('Embudos y etapas (Kanban)') }}</h2>
        <p>{{ __('Un embudo representa tu proceso de venta. Cada columna es una etapa (por ejemplo: Nuevo, Contactado, Propuesta, Ganado).') }}</p>
        <ol class="hc-steps">
          <li>{{ __('En el menú lateral entra a «CRM». Debajo verás tus embudos.') }}</li>
          <li>{{ __('Haz clic en un embudo para abrir su tablero Kanban.') }}</li>
          <li>{{ __('Cada tarjeta es una negociación. Arrástrala de una columna a otra para cambiar su etapa.') }}</li>
          <li>{{ __('El total de cada columna te muestra cuánto dinero tienes en esa etapa.') }}</li>
        </ol>
        {!! $__hcFig('crm-kanban.png', __('Tablero Kanban con etapas')) !!}
        <div class="hc-tip">💡 {{ __('Mover una tarjeta a la etapa final (Ganado/Perdido) cierra la negociación.') }}</div>
        <a href="{{ route('pipelines.index') }}" class="hc-cta">{{ __('Ir a CRM') }}</a>
      </section>

      {{-- NEGOCIACIÓN --}}
      <section data-art="negociacion" x-show="current === 'negociacion'">
        <h2>{{ __('Crear y gestionar una negociación') }}</h2>
        <p>{{ __('Una negociación (u oportunidad) es una venta en curso con un cliente.') }}</p>
        <ol class="hc-steps">
          <li>{{ __('Abre el embudo donde quieres registrarla.') }}</li>
          <li>{{ __('Haz clic en «Nueva negociación» o en el «+» de una etapa.') }}</li>
          <li>{{ __('Ponle un título, asígnale un contacto y un monto estimado.') }}</li>
          <li>{{ __('Guarda. Abre la tarjeta para añadir notas, actividades o cambiar la etapa.') }}</li>
        </ol>
        {!! $__hcFig('crm-negociacion.png', __('Ficha de una negociación')) !!}
        <div class="hc-tip">💡 {{ __('Mantén el monto y la etapa al día: así el tablero refleja tu pipeline real.') }}</div>
      </section>

      {{-- ACTIVIDADES --}}
      <section data-art="actividades" x-show="current === 'actividades'">
        <h2>{{ __('Actividades y recordatorios') }}</h2>
        <p>{{ __('Las actividades te ayudan a no olvidar un seguimiento: una llamada, una reunión o una tarea con fecha.') }}</p>
        <ol class="hc-steps">
          <li>{{ __('Abre una negociación (o un contacto).') }}</li>
          <li>{{ __('En la sección de actividades, crea una nueva: elige tipo, descripción y fecha de vencimiento.') }}</li>
          <li>{{ __('Cuando esté por vencer, recibirás una notificación (revisa la campana 🔔).') }}</li>
          <li>{{ __('Al completarla, márcala como hecha.') }}</li>
        </ol>
        {!! $__hcFig('crm-actividad.png', __('Registrar una actividad con recordatorio')) !!}
        <div class="hc-tip">💡 {{ __('Puedes activar o silenciar los avisos de actividades desde la configuración de la campana de notificaciones.') }}</div>
      </section>

      {{-- PRODUCTOS --}}
      <section data-art="productos" x-show="current === 'productos'">
        <h2>{{ __('Catálogo de productos') }}</h2>
        <p>{{ __('Registra los productos o servicios que vendes para reutilizarlos en tus negociaciones.') }}</p>
        <ol class="hc-steps">
          <li>{{ __('En el menú lateral, dentro de «CRM», entra a «Productos».') }}</li>
          <li>{{ __('Haz clic en «Nuevo producto».') }}</li>
          <li>{{ __('Escribe nombre, precio y una descripción opcional.') }}</li>
          <li>{{ __('Guarda. Ya podrás asociarlo a tus negociaciones.') }}</li>
        </ol>
        {!! $__hcFig('crm-productos.png', __('Catálogo de productos')) !!}
        <a href="{{ route('products.index') }}" class="hc-cta">{{ __('Ir a Productos') }}</a>
      </section>

      {{-- WA BANDEJA --}}
      <section data-art="wa-bandeja" x-show="current === 'wa-bandeja'">
        <h2>{{ __('Usar la bandeja de entrada de WhatsApp') }}</h2>
        <p>{{ __('Desde aquí chateas con tus clientes sin salir de Qipu.') }}</p>
        <ol class="hc-steps">
          <li>{{ __('En el menú lateral entra a «WhatsApp».') }}</li>
          <li>{{ __('A la izquierda verás la lista de conversaciones. Usa las pestañas Todo / Abierto / Cerrado y el buscador para filtrarlas.') }}</li>
          <li>{{ __('Haz clic en una conversación para abrir el chat en el centro.') }}</li>
          <li>{{ __('A la derecha ves los datos del contacto y la negociación asociada.') }}</li>
        </ol>
        {!! $__hcFig('wa-bandeja.png', __('Bandeja de entrada de WhatsApp')) !!}
        <a href="{{ route('whatsapp.inbox.index') }}" class="hc-cta">{{ __('Ir a WhatsApp') }}</a>
      </section>

      {{-- WA RESPONDER --}}
      <section data-art="wa-responder" x-show="current === 'wa-responder'">
        <h2>{{ __('Responder, adjuntar y respuestas rápidas') }}</h2>
        <p>{{ __('Con la conversación abierta:') }}</p>
        <ol class="hc-steps">
          <li>{{ __('Escribe tu mensaje en la caja inferior y presiona Enter para enviarlo.') }}</li>
          <li>{{ __('Con el clip 📎 adjuntas imágenes, audios, videos o documentos (JPG, PNG, GIF, WEBP y MP4 hasta 16 MB).') }}</li>
          <li>{{ __('El ícono de respuestas rápidas te deja insertar mensajes que usas con frecuencia, sin reescribirlos.') }}</li>
        </ol>
        {!! $__hcFig('wa-responder.png', __('Caja de respuesta, adjuntos y respuestas rápidas')) !!}
        <div class="hc-tip">💡 {{ __('Los mensajes nuevos del cliente aparecen solos en pantalla; no necesitas recargar.') }}</div>
      </section>

      {{-- WA VENTANA 24H --}}
      <section data-art="wa-ventana" x-show="current === 'wa-ventana'">
        <h2>{{ __('La ventana de 24 horas y las plantillas') }}</h2>
        <p>{{ __('WhatsApp (Meta) solo permite enviar texto libre dentro de las 24 horas posteriores al último mensaje del cliente. A eso le llamamos «ventana de 24 horas».') }}</p>
        <ul class="hc-bullets">
          <li>{{ __('Si el cliente te escribió hace menos de 24 h, puedes responder con cualquier mensaje.') }}</li>
          <li>{{ __('Si pasaron más de 24 h sin que el cliente escriba, verás el aviso «Ventana de 24h vencida» y solo podrás enviar una plantilla aprobada.') }}</li>
        </ul>
        {!! $__hcFig('wa-plantilla.png', __('Aviso de ventana vencida y botón Enviar plantilla')) !!}
        <p><b>{{ __('Para enviar una plantilla:') }}</b></p>
        <ol class="hc-steps">
          <li>{{ __('Haz clic en «Enviar plantilla».') }}</li>
          <li>{{ __('Elige una plantilla aprobada y completa sus variables si las tiene.') }}</li>
          <li>{{ __('Envíala. Cuando el cliente responda, la ventana se reabre y podrás chatear con normalidad.') }}</li>
        </ol>
        <div class="hc-tip">💡 {{ __('En cuanto el cliente vuelve a escribir, el aviso desaparece solo y puedes responder texto libre otra vez.') }}</div>
      </section>

      {{-- WA IA --}}
      <section data-art="wa-ia" x-show="current === 'wa-ia'">
        <h2>{{ __('Asistente de IA en WhatsApp') }}</h2>
        <p>{{ __('Si tu cuenta tiene el asistente de IA activo, puede responder automáticamente a tus clientes según su configuración.') }}</p>
        <ul class="hc-bullets">
          <li>{{ __('En la cabecera del chat verás una insignia «IA» (verde) cuando está activa, o «IA pausada» (roja) cuando no.') }}</li>
          <li>{{ __('Puedes pausar la IA en una conversación para atender tú mismo, y reactivarla cuando quieras.') }}</li>
        </ul>
        {!! $__hcFig('wa-ia.png', __('Insignia de IA en la conversación')) !!}
        <div class="hc-tip">💡 {{ __('Cuando respondes manualmente, conviene pausar la IA para que no envíe mensajes al mismo tiempo.') }}</div>
      </section>

      {{-- ADMIN USUARIOS --}}
      <section data-art="admin-usuarios" x-show="current === 'admin-usuarios'">
        <h2>{{ __('Invitar usuarios y permisos') }} <span class="hc-badge-admin">{{ __('Administradores') }}</span></h2>
        <p>{{ __('Si eres administrador de la cuenta puedes sumar a tu equipo y controlar qué ve cada persona.') }}</p>
        <ol class="hc-steps">
          <li>{{ __('En el menú lateral, abre «Configuración» (bajo el nombre de tu cuenta).') }}</li>
          <li>{{ __('Usa «Agregar usuario nuevo» para invitar a un miembro con su correo.') }}</li>
          <li>{{ __('En «Permisos de Acceso CRM» defines su rol: qué embudos y acciones puede usar.') }}</li>
        </ol>
        {!! $__hcFig('admin-usuarios.png', __('Agregar usuario y permisos de CRM')) !!}
        <a href="{{ route('team.crm-roles.index') }}" class="hc-cta">{{ __('Ir a Permisos de CRM') }}</a>
      </section>

      {{-- ADMIN WA --}}
      <section data-art="admin-wa" x-show="current === 'admin-wa'">
        <h2>{{ __('Conectar una cuenta de WhatsApp') }} <span class="hc-badge-admin">{{ __('Administradores') }}</span></h2>
        <p>{{ __('Para chatear necesitas una cuenta de WhatsApp conectada a Qipu.') }}</p>
        <ol class="hc-steps">
          <li>{{ __('En el menú lateral (sección Administración) entra a «WhatsApp Cuentas».') }}</li>
          <li>{{ __('Agrega una cuenta e ingresa los datos que te pide (número y credenciales de la API de WhatsApp de Meta).') }}</li>
          <li>{{ __('Guarda. Una vez conectada, sus conversaciones aparecerán en la bandeja de entrada.') }}</li>
        </ol>
        {!! $__hcFig('admin-wa-cuenta.png', __('Conectar una cuenta de WhatsApp')) !!}
        <div class="hc-warn">⚠️ {{ __('Necesitas las credenciales de WhatsApp Business API de Meta. Si no las tienes a la mano, contacta a Soporte.') }}</div>
        <a href="{{ route('whatsapp.accounts.index') }}" class="hc-cta">{{ __('Ir a WhatsApp Cuentas') }}</a>
      </section>

      {{-- ADMIN CAMPOS --}}
      <section data-art="admin-campos" x-show="current === 'admin-campos'">
        <h2>{{ __('Campos personalizados') }} <span class="hc-badge-admin">{{ __('Administradores') }}</span></h2>
        <p>{{ __('Agrega campos propios a tus contactos o negociaciones para guardar la información que tu negocio necesita.') }}</p>
        <ol class="hc-steps">
          <li>{{ __('En el menú lateral, abre «Configuración» y entra a «Campos personalizados».') }}</li>
          <li>{{ __('Crea un campo: dale un nombre y elige el tipo (texto, número, lista, fecha…).') }}</li>
          <li>{{ __('Guarda. El campo aparecerá en los formularios correspondientes.') }}</li>
        </ol>
        {!! $__hcFig('admin-campos.png', __('Crear un campo personalizado')) !!}
        <a href="{{ route('custom-fields.index') }}" class="hc-cta">{{ __('Ir a Campos personalizados') }}</a>
      </section>

      {{-- NOTIFICACIONES --}}
      <section data-art="notificaciones" x-show="current === 'notificaciones'">
        <h2>{{ __('Configurar notificaciones') }}</h2>
        <p>{{ __('La campana 🔔 (arriba a la derecha) te avisa de negociaciones asignadas y actividades por vencer.') }}</p>
        <ol class="hc-steps">
          <li>{{ __('Haz clic en la campana para ver tus notificaciones.') }}</li>
          <li>{{ __('Usa el ícono de engranaje ⚙️ para abrir la configuración.') }}</li>
          <li>{{ __('Activa o desactiva el sonido, y elige qué avisos quieres recibir y de qué embudos.') }}</li>
          <li>{{ __('Guarda tus preferencias.') }}</li>
        </ol>
        {!! $__hcFig('cuenta-notificaciones.png', __('Configuración de notificaciones')) !!}
      </section>

      {{-- LICENCIA --}}
      <section data-art="licencia" x-show="current === 'licencia'">
        <h2>{{ __('Licencia y cambio de cuenta') }}</h2>
        <p>{{ __('Tu acceso a Qipu depende de la licencia de tu cuenta.') }}</p>
        <ul class="hc-bullets">
          <li>{{ __('Si perteneces a varias cuentas, cámbiate entre ellas desde el selector de cuenta en el menú lateral.') }}</li>
          <li>{{ __('Los administradores ven el estado de la licencia en la sección «Licencia» del menú.') }}</li>
          <li>{{ __('Cuando una licencia está por vencer, aparece un aviso en la parte superior.') }}</li>
        </ul>
        {!! $__hcFig('cuenta-licencia.png', __('Pantalla de licencia y cambio de cuenta')) !!}
        <div class="hc-warn">⚠️ {{ __('Si tu cuenta se bloquea por licencia vencida, contacta a Soporte para renovarla.') }}</div>
      </section>

    </div>

    {{-- Pie: contacto con soporte --}}
    <div class="hc-foot">
      <span>{{ __('¿No encontraste lo que buscabas?') }}</span>
      <a href="{{ route('soporte') }}">{{ __('Contactar a Soporte') }}</a>
    </div>
  </aside>
</div>

<style>
  /* ===== Botón flotante ===== */
  .hc-fab { position: fixed; top: 1rem; right: 3.65rem; z-index: 41;
    display: inline-flex; align-items: center; justify-content: center;
    padding: .5rem; border-radius: .6rem; color: #fff; background: transparent;
    border: none; cursor: pointer; transition: background .15s; }
  .hc-fab:hover { background: rgba(255,255,255,.14); }
  .hc-fab svg { width: 1.4rem; height: 1.4rem; }

  /* En pantallas donde el header es claro (no navy) el ícono blanco no se ve;
     el header de la app y la barra de WhatsApp son navy, así que va bien. */

  /* ===== Fondo oscuro ===== */
  .hc-backdrop { position: fixed; inset: 0; z-index: 90; background: rgba(15,23,42,.45); }

  /* ===== Panel lateral ===== */
  .hc-panel { position: fixed; top: 0; right: 0; z-index: 95; height: 100vh;
    width: 30rem; max-width: 94vw; background: #fff; color: #374151;
    display: flex; flex-direction: column;
    box-shadow: -12px 0 34px rgba(15,23,42,.22);
    transform: translateX(100%); transition: transform .25s ease; }
  .hc-panel.hc-open { transform: translateX(0); }

  .hc-head { display: flex; align-items: center; gap: .6rem; padding: .9rem 1rem;
    background: var(--brand-navy); color: #fff; }
  .hc-back, .hc-close { display: inline-flex; align-items: center; justify-content: center;
    width: 2rem; height: 2rem; border: none; background: rgba(255,255,255,.12); color: #fff;
    border-radius: .5rem; cursor: pointer; flex-shrink: 0; }
  .hc-back:hover, .hc-close:hover { background: rgba(255,255,255,.22); }
  .hc-back svg, .hc-close svg { width: 1.1rem; height: 1.1rem; }
  .hc-head-title { display: flex; flex-direction: column; line-height: 1.1; flex: 1; }
  .hc-brand { font-weight: 800; font-size: 1rem; letter-spacing: .3px; }
  .hc-head-sub { font-size: .72rem; opacity: .8; }

  /* ===== Buscador ===== */
  .hc-search { position: relative; display: flex; align-items: center; padding: .7rem .9rem;
    border-bottom: 1px solid #eef0f3; }
  .hc-search svg { width: 1.05rem; height: 1.05rem; color: #9ca3af; position: absolute; left: 1.4rem; }
  .hc-search input { width: 100%; padding: .55rem .8rem .55rem 2.4rem; border: 1px solid #e5e7eb;
    border-radius: .6rem; font-size: .82rem; background: #f9fafb; color: #374151; }
  .hc-search input:focus { outline: none; border-color: var(--brand-gold); background: #fff; }
  .hc-search-clear { position: absolute; right: 1.5rem; background: none; border: none;
    color: #9ca3af; font-size: 1.2rem; line-height: 1; cursor: pointer; }

  /* ===== Cuerpo ===== */
  .hc-body { flex: 1; overflow-y: auto; padding: .9rem 1rem 1.2rem; }
  .hc-intro { font-size: .8rem; color: #6b7280; margin-bottom: .9rem; line-height: 1.5; }

  .hc-cat { margin-bottom: 1.1rem; }
  .hc-cat-head { display: flex; align-items: center; gap: .5rem; font-size: .7rem;
    font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--brand-navy);
    margin-bottom: .35rem; }
  .hc-cat-emoji { font-size: .95rem; }

  .hc-row { width: 100%; display: flex; align-items: center; justify-content: space-between;
    gap: .6rem; padding: .6rem .7rem; border: 1px solid #f1f2f4; border-radius: .55rem;
    background: #fff; text-align: left; font-size: .82rem; color: #374151; cursor: pointer;
    margin-bottom: .3rem; transition: background .12s, border-color .12s; }
  .hc-row:hover { background: var(--brand-gold-light); border-color: var(--brand-gold); }
  .hc-row svg { width: 1rem; height: 1rem; color: #cbd5e1; flex-shrink: 0; }
  .hc-row:hover svg { color: var(--brand-gold-dark); }

  .hc-results-label { font-size: .72rem; color: #9ca3af; margin-bottom: .5rem; }
  .hc-empty { padding: 1.5rem 1rem; text-align: center; font-size: .8rem; color: #9ca3af; }

  /* ===== Artículo ===== */
  .hc-article h2 { font-size: 1rem; font-weight: 800; color: var(--brand-navy);
    margin-bottom: .6rem; line-height: 1.3; }
  .hc-article p { font-size: .82rem; color: #4b5563; line-height: 1.6; margin-bottom: .7rem; }
  .hc-article b { color: #374151; }

  .hc-badge-admin { display: inline-block; vertical-align: middle; margin-left: .35rem;
    font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    color: var(--brand-gold-dark); background: var(--brand-gold-light);
    border: 1px solid var(--brand-gold); padding: .1rem .4rem; border-radius: 999px; }

  .hc-steps { list-style: none; counter-reset: hc; margin: .3rem 0 .9rem; padding: 0; }
  .hc-steps li { counter-increment: hc; position: relative; padding: .1rem 0 .7rem 2.1rem;
    font-size: .82rem; color: #4b5563; line-height: 1.55; }
  .hc-steps li::before { content: counter(hc); position: absolute; left: 0; top: 0;
    width: 1.5rem; height: 1.5rem; border-radius: 999px; background: var(--brand-navy);
    color: #fff; font-size: .72rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
  .hc-steps li:not(:last-child)::after { content: ''; position: absolute; left: .72rem; top: 1.5rem;
    bottom: .1rem; width: 1px; background: #e5e7eb; }

  .hc-bullets { margin: .2rem 0 .9rem; padding-left: 1.1rem; }
  .hc-bullets li { font-size: .82rem; color: #4b5563; line-height: 1.55; margin-bottom: .35rem; }

  /* ===== Figuras / imágenes de apoyo ===== */
  .hc-fig { margin: .6rem 0 1rem; }
  .hc-fig img { width: 100%; border: 1px solid #e5e7eb; border-radius: .55rem; display: block; }
  .hc-fig figcaption { font-size: .7rem; color: #9ca3af; margin-top: .35rem; text-align: center; }
  .hc-fig.hc-fig-missing img { display: none; }
  .hc-fig.hc-fig-missing::before { content: '🖼  ' attr(data-label);
    display: flex; align-items: center; justify-content: center; text-align: center;
    min-height: 118px; padding: 1rem; border: 1.5px dashed #cbd5e1; border-radius: .55rem;
    background: repeating-linear-gradient(45deg, #f8fafc, #f8fafc 10px, #f1f5f9 10px, #f1f5f9 20px);
    color: #94a3b8; font-size: .74rem; line-height: 1.4; }
  .hc-fig.hc-fig-missing figcaption { display: none; }

  /* ===== Avisos ===== */
  .hc-tip, .hc-warn { font-size: .78rem; line-height: 1.5; padding: .6rem .75rem;
    border-radius: .5rem; margin: .3rem 0 .9rem; }
  .hc-tip { background: var(--brand-navy-light); color: #334155; }
  .hc-warn { background: #fef3c7; color: #92400e; }

  .hc-cta { display: inline-flex; align-items: center; gap: .4rem; margin-top: .2rem;
    padding: .5rem .9rem; border-radius: .55rem; background: var(--brand-navy); color: #fff;
    font-size: .78rem; font-weight: 600; text-decoration: none; }
  .hc-cta:hover { background: var(--brand-navy-dark); }

  /* ===== Pie ===== */
  .hc-foot { border-top: 1px solid #eef0f3; padding: .8rem 1rem; font-size: .76rem;
    color: #6b7280; display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
  .hc-foot a { color: var(--brand-gold-dark); font-weight: 700; text-decoration: none; white-space: nowrap; }
  .hc-foot a:hover { text-decoration: underline; }

  @media (max-width: 640px) {
    .hc-fab { right: 3.4rem; }
    .hc-panel { width: 100%; max-width: 100%; }
  }
</style>

<script>
  function helpCenter() {
    return {
      open: false,
      view: 'home',      // 'home' | 'article'
      current: null,
      q: '',

      init() {
        // Marca las figuras cuya imagen no existe para mostrar el placeholder.
        this.$nextTick(() => this.checkFigures());
      },
      checkFigures() {
        document.querySelectorAll('.hc-fig img').forEach(img => {
          const mark = () => img.closest('.hc-fig')?.classList.add('hc-fig-missing');
          if (img.complete && img.naturalWidth === 0) mark();
          img.addEventListener('error', mark, { once: true });
        });
      },

      toggle() { this.open ? this.close() : this.openPanel(); },
      openPanel() { this.open = true; document.body.style.overflow = 'hidden'; },
      close() { this.open = false; document.body.style.overflow = ''; },
      goHome() { this.view = 'home'; this.current = null; },
      openArticle(id) {
        this.current = id;
        this.view = 'article';
        // Al abrir, sube el scroll del cuerpo al inicio.
        this.$nextTick(() => { const b = this.$el.querySelector('.hc-article'); if (b) b.scrollTop = 0; });
      },

      match(hay) {
        const q = this.q.trim().toLowerCase();
        if (!q) return true;
        return q.split(/\s+/).every(w => hay.indexOf(w) !== -1);
      },
      visibleCount() {
        // Cuenta filas visibles en resultados de búsqueda.
        return Array.from(this.$el.querySelectorAll('.hc-body [data-kw]'))
          .filter(el => this.match(el.getAttribute('data-kw'))).length;
      },
      resultsLabel() {
        const n = this.visibleCount();
        return n === 1 ? '1 {{ __('resultado') }}' : n + ' {{ __('resultados') }}';
      },
    }
  }

  // Alinea el botón de ayuda al centro vertical de la cabecera (app-header o
  // barra de WhatsApp), igual que la campana de notificaciones.
  (function () {
    function alignHelp() {
      var b = document.querySelector('.hc-fab');
      if (!b) return;
      var h = document.querySelector('.app-header') || document.querySelector('.wa-topbar');
      if (!h) { b.style.top = '1rem'; return; }
      var r = h.getBoundingClientRect();
      var bh = b.offsetHeight || 38;
      var top = r.top + (r.height / 2) - (bh / 2);
      if (top < 4) top = 4;
      b.style.top = top + 'px';
    }
    window.addEventListener('scroll', alignHelp, true);
    window.addEventListener('resize', alignHelp);
    document.addEventListener('DOMContentLoaded', alignHelp);
    setTimeout(alignHelp, 200);
  })();
</script>
