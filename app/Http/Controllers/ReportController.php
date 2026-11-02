<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Enums\UserStatus;
use App\Enums\TutorRequestStatus;
use App\Models\ProjectStudent;

class ReportController extends Controller
{
    public function print(string $report)
    {
        $validReports = ['protocols', 'tutorships', 'students', 'teachers'];

        if (!in_array($report, $validReports)) {
            abort(404, 'Report not found');
        }

        return match($report) {
            'protocols' => $this->printProtocols(),
            'tutorships' => $this->printTutorships(),
            'students' => $this->printStudents(),
            'teachers' => $this->printTeachers(),
        };
    }

    private function printTeachers()
    {
        $user = auth()->user();
        
        // Seguridad: Solo coordinadores y admins pueden imprimir la lista global de docentes
        if (!$user->hasAnyRole(['admin', 'coordinador'])) {
            abort(403, 'No tienes permiso para acceder a este reporte.');
        }

        $teachers = User::role('docente')
            ->with('status')
            ->orderBy('name')
            ->get();

        return view('reports.print', [
            'title' => 'Teachers Report',
            'data' => $teachers,
            'view' => 'reports.tables.teachers'
        ]);
    }

    private function printProtocols()
    {
        $user = auth()->user();
        $query = Project::with(['section.trayecto', 'investigationLine', 'status', 'students.enrollment.student'])
        ->where('status_id', '!=', UserStatus::DRAFT->value);

        if ($user->hasRole('estudiante')) {
            $enrollment = $user->enrollments()->where('is_active', true)->first();
            
            if (!$enrollment) {
                $query->where('id', 0);
            } else {
                $projectIds = ProjectStudent::where('enrollment_id', $enrollment->id)->pluck('project_id');
                $query->whereIn('id', $projectIds);
            }

        } elseif ($user->hasRole('docente')) {
            $query->where(function($q) use ($user) {
                $q->whereHas('evaluations', function($sub) use ($user) {
                    $sub->where('user_id', $user->id);
                })->orWhere('tutor_id', $user->id);
            });
        }

        return view('reports.print', [
            'title' => 'Protocols Report',
            'data' => $query->orderBy('updated_at', 'desc')->get(),
            'view' => 'reports.tables.protocols'
        ]);
    }

    private function printTutorships()
    {
        $user = auth()->user();
        $query = Project::with(['section.trayecto', 'tutor'])
            ->whereNotNull('tutor_id')
        ->where('tutor_request_status_id', TutorRequestStatus::ACCEPTED->value);

        if ($user->hasRole('estudiante')) {
            $enrollment = $user->enrollments()->where('is_active', true)->first();
            if (!$enrollment) {
                $query->where('id', 0);
            } else {
                $projectIds = ProjectStudent::where('enrollment_id', $enrollment->id)->pluck('project_id');
                $query->whereIn('id', $projectIds);
            }
        } elseif ($user->hasRole('docente')) {
            $query->where('tutor_id', $user->id);
        }

        return view('reports.print', [
            'title' => 'Tutorships Report',
            'data' => $query->orderBy('updated_at', 'desc')->get(),
            'view' => 'reports.tables.tutorships'
        ]);
    }

    private function printStudents() {
        $user = auth()->user();
        $query = User::role('estudiante')
            ->with(['status', 'enrollments' => function($q) {
                $q->where('is_active', true)->with('section.trayecto');
            }]);

        if ($user->hasRole('estudiante')) {
            $query->where('id', $user->id);
        } elseif ($user->hasRole('docente')) {
            $query->whereHas('enrollments', function($q) use ($user) {
                $q->where('is_active', true)
                  ->whereHas('section.activeTeacher', function($sub) use ($user) {
                      $sub->where('users.id', $user->id);
                  });
            });
        }

        return view('reports.print', [
            'title' => 'Student Report',
            'data' => $query->orderBy('name')->get(),
            'view' => 'reports.tables.students'
        ]);
    }
}