<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvitationReceived extends Notification
{
    use Queueable;

    public function __construct(
        public string $projectTitle,
        public string $senderName,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nueva invitación a un proyecto',
            'message' => $this->senderName . ' te invitó a unirte al proyecto "' . $this->projectTitle . '".',
            'url' => route('projects.index'),
        ];
    }
}
