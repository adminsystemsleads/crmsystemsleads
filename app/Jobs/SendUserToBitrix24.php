<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Bitrix24Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUserToBitrix24 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Reintentos automáticos si Bitrix24 está caído o devuelve error. */
    public int $tries = 3;

    /** Segundos entre reintentos (30s, 60s, 120s con backoff exponencial). */
    public int $backoff = 30;

    public function __construct(public User $user) {}

    public function handle(Bitrix24Service $service): void
    {
        $result = $service->sendNewRegistration($this->user);

        // Si el service devolvió error, lanzamos para que el job reintente
        if ($result['error'] !== null) {
            throw new \RuntimeException($result['error']);
        }
    }
}
