<?php

namespace App\Http\Controllers;

use App\Enums\TutorRequestStatus;
use App\Models\Project;
use App\Models\User;
use App\Enums\UserStatus;
use App\Models\AcademicActivity;
use App\Models\AuditLog;
use App\Models\ProjectStudent;
use Illuminate\Http\Request;
use App\Notifications\TutorRequested;
use App\Notifications\TutorResponse;
use Illuminate\Support\Facades\DB;

class TutorController extends Controller
{
    // --- VISTAS DEL DOCENTE ---
    public function index()
    {
        // 1. Solicitudes pendientes
        $requests = Project::with(['section.trayecto', 'investigationLine', 'students.enrollment.student'])
            ->where('tutor_id', auth()->id())
            ->where('tutor_request_status_id', TutorRequestStatus::PENDING->value)
            ->orderBy('updated_at', 'desc')
        ->paginate(10); // O get() si prefieres no paginar

        $activeProjects = Project::with(['section.trayecto', 'investigationLine'])
            ->where('tutor_id', auth()->id())
            ->where('tutor_request_status_id', TutorRequestStatus::ACCEPTED->value)
            ->orderBy('updated_at', 'desc')
        ->get();

        return view('tutors.teacher.index', compact('requests', 'activeProjects'));
    }

    public function accept(Project $project)
    {
        if ($project->tutor_id !== auth()->id()) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($project) {
                $lockedProject = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();
                $teacher = User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();

                if ($lockedProject->tutor_id !== $teacher->id
                    || $lockedProject->tutor_request_status_id !== TutorRequestStatus::PENDING->value) {
                    throw new \RuntimeException('Esta solicitud ya no está pendiente.');
                }

                if ($teacher->status_id !== UserStatus::ACTIVE->value) {
                    throw new \RuntimeException('Tu cuenta no está activa.');
                }

                if ($teacher->availableTutorSlots() <= 0) {
                    throw new \RuntimeException('Has alcanzado tu cupo máximo de tutorías.');
                }

                $lockedProject->update([
                    'tutor_request_status_id' => TutorRequestStatus::ACCEPTED->value,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditLog::record('accept_tutor', 'Aceptó la tutoría del proyecto: ' . $project->title);

        $this->notifyStudents($project, 'Tutor Aceptado', 'El docente ' . auth()->user()->name . ' ha aceptado ser su tutor.');

        return back()->with('success', __('You have accepted the tutoring request.'));
    }

    public function reject(Project $project)
    {
        if ($project->tutor_id !== auth()->id()) {
            abort(403);
        }

        if ($project->tutor_request_status_id !== TutorRequestStatus::PENDING->value) {
            return back()->with('error', __('This tutoring request is no longer pending.'));
        }

        DB::transaction(function () use ($project) {
            $lockedProject = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();

            if ($lockedProject->tutor_id !== auth()->id()
                || $lockedProject->tutor_request_status_id !== TutorRequestStatus::PENDING->value) {
                abort(409, 'This tutoring request is no longer pending.');
            }

            // Limpiamos el tutor_id para que el estudiante elija otro
            $lockedProject->update([
                'tutor_id' => null,
                'tutor_request_status_id' => TutorRequestStatus::REJECTED->value,
            ]);
        });

        AuditLog::record('reject_tutor', 'Rechazó la tutoría del proyecto: ' . $project->title);

        $this->notifyStudents($project, 'Tutor Rechazado', 'El docente ' . auth()->user()->name . ' ha rechazado la solicitud. Por favor, seleccione otro tutor.');

        return back()->with('success', __('You have rejected the tutoring request.'));
    }

    // --- VISTAS DEL ESTUDIANTE ---

    public function studentIndex()
    {
        $user = auth()->user();

        if (!$user->hasRole('estudiante')) {
            return redirect()->route('dashboard');
        }

        $enrollment = $user->enrollments()->where('is_active', true)->first();

        if (!$enrollment) {
            return redirect()->route('dashboard')->with('error', __('You do not have an active enrollment.'));
        }

        $projectStudent = ProjectStudent::where('enrollment_id', $enrollment->id)->first();
        $project = $projectStudent ? $projectStudent->project : null;

        $availableTeachers = collect();
        $tutorLogs = collect();

        if ($project) {
            $canRequestTutor = in_array($project->tutor_request_status_id, [
                TutorRequestStatus::NO_REQUEST->value,
                TutorRequestStatus::REJECTED->value,
            ], true);

            if ($project->status_id === UserStatus::ACTIVE->value && $project->section->is_active && $canRequestTutor) {

                // 1. Asesor actual de la sección (no el histórico del proyecto)
                $advisorUserId = $project->section->activeTeacher()->value('users.id');

                // 2. Traemos a los docentes EXCLUYENDO al asesor
                $availableTeachers = User::role('docente')
                    ->where('status_id', UserStatus::ACTIVE->value)
                    ->when($advisorUserId, function ($query) use ($advisorUserId) {
                        return $query->where('id', '!=', $advisorUserId);
                    })
                    ->orderBy('name')
                    ->get();
            }

            if ($project->tutor_request_status_id === TutorRequestStatus::ACCEPTED->value) {
                $tutorLogs = $project->tutorLogs()->orderBy('meeting_date', 'desc')->get();
            }
        }

        return view('tutors.student.index', compact('project', 'availableTeachers', 'tutorLogs'));
    }

    public function request(Request $request, Project $project)
    {
        $user = auth()->user();

        // Solo un integrante del proyecto puede solicitar tutor.
        $isMember = $project->students()
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if (! $isMember) {
            abort(403, 'No tienes permiso para solicitar tutor para este proyecto.');
        }

        if ($project->section->trayecto->number == 1) {
            return back()->with('error', __('Trayecto 1 students do not require an academic tutor.'));
        }

        if (! AcademicActivity::isOpen('tutor_request')) {
            return back()->with('error', __('The tutor request period has closed.'));
        }

        $request->validate(['tutor_id' => ['required', 'exists:users,id']]);

        // Solo docentes activos pueden ser tutores.
        $teacher = User::role('docente')
            ->where('status_id', UserStatus::ACTIVE->value)
            ->find($request->tutor_id);

        if (! $teacher) {
            return back()->with('error', __('The selected user is not an active teacher.'));
        }

        // No se puede reemplazar un tutor ya aceptado ni pisar una solicitud pendiente.
        if (! in_array($project->tutor_request_status_id, [TutorRequestStatus::NO_REQUEST->value, TutorRequestStatus::REJECTED->value], true)
            || $project->tutor_id !== null) {
            return back()->with('error', __('This project already has a tutor or a pending request.'));
        }

        if ($teacher->availableTutorSlots() <= 0) {
            return back()->with('error', __('The selected teacher has reached their maximum tutoring capacity.'));
        }

        // 1. El proyecto debe estar aprobado
        if ($project->status_id !== UserStatus::ACTIVE->value) {
            return back()->with('error', __('Your project must be approved before selecting a tutor.'));
        }

        // 2. La sección debe estar abierta
        if (! $project->section->is_active) {
            return back()->with('error', __('You cannot request a tutor because the academic period is closed.'));
        }

        // 3. El Asesor actual de la sección NO puede ser el Tutor
        $advisorUserId = $project->section->activeTeacher()->value('users.id');

        if ($advisorUserId && (int) $advisorUserId === (int) $teacher->id) {
            return back()->with('error', __('The subject advisor cannot be selected as your project tutor.'));
        }

        // 4. Guardar y notificar (revalidando el cupo de forma atómica)
        try {
            DB::transaction(function () use ($project, $teacher) {
                $lockedTeacher = User::whereKey($teacher->id)->lockForUpdate()->firstOrFail();

                if ($lockedTeacher->availableTutorSlots() <= 0) {
                    throw new \RuntimeException('El docente alcanzó su cupo máximo.');
                }

                $project->update([
                    'tutor_id' => $lockedTeacher->id,
                    'tutor_request_status_id' => TutorRequestStatus::PENDING->value,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditLog::record('request_tutor', 'Solicitó al docente ' . $teacher->name . ' como tutor del proyecto: ' . $project->title);

        $teacher->notify(new TutorRequested($project));

        return back()->with('success', __('Tutoring request sent successfully.'));
    }

    private function notifyStudents(Project $project, $title, $message)
    {
        $students = $project->students()->with('enrollment.student')->get();
        foreach ($students as $projectStudent) {
            $projectStudent->enrollment?->student?->notify(new TutorResponse($title, $message));
        }
    }

    public function calculateFairQuota()
    {
        // 1. Contar proyectos que necesitan tutor (Aprobados, sin tutor, no Trayecto 1)
        $totalProjects = Project::where('status_id', UserStatus::ACTIVE->value)
            ->whereNull('tutor_id')
            ->whereHas('section.trayecto', function($q) {
                $q->where('number', '!=', 1);
            })
        ->count();

        // 2. Contar docentes activos disponibles
        $totalTeachers = User::role('docente')
            ->where('status_id', UserStatus::ACTIVE->value)
        ->count();

        if ($totalTeachers === 0 || $totalProjects === 0) {
            return back()->with('error', __('Not enough projects or teachers to calculate a fair quota.'));
        }

        // 3. Calcular la cuota (Redondeando hacia arriba para no dejar proyectos huérfanos)
        $fairQuota = (int) ceil($totalProjects / $totalTeachers);

        // 4. Aplicar solo a docentes activos y nunca por debajo de su carga ya aceptada.
        $teachers = User::role('docente')
            ->where('status_id', UserStatus::ACTIVE->value)
            ->get();

        foreach ($teachers as $teacher) {
            $acceptedLoad = $teacher->tutoredProjects()
                ->where('tutor_request_status_id', TutorRequestStatus::ACCEPTED->value)
                ->count();

            $teacher->update([
                'max_tutored_projects' => max($fairQuota, $acceptedLoad),
            ]);
        }

        AuditLog::record('calculate_quota', 'Recalculó la cuota de tutorías a ' . $fairQuota . ' proyecto(s) por docente.');

        return back()->with('success', __('Fair quota calculated and applied: :quota projects per teacher.', ['quota' => $fairQuota]));
    }

    public function increaseCapacity(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'coordinador']), 403);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $teacher = User::role('docente')->findOrFail($request->user_id);
        $teacher->increment('max_tutored_projects', 1);

        AuditLog::record('increase_capacity', 'Amplió el cupo de tutorías del docente ' . $teacher->name . '.');

        return back()->with('success', __('Tutoring capacity increased by 1 for :name.', ['name' => $teacher->name]));
    }

        // 1. Vista de la mesa de asignación para el coordinador
    public function assignDashboard()
    {
        // Proyectos que necesitan tutor (Aprobados, sin tutor, no Trayecto 1)
        $unassignedProjects = Project::with(['section.trayecto', 'students.enrollment.student'])
            ->where('status_id', UserStatus::ACTIVE->value)
            ->whereNull('tutor_id')
            ->whereHas('section.trayecto', function($q) {
                $q->where('number', '!=', 1);
            })
        ->get();

        // Docentes con cupos disponibles
        $teachers = User::role('docente')
            ->where('status_id', UserStatus::ACTIVE->value)
            ->get()
        ->filter(function($teacher) {
            return $teacher->availableTutorSlots() > 0;
        });

        return view('coordinator.assign-tutors', compact('unassignedProjects', 'teachers'));
    }

    // 2. Asignación manual hecha por el coordinador
    public function manualAssign(Request $request, Project $project)
    {
        $request->validate([
            'tutor_id' => ['required', 'exists:users,id'],
        ]);

        if ($project->status_id !== UserStatus::ACTIVE->value) {
            return back()->with('error', __('Only approved projects can receive a tutor.'));
        }

        if ($project->tutor_id !== null) {
            return back()->with('error', __('This project already has a tutor assigned.'));
        }

        if ($project->section->trayecto->number == 1) {
            return back()->with('error', __('Trayecto 1 projects do not require a tutor.'));
        }

        $teacher = User::role('docente')
            ->where('status_id', UserStatus::ACTIVE->value)
            ->find($request->tutor_id);

        if (! $teacher) {
            return back()->with('error', __('The selected user is not an active teacher.'));
        }

        $advisorUserId = $project->section->activeTeacher()->value('users.id');

        if ($advisorUserId && (int) $advisorUserId === (int) $teacher->id) {
            return back()->with('error', __('The subject advisor cannot be the project tutor.'));
        }

        try {
            DB::transaction(function () use ($project, $teacher) {
                $lockedTeacher = User::whereKey($teacher->id)->lockForUpdate()->firstOrFail();
                $lockedProject = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();

                if ($lockedTeacher->availableTutorSlots() <= 0) {
                    throw new \RuntimeException('El docente alcanzó su cupo máximo.');
                }

                if ($lockedProject->tutor_id !== null) {
                    throw new \RuntimeException('El proyecto ya tiene tutor.');
                }

                $lockedProject->update([
                    'tutor_id' => $lockedTeacher->id,
                    'tutor_request_status_id' => TutorRequestStatus::ACCEPTED->value,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditLog::record('assign_tutor', 'Asignó al docente ' . $teacher->name . ' como tutor del proyecto: ' . $project->title);

        $teacher->notify(new TutorRequested($project));
        $this->notifyStudents($project, 'Tutor Asignado', 'La coordinación asignó al docente ' . $teacher->name . ' como su tutor.');

        return back()->with('success', __('Tutor assigned successfully.'));
    }
}