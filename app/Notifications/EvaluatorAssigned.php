<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Project;

class EvaluatorAssigned extends Notification
{
    use Queueable;

    public Project $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    // Definimos que se guarde en la base de datos
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // Los datos que se guardarán en formato JSON
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'title' => 'Nueva Evaluación Asignada',
            'message' => 'Has sido asignado como evaluador del proyecto: ' . $this->project->title,
            'url' => route('evaluations.teacher.index'), // Link para que el docente vaya directo
        ];
    }
}