<?php

namespace App\Console\Commands;

use App\Models\DealActivity;
use App\Models\User;
use App\Notifications\ActivityDue;
use Illuminate\Console\Command;

class ActivityReminders extends Command
{
    protected $signature = 'notifications:activity-reminders {--window=60 : Minutos de anticipación}';

    protected $description = 'Notifica a los responsables las actividades de negociaciones próximas a su fecha/hora límite';

    public function handle(): int
    {
        $window = (int) $this->option('window');
        $now    = now();
        $until  = $now->copy()->addMinutes($window);
        $floor  = $now->copy()->subMinutes($window); // no recordar actividades muy vencidas

        $activities = DealActivity::with(['deal.contact'])
            ->whereNull('reminded_at')
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$floor, $until])
            ->whereNotIn('status', ['done', 'completed', 'cancelled'])
            ->get();

        $sent = 0;

        foreach ($activities as $act) {
            $deal = $act->deal;

            if ($deal) {
                $userId = $act->user_id ?: $deal->responsible_id;
                $user   = $userId ? User::find($userId) : null;

                if ($user && $user->wantsNotification('activity_due', $deal->pipeline_id)) {
                    $client = optional($deal->contact)->name ?: ($deal->title ?: 'Negociación');
                    $url    = route('deals.edit', [$deal->pipeline_id, $deal->id]);

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

            // Marca como recordada para no repetir (aunque no se haya enviado por prefs).
            $act->forceFill(['reminded_at' => now()])->saveQuietly();
        }

        $this->info("Actividades revisadas: {$activities->count()} · recordatorios enviados: {$sent}");

        return self::SUCCESS;
    }
}
