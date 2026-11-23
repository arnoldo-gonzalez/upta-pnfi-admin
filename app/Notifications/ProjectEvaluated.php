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
        $isProtocol = ($this->evaluation->stage?->name ?? 'Protocolo') === 'Protocolo';
        $subject = $isProtocol ? 'Protocolo' : 'Defensa';

        return [
            'project_id' => $this->project->id,
            'title' => $isApproved ? $subject . ' Aprobado' : $subject . ' Rechazado',
            'message' => $isApproved
                ? 'Tu proyecto "' . $this->project->title . '" fue aprobado en la etapa de ' . strtolower($subject) . '.'
                : 'Tu proyecto "' . $this->project->title . '" requiere correcciones. Revisa las observaciones.',
            'url' => route('projects.index'),
        ];
    }
}