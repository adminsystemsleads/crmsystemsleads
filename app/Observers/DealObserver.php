<?php

namespace App\Observers;

use App\Models\Deal;
use App\Models\User;
use App\Notifications\DealAssigned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DealObserver
{
    /** Negociación recién creada: notifica al responsable. */
    public function created(Deal $deal): void
    {
        $this->notifyResponsible($deal, 'created');
    }

    /** Si cambió el responsable, notifica al nuevo responsable. */
    public function updated(Deal $deal): void
    {
        if ($deal->wasChanged('responsible_id')) {
            $this->notifyResponsible($deal, 'assigned');
        }
    }

    private function notifyResponsible(Deal $deal, string $kind): void
    {
        try {
            $responsibleId = $deal->responsible_id;
            if (!$responsibleId) {
                return;
            }

            // No notificar a quien se autoasigna su propia negociación.
            if ((int) $responsibleId === (int) Auth::id()) {
                return;
            }

            $user = User::find($responsibleId);
            if (!$user) {
                return;
            }

            $client = optional($deal->contact)->name
                ?: ($deal->title ?: 'Negociación');

            $url = route('deals.edit', [$deal->pipeline_id, $deal->id]);

            $user->notify(new DealAssigned(
                $deal->id,
                (int) $deal->pipeline_id,
                (string) ($deal->title ?? ''),
                $client,
                $url,
                $kind,
                optional(Auth::user())->name
            ));
        } catch (\Throwable $e) {
            // Nunca romper el guardado de la negociación por un fallo de notificación.
            Log::warning('No se pudo notificar al responsable de la negociación: '.$e->getMessage());
        }
    }
}
