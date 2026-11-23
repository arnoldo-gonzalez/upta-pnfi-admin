<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Section;
use App\Enums\UserStatus;
use App\Enums\TutorRequestStatus;
use App\Models\Event;
use App\Models\ProjectInvitation;
use App\Models\ProjectEvaluation;
use App\Models\ProjectStudent;
use App\Models\TutorLog;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Claves inicializadas para que la vista nunca reciba una clave indefinida,
        // incluso si el usuario tiene más de un rol.
        $data = [
            'total_projects' => 0,
            'pending_projects' => 0,
            'approved_projects' => 0,
            'active_sections' => 0,
            'projects_by_track' => collect(),
            'recent_logs' => collect(),
            'pending_evaluations' => 0,
            'tutoring_requests' => 0,
            'my_tutor_projects' => 0,
            'my_project' => null,
            'pending_invitations' => 0,
            'tutor_logs_count' => 0,
            'last_log' => null,
        ];

        // 1. DATOS PARA COORDINADORES Y ADMINS (Vista Global)
        if ($user->hasAnyRole(['admin', 'coordinador'])) {
            $data['total_projects'] = Project::count();
            $data['pending_projects'] = Project::where('status_id', UserStatus::PENDING->value)->count();
            $data['approved_projects'] = Project::where('status_id', UserStatus::ACTIVE->value)->count();
            $data['active_sections'] = Section::where('is_active', true)->count();
            
            // Proyectos por trayecto (agregado en BD, sin cargar todos los modelos).
            $data['projects_by_track'] = Project::query()
                ->join('sections', 'projects.section_id', '=', 'sections.id')
                ->join('trayectos', 'sections.trayecto_id', '=', 'trayectos.id')
                ->selectRaw('trayectos.name as track, count(*) as total')
                ->groupBy('trayectos.name')
                ->pluck('total', 'track');
                
            // Últimas 5 minutas de tutoría (actividades recientes)
            $data['recent_logs'] = TutorLog::with('project', 'student')->latest()->take(5)->get();
        }

        // 2. DATOS PARA DOCENTES (Tutor / Evaluador). Se calculan también si el
        // usuario comparte roles de coordinación.
        if ($user->hasRole('docente')) {
            $data['pending_evaluations'] = ProjectEvaluation::where('user_id', $user->id)
                ->where('status_id', UserStatus::PENDING->value)
            ->count();
                
            $data['tutoring_requests'] = Project::where('tutor_id', $user->id)
                ->where('tutor_request_status_id', TutorRequestStatus::PENDING->value)
            ->count();
                
            $data['my_tutor_projects'] = Project::where('tutor_id', $user->id)
                ->where('tutor_request_status_id', TutorRequestStatus::ACCEPTED->value)
            ->count();
        }

        // 3. DATOS PARA ESTUDIANTES
        if ($user->hasRole('estudiante')) {
            $enrollment = $user->enrollments()->where('is_active', true)->first();
            
            $data['my_project'] = null;
            $data['pending_invitations'] = 0;
            $data['tutor_logs_count'] = 0;
            $data['last_log'] = null;

            if ($enrollment) {
                $projectStudent = ProjectStudent::where('enrollment_id', $enrollment->id)->first();
                
                if ($projectStudent) {
                    $project = $projectStudent->project;
                    $data['my_project'] = $project;
                    
                    // Si ya tiene tutor aceptado, traemos las estadísticas de la bitácora
                    if ($project->tutor_request_status_id === TutorRequestStatus::ACCEPTED->value) {
                        $data['tutor_logs_count'] = $project->tutorLogs()->count();
                        $data['last_log'] = $project->tutorLogs()->latest()->first();
                    }
                } else {
                    // Solo buscamos invitaciones si NO tiene proyecto
                    $data['pending_invitations'] = ProjectInvitation::where('receiver_enrollment_id', $enrollment->id)
                        ->where('status_id', UserStatus::PENDING->value)
                        ->count();
                }
            }
        }

        // 4. LÓGICA DEL CALENDARIO (Filtrada por Rol)
        $userRoles = $user->getRoleNames()->all();

        $eventsQuery = Event::where('start_date', '>=', now()->startOfMonth())->orderBy('start_date', 'asc');

        // Si NO es admin, mostramos los eventos visibles para cualquiera de sus roles.
        if (! $user->hasRole('admin')) {
            $eventsQuery->where(function ($query) use ($userRoles) {
                foreach ($userRoles as $role) {
                    $query->orWhereJsonContains('visible_to_roles', $role);
                }
            });
        }

        $events = $eventsQuery->get();

        // Agrupamos los eventos por fecha (formato Y-m-d) para pintar los puntitos en el calendario
        $data['calendar_events'] = $events->groupBy(function($event) {
            return \Carbon\Carbon::parse($event->start_date)->format('Y-m-d');
        });

        return view('dashboard', compact('data', 'events'));
    }
}