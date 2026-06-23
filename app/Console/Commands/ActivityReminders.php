<?php

namespace App\Console\Commands;

use App\Models\DealActivity;
use App\Models\User;
use App\Notifications\ActivityDue;
use Illuminate\Console\Command;

class ActivityReminders extends Command
{
    protected $signature = 'notifications:activity-reminders';

    protected $description = 'Notifica recordatorios de actividades (varios por actividad) y marca como Perdida las vencidas';

    public function handle(): int
    {
        $now = now();

        // 1) Auto-marcar como Perdida las actividades abiertas cuyo vencimiento ya pasó.
        $lost = DealActivity::where('status', 'open')
            ->whereNotNull('due_at')
            ->where('due_at', '<', $now)
            ->update(['status' => 'lost']);

        // 2) Recordatorios: cada actividad puede tener varios umbrales (ej. [60,15,5]).
        $activities = DealActivity::with(['deal.contact'])
            ->whereNotNull('due_at')
            ->whereNotNull('notify_minutes')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->where('due_at', '>=', $now->copy()->subDay())
            ->get();

        $sent = 0;

        foreach ($activities as $act) {
            $minutes = (array) ($act->notify_minutes ?? []);
            if (empty($minutes)) {
                continue;
            }
            $alreadySent = (array) ($act->reminded_minutes ?? []);
            $deal = $act->deal;
            $changed = false;

            foreach ($minutes as $m) {
                $m = (int) $m;
                if (in_array($m, $alreadySent, true)) {
                    continue; // ese umbral ya se avisó
                }
                $remindAt = $act->due_at->copy()->subMinutes($m);
                if ($now->lt($remindAt)) {
                    continue; // aún no toca este umbral
                }

                if ($deal) {
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

                $alreadySent[] = $m;
                $changed = true;
            }

            if ($changed) {
                $act->forceFill(['reminded_minutes' => array_values(array_unique($alreadySent))])->saveQuietly();
            }
        }

        $this->info("Vencidas marcadas Perdida: {$lost} · recordatorios enviados: {$sent}");

        return self::SUCCESS;
    }
}
