<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Project;

class TutorRequested extends Notification
{
    use Queueable;

    public $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'title' => 'Nueva Solicitud de Tutoría',
            'message' => 'Has recibido una solicitud para ser tutor del proyecto: ' . $this->project->title,
            'url' => route('tutors.index'),
        ];
    }
}