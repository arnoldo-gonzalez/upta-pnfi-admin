<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventScheduled extends Notification
{
    use Queueable;

    public function __construct(public Event $event)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $date = $this->event->start_date?->format('d/m/Y h:i A');

        return [
            'title' => 'Nuevo evento académico',
            'message' => $this->event->title . ($date ? ' — ' . $date : ''),
            'url' => route('cartelera.index'),
        ];
    }
}
