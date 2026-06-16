@php
    $grant = $team->license?->grant_type;
    $titulo = match ($grant) {
        'prorroga' => __('Tu periodo de prórroga terminó'),
        'trial'    => __('Tu periodo de prueba terminó'),
        default    => __('Tu licencia no está activa'),
    };
    $allTeams = Auth::user()->allTeams();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('Acceso bloqueado') }} — {{ $team->name }}</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{
      font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
      min-height:100vh;
      background:
        radial-gradient(ellipse at 80% 10%, rgba(201,169,97,.18) 0%, transparent 55%),
        radial-gradient(ellipse at 10% 90%, rgba(201,169,97,.10) 0%, transparent 50%),
        linear-gradient(135deg, #0f172a 0%, #1E2E48 50%, #2a3f5f 100%);
      color:#0f172a;
      display:flex;flex-direction:column;align-items:center;justify-content:center;
      padding:24px;position:relative;
    }
    .brand{ text-align:center; margin-bottom:20px; }
    .brand img{ height:62px; width:auto; filter:drop-shadow(0 6px 14px rgba(0,0,0,.4)); }
    .brand .name{ color:#C9A961; font-weight:800; font-size:22px; letter-spacing:.02em; margin-top:6px; }
    .card{
      background:#fff;border-radius:16px;max-width:520px;width:100%;
      padding:40px 36px;text-align:center;
      border-top:4px solid #C9A961;
      box-shadow:0 25px 60px rgba(0,0,0,.45);
    }
    .lock{
      width:76px;height:76px;border-radius:50%;
      background:#fee2e2;color:#dc2626;
      display:flex;align-items:center;justify-content:center;
      margin:0 auto 20px;
    }
    h1{font-size:22px;font-weight:800;color:#111827;margin-bottom:10px;}
    .msg{font-size:14.5px;color:#475569;line-height:1.6;margin-bottom:8px;}
    .msg b{color:#1e293b;}
    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:8px;
      font-size:14px;font-weight:700;border:none;cursor:pointer;
      padding:12px 22px;border-radius:12px;text-decoration:none;transition:.15s;
    }
    .btn-primary{background:#0ea5e9;color:#fff;width:100%;margin-top:18px;box-shadow:0 8px 20px rgba(14,165,233,.35);}
    .btn-primary:hover{background:#0284c7;}
    .divider{height:1px;background:#e5e7eb;margin:26px 0 20px;}
    .sub{font-size:13px;font-weight:600;color:#334155;margin-bottom:10px;}
    .code-row{display:flex;gap:8px;}
    .code-input{
      flex:1;border:1px solid #cbd5e1;border-radius:10px;padding:11px 12px;
      font-family:ui-monospace,monospace;text-transform:uppercase;font-size:14px;letter-spacing:.5px;
    }
    .btn-dark{background:#0f172a;color:#fff;padding:11px 18px;}
    .btn-dark:hover{background:#1e293b;}
    .alert{
      background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;
      font-size:13px;border-radius:10px;padding:9px 12px;margin-bottom:14px;text-align:left;
    }
    .alert-ok{background:#f0fdf4;border-color:#bbf7d0;color:#15803d;}
    /* Esquina ID */
    .acct-id{
      position:fixed;top:18px;right:22px;
      background:rgba(255,255,255,.12);color:#e2e8f0;
      border:1px solid rgba(255,255,255,.18);
      padding:7px 14px;border-radius:999px;font-size:12.5px;font-weight:600;backdrop-filter:blur(4px);
    }
    /* Esquina inferior izquierda */
    .corner{
      position:fixed;bottom:18px;left:22px;
      display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;
    }
    .switcher{
      background:rgba(255,255,255,.97);border-radius:14px;padding:10px 12px;
      box-shadow:0 10px 30px rgba(0,0,0,.35);min-width:240px;
    }
    .switcher-label{
      display:flex;align-items:center;gap:6px;
      font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;
      color:#475569;margin-bottom:6px;
    }
    .switcher select{
      width:100%;background:#f8fafc;border:1.5px solid #c7d2fe;
      border-radius:10px;padding:10px 12px;font-size:13.5px;font-weight:600;color:#0f172a;cursor:pointer;
    }
    .switcher select:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.25);}
    .corner a{
      display:inline-flex;align-items:center;gap:6px;
      background:rgba(255,255,255,.12);color:#e2e8f0;border:1px solid rgba(255,255,255,.2);
      padding:11px 16px;border-radius:12px;font-size:13px;font-weight:600;text-decoration:none;
    }
    .corner a:hover{background:rgba(255,255,255,.2);}
    .ic{width:18px;height:18px;}
  </style>
</head>
<body>

  {{-- ID de la cuenta bloqueada (esquina superior derecha) --}}
  <div class="acct-id">{{ __('ID de la Cuenta:') }} {{ $team->id }}</div>

  {{-- Logo QipuCRM --}}
  <div class="brand">
    <img src="{{ asset('logo_1.png') }}" alt="QipuCRM">
  </div>

  {{-- Tarjeta central --}}
  <div class="card">
    <div class="lock">
      <svg class="ic" style="width:34px;height:34px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
      </svg>
    </div>

    <h1>{{ $titulo }}</h1>
    <p class="msg">
      {!! __('El acceso a :name fue :blocked porque tu periodo finalizó.', ['name' => '<b>'.e($team->name).'</b>', 'blocked' => '<b>'.__('bloqueado').'</b>']) !!}
    </p>
    <p class="msg">
      {{ __('Si necesitas más tiempo o tienes alguna duda, puedes solicitar ayuda comunicándote con nuestro equipo de soporte.') }}
    </p>

    <a href="{{ route('soporte') }}" class="btn btn-primary">
      <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 11-12.728 0M12 3v6m-3.536 1.464a5 5 0 107.072 0"/>
      </svg>
      {{ __('Comunicarme con soporte') }}
    </a>

    <div class="divider"></div>

    {{-- Activar con un código de licencia --}}
    <p class="sub">{{ __('¿Tienes un código de licencia? Actívalo aquí') }}</p>

    @if (session('error'))
      <div class="alert">{{ session('error') }}</div>
    @endif
    @if (session('success'))
      <div class="alert alert-ok">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('team.license.activate', $team) }}">
      @csrf
      <div class="code-row">
        <input type="text" name="license_key" class="code-input"
               placeholder="SL-XXXX-XXXX-XXXX" required>
        <button type="submit" class="btn btn-dark">{{ __('Activar') }}</button>
      </div>
    </form>
  </div>

  {{-- Esquina inferior izquierda: cambiar de cuenta (si hay más de una) + soporte --}}
  <div class="corner">
    @if ($allTeams->count() > 1)
      <div class="switcher">
        <span class="switcher-label">
          <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m4-14h2m-2 4h2m-2 4h2m4-8h2m-2 4h2m-2 4h2"/>
          </svg>
          {{ __('Selecciona tu cuenta') }}
        </span>
        <form method="POST" action="{{ route('current-team.update') }}" id="switchForm">
          @csrf @method('PUT')
          <select name="team_id" onchange="document.getElementById('switchForm').submit()">
            @foreach ($allTeams as $t)
              <option value="{{ $t->id }}" @selected($t->id === $team->id)>
                {{ $t->name }} (ID {{ $t->id }}){{ $t->id === $team->id ? ' · ' . __('actual') : '' }}
              </option>
            @endforeach
          </select>
        </form>
      </div>
    @endif

    <a href="{{ route('soporte') }}">
      <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 11-12.728 0M12 3v6m-3.536 1.464a5 5 0 107.072 0"/>
      </svg>
      {{ __('Soporte') }}
    </a>
  </div>

</body>
</html>
