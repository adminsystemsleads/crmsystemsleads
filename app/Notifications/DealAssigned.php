<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DealAssigned extends Notification
{
    use Queueable;

    /**
     * @param  string  $kind  'created' (nueva negociación) | 'assigned' (reasignada)
     */
    public function __construct(
        public int $dealId,
        public int $pipelineId,
        public string $title,
        public ?string $client,
        public string $url,
        public string $kind,
        public ?string $actor
    ) {}

    /** Se guarda en la tabla notifications (canal database). */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind'        => $this->kind,
            'deal_id'     => $this->dealId,
            'pipeline_id' => $this->pipelineId,
            'title'       => $this->title,
            'client'      => $this->client,
            'url'         => $this->url,
            'actor'       => $this->actor,
        ];
    }
}
