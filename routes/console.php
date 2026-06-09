<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Borra por completo las cuentas eliminadas con más de 45 días (retención).
Schedule::command('accounts:purge-deleted')->dailyAt('03:00');
