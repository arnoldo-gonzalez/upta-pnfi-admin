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
        $data = [];

        // 1. DATOS PARA COORDINADORES Y ADMINS (Vista Global)
        if ($user->hasAnyRole(['admin', 'coordinador'])) {
            $data['total_projects'] = Project::count();
            $data['pending_projects'] = Project::where('status_id', UserStatus::PENDING->value)->count();
            $data['approved_projects'] = Project::where('status_id', UserStatus::ACTIVE->value)->count();
            $data['active_sections'] = Section::where('is_active', true)->count();
            
            // Proyectos por trayecto (para una gráfica o lista)
            $data['projects_by_track'] = Project::with('section.trayecto')
                ->get()
                ->groupBy('section.trayecto.name')
                ->map
            ->count();
                
            // Últimas 5 minutas de tutoría (actividades recientes)
            $data['recent_logs'] = TutorLog::with('project', 'student')->latest()->take(5)->get();
        }

        // 2. DATOS PARA DOCENTES (Tutor / Evaluador)
        elseif ($user->hasRole('docente')) {
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
        elseif ($user->hasRole('estudiante')) {
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
        $userRole = $user->getRoleNames()->first();

        $eventsQuery = Event::where('start_date', '>=', now()->startOfMonth())->orderBy('start_date', 'asc');

        // Si NO es admin, filtramos por su rol en el JSON
        if (!$user->hasRole('admin')) {
            $eventsQuery->whereJsonContains('visible_to_roles', $userRole);
        }

        $events = $eventsQuery->get();

        // Agrupamos los eventos por fecha (formato Y-m-d) para pintar los puntitos en el calendario
        $data['calendar_events'] = $events->groupBy(function($event) {
            return \Carbon\Carbon::parse($event->start_date)->format('Y-m-d');
        });

        return view('dashboard', compact('data', 'events'));
    }
}