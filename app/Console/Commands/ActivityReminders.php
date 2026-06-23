<?php

namespace App\Console\Commands;

use App\Models\DealActivity;
use App\Models\User;
use App\Notifications\ActivityDue;
use Illuminate\Console\Command;

class ActivityReminders extends Command
{
    protected $signature = 'notifications:activity-reminders';

    protected $description = 'Notifica a los responsables las actividades de negociaciones según su recordatorio (X min antes del vencimiento)';

    public function handle(): int
    {
        $now = now();

        // Candidatas: con recordatorio configurado, no avisadas, no vencidas hace más de 1 día.
        $activities = DealActivity::with(['deal.contact'])
            ->whereNull('reminded_at')
            ->whereNotNull('due_at')
            ->whereNotNull('notify_before')
            ->where('notify_before', '>', 0)
            ->where('due_at', '>=', $now->copy()->subDay())
            ->whereNotIn('status', ['done', 'completed', 'cancelled'])
            ->get();

        $sent = 0;

        foreach ($activities as $act) {
            // ¿Ya llegó el momento de recordar? (due_at - notify_before <= ahora)
            $remindAt = $act->due_at->copy()->subMinutes((int) $act->notify_before);
            if ($now->lt($remindAt)) {
                continue; // aún no toca; se evaluará en la próxima corrida
            }

            $deal = $act->deal;

            if ($deal) {
                // Avisar tanto al responsable de la ACTIVIDAD como al de la NEGOCIACIÓN
                // (sin duplicar; cada uno según sus propias preferencias).
                $recipientIds = array_unique(array_filter([
                    $act->user_id,
                    $deal->responsible_id,
                ]));

                $client = optional($deal->contact)->name ?: ($deal->title ?: 'Negociación');
                $url    = route('deals.edit', [$deal->pipeline_id, $deal->id]);

                foreach ($recipientIds as $rid) {
                    $user = User::find($rid);
                    if ($user && $user->wantsNotification('activity_due', $deal->pipeline_id)) {
                        $user->notify(new ActivityDue(
                            $deal->id,
                            (int) $deal->pipeline_id,
                            (string) ($act->subject ?: 'Actividad'),
                            $client,
                            $url,
                            optional($act->due_at)->toIso8601String()
                        ));
                        $sent++;
                    }
                }
            }

            // Marca como recordada para no repetir (aunque no se haya enviado por prefs).
            $act->forceFill(['reminded_at' => now()])->saveQuietly();
        }

        $this->info("Actividades revisadas: {$activities->count()} · recordatorios enviados: {$sent}");

        return self::SUCCESS;
    }
}
