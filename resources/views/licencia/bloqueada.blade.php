@php
    $grant = $team->license?->grant_type;
    $titulo = match ($grant) {
        'prorroga' => 'Tu periodo de prórroga terminó',
        'trial'    => 'Tu periodo de prueba terminó',
        default    => 'Tu licencia no está activa',
    };
    $allTeams = Auth::user()->allTeams();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Acceso bloqueado — {{ $team->name }}</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{
      font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
      min-height:100vh;
      background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#312e81 100%);
      color:#0f172a;
      display:flex;align-items:center;justify-content:center;
      padding:24px;position:relative;
    }
    .card{
      background:#fff;border-radius:20px;max-width:520px;width:100%;
      padding:40px 36px;text-align:center;
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
    .btn-primary{background:#4f46e5;color:#fff;width:100%;margin-top:18px;}
    .btn-primary:hover{background:#4338ca;}
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
      display:flex;align-items:center;gap:10px;flex-wrap:wrap;
    }
    .corner select{
      background:rgba(255,255,255,.95);border:1px solid rgba(255,255,255,.25);
      border-radius:10px;padding:9px 12px;font-size:13px;color:#0f172a;cursor:pointer;max-width:220px;
    }
    .corner a{
      display:inline-flex;align-items:center;gap:6px;
      background:rgba(255,255,255,.12);color:#e2e8f0;border:1px solid rgba(255,255,255,.2);
      padding:9px 14px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;
    }
    .corner a:hover{background:rgba(255,255,255,.2);}
    .ic{width:18px;height:18px;}
  </style>
</head>
<body>

  {{-- ID de la cuenta bloqueada (esquina superior derecha) --}}
  <div class="acct-id">Cuenta #{{ $team->id }}</div>

  {{-- Tarjeta central --}}
  <div class="card">
    <div class="lock">
      <svg class="ic" style="width:34px;height:34px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
      </svg>
    </div>

    <h1>{{ $titulo }}</h1>
    <p class="msg">
      El acceso a <b>{{ $team->name }}</b> fue <b>bloqueado</b> porque tu periodo finalizó.
    </p>
    <p class="msg">
      Si necesitas más tiempo o tienes alguna duda, puedes solicitar ayuda
      comunicándote con nuestro equipo de soporte.
    </p>

    <a href="{{ route('soporte') }}" class="btn btn-primary">
      <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 11-12.728 0M12 3v6m-3.536 1.464a5 5 0 107.072 0"/>
      </svg>
      Comunicarme con soporte
    </a>

    <div class="divider"></div>

    {{-- Activar con un código de licencia --}}
    <p class="sub">¿Tienes un código de licencia? Actívalo aquí</p>

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
        <button type="submit" class="btn btn-dark">Activar</button>
      </div>
    </form>
  </div>

  {{-- Esquina inferior izquierda: cambiar de cuenta (si hay más de una) + soporte --}}
  <div class="corner">
    @if ($allTeams->count() > 1)
      <form method="POST" action="{{ route('current-team.update') }}" id="switchForm">
        @csrf @method('PUT')
        <select name="team_id" onchange="document.getElementById('switchForm').submit()">
          <option value="" disabled selected>Cambiar de cuenta…</option>
          @foreach ($allTeams as $t)
            <option value="{{ $t->id }}" @selected($t->id === $team->id)>
              {{ $t->name }} (#{{ $t->id }}){{ $t->id === $team->id ? ' · actual' : '' }}
            </option>
          @endforeach
        </select>
      </form>
    @endif

    <a href="{{ route('soporte') }}">
      <svg class="ic" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 11-12.728 0M12 3v6m-3.536 1.464a5 5 0 107.072 0"/>
      </svg>
      Soporte
    </a>
  </div>

</body>
</html>
