<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActivityDue extends Notification
{
    use Queueable;

    public function __construct(
        public int $dealId,
        public int $pipelineId,
        public string $subject,
        public ?string $client,
        public string $url,
        public ?string $dueAt
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind'        => 'activity_due',
            'deal_id'     => $this->dealId,
            'pipeline_id' => $this->pipelineId,
            'subject'     => $this->subject,
            'client'      => $this->client,
            'url'         => $this->url,
            'due_at'      => $this->dueAt,
        ];
    }
}
