<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Borra por completo las cuentas eliminadas con más de 45 días (retención).
Schedule::command('accounts:purge-deleted')->dailyAt('03:00');

// Recordatorios de actividades según su recordatorio (X min antes del vencimiento).
// Cada minuto para respetar opciones cortas como "5 minutos antes".
Schedule::command('notifications:activity-reminders')->everyMinute()->withoutOverlapping();
