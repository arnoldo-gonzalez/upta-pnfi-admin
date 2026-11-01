<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Enums\UserStatus;

class ProjectEvaluated extends Notification
{
    use Queueable;

    public Project $project;
    public ProjectEvaluation $evaluation;

    public function __construct(Project $project, ProjectEvaluation $evaluation)
    {
        $this->project = $project;
        $this->evaluation = $evaluation;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isApproved = $this->evaluation->status_id === UserStatus::ACTIVE->value;

        return [
            'project_id' => $this->project->id,
            'title' => $isApproved ? 'Protocolo Aprobado' : 'Protocolo Rechazado',
            'message' => $isApproved 
                ? 'Tu proyecto "' . $this->project->title . '" ha sido aprobado.' 
                : 'Tu proyecto requiere correcciones. Revisa las observaciones.',
            'url' => route('projects.index'),
        ];
    }
}