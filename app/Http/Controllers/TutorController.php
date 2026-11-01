<?php

namespace App\Http\Controllers;

use App\Enums\TutorRequestStatus;
use App\Models\Project;
use App\Models\User;
use App\Enums\UserStatus;
use App\Models\ProjectStudent;
use Illuminate\Http\Request;
use App\Notifications\TutorRequested;
use App\Notifications\TutorResponse;

class TutorController extends Controller
{
    // --- VISTAS DEL DOCENTE ---
    public function index()
    {
        $requests = Project::with(['section.trayecto', 'investigationLine', 'students.enrollment.student'])
            ->where('tutor_id', auth()->id())
            ->where('tutor_request_status_id', TutorRequestStatus::PENDING->value)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('tutors.teacher.index', compact('requests'));
    }

    public function accept(Project $project)
    {
        if ($project->tutor_id !== auth()->id()) abort(403);

        $project->update([
            'tutor_request_status_id' => TutorRequestStatus::ACCEPTED->value
        ]);

        $this->notifyStudents($project, 'Tutor Aceptado', 'El docente ' . auth()->user()->name . ' ha aceptado ser su tutor.');

        return back()->with('success', __('You have accepted the tutoring request.'));
    }

    public function reject(Project $project)
    {
        if ($project->tutor_id !== auth()->id()) abort(403);

        // Limpiamos el tutor_id para que el estudiante elija otro
        $project->update([
            'tutor_id' => null,
            'tutor_request_status_id' => TutorRequestStatus::REJECTED->value
        ]);

        $this->notifyStudents($project, 'Tutor Rechazado', 'El docente ' . auth()->user()->name . ' ha rechazado la solicitud. Por favor, seleccione otro tutor.');

        return back()->with('success', __('You have rejected the tutoring request.'));
    }

    // --- VISTAS DEL ESTUDIANTE ---

    public function studentIndex()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $enrollment = $user->enrollments()->where('is_active', true)->first();
        
        // Buscamos el proyecto del estudiante
        $projectStudent = ProjectStudent::where('enrollment_id', $enrollment->id)->first();
        $project = $projectStudent ? $projectStudent->project : null;

        $availableTeachers = collect();
        $tutorLogs = collect();

        if ($project) {
            // 1. Si el proyecto está aprobado, la sección abierta y NO tiene tutor aceptado, mostramos la lista de docentes
            if ($project->status_id === UserStatus::ACTIVE->value && $project->section->is_active && $project->tutor_request_status_id !== TutorRequestStatus::ACCEPTED->value) {
                $availableTeachers = User::role('docente')
                    ->where('status_id', UserStatus::ACTIVE->value)
                    ->orderBy('name')
                    ->get();
            }

            // 2. Si ya tiene tutor aceptado, cargamos la bitácora
            if ($project->tutor_request_status_id === TutorRequestStatus::ACCEPTED->value) {
                $tutorLogs = $project->tutorLogs()->orderBy('meeting_date', 'desc')->get();
            }
        }

        return view('tutors.student.index', compact('project', 'availableTeachers', 'tutorLogs'));
    }

    public function request(Request $request, Project $project)
    {
        $request->validate(['tutor_id' => ['required', 'exists:users,id']]);

        // 1. El proyecto debe estar aprobado
        if ($project->status_id !== UserStatus::ACTIVE->value) {
            return back()->with('error', __('Your project must be approved before selecting a tutor.'));
        }

        // 2. La sección debe estar abierta
        if (!$project->section->is_active) {
            return back()->with('error', __('You cannot request a tutor because the academic period is closed.'));
        }

        // 3. El Asesor NO puede ser el Tutor
        $advisorId = $project->section_teacher_id;
        $isAdvisor = \Illuminate\Support\Facades\DB::table('section_teacher')
            ->where('id', $advisorId)
            ->where('user_id', $request->tutor_id)
            ->exists();

        if ($isAdvisor) {
            return back()->with('error', __('The subject advisor cannot be selected as your project tutor.'));
        }

        // 4. Guardar y notificar
        $project->update([
            'tutor_id' => $request->tutor_id,
            'tutor_request_status_id' => TutorRequestStatus::PENDING->value
        ]);

        User::find($request->tutor_id)->notify(new TutorRequested($project));

        return back()->with('success', __('Tutoring request sent successfully.'));
    }

    private function notifyStudents(Project $project, $title, $message)
    {
        $students = $project->students()->with('enrollment.student')->get();
        foreach ($students as $projectStudent) {
            $projectStudent->enrollment->student->notify(new TutorResponse($title, $message));
        }
    }
}