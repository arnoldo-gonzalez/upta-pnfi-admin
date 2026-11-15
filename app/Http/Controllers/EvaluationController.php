<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\EvaluationStage;
use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\EvaluatorAssigned;
use App\Notifications\ProjectEvaluated;

class EvaluationController extends Controller
{
    // --- VISTAS DEL COORDINADOR ---

    public function index()
    {
        $user = auth()->user();
        
        $query = Project::with(['section.trayecto', 'investigationLine'])
            ->where('status_id', UserStatus::PENDING->value);

        // Si es docente (asesor), solo ve los proyectos de su sección
        if ($user->hasRole('docente')) {
            $query->whereHas('section.activeTeacher', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $projects = $query->orderBy('created_at', 'asc')->paginate(10);

        return view('evaluations.coordinator.index', compact('projects'));
    }

    public function assignForm(Project $project)
    {
        $this->authorizeAssignment($project);

        // Traemos a todos los docentes disponibles
        $teachers = User::role('docente')->orderBy('name')->get();

        // Evaluadores ya asignados a este proyecto en la etapa de protocolo
        $assignedTeacherIds = $project->evaluations()
            ->whereHas('stage', function($q) {
                $q->where('name', 'Protocolo');
            })->pluck('user_id')
        ->toArray();

        return view('evaluations.coordinator.assign', compact('project', 'teachers', 'assignedTeacherIds'));
    }

    public function assign(Request $request, Project $project)
    {
        $this->authorizeAssignment($project);

        $request->validate(['teachers' => ['required', 'array', 'min:1']]);
        $protocolStage = EvaluationStage::where('name', 'Protocolo')->first();

        $latestDocument = $project->documents()->latest()->first();

        DB::transaction(function () use ($request, $project, $protocolStage, $latestDocument) {
            // Sincronizamos los docentes sin borrar el historial
            $currentTeacherIds = $project->evaluations()->where('stage_id', $protocolStage->id)->pluck('user_id')->toArray();
            
            // Eliminar a los que ya no están seleccionados
            $project->evaluations()->where('stage_id', $protocolStage->id)
                ->whereNotIn('user_id', $request->teachers)->delete();

            // Agregar a los nuevos
            foreach ($request->teachers as $teacherId) {
                if (!in_array($teacherId, $currentTeacherIds)) {
                    ProjectEvaluation::create([
                        'project_id' => $project->id,
                        'user_id' => $teacherId,
                        'stage_id' => $protocolStage->id,
                        'project_document_id' => $latestDocument ? $latestDocument->id : null,
                        'status_id' => UserStatus::PENDING->value,
                    ]);
                    User::find($teacherId)->notify(new EvaluatorAssigned($project));
                }
            }
        });

        return redirect()->route('evaluations.coordinator.index')->with('success', __('Evaluators assigned successfully.'));
    }

    // --- VISTAS DEL DOCENTE EVALUADOR ---

    public function teacherIndex()
    {
        // Evaluaciones asignadas al docente logueado que estén pendientes
        $evaluations = ProjectEvaluation::with(['project.section.trayecto', 'project.investigationLine', 'stage'])
            ->where('user_id', auth()->id())
            ->where('status_id', UserStatus::PENDING->value)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('evaluations.teacher.index', compact('evaluations'));
    }

    public function reviewForm(ProjectEvaluation $evaluation)
    {
        if ($evaluation->user_id !== auth()->id()) {
            abort(403);
        }

        $project = $evaluation->project;
        $project->load('students.enrollment.student');

        $protocolDoc = $evaluation->document; 

        return view('evaluations.teacher.review', compact('evaluation', 'project', 'protocolDoc'));
    }

    public function storeReview(Request $request, ProjectEvaluation $evaluation)
    {
        if ($evaluation->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'observations' => ['required_if:decision,rejected', 'nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $evaluation) {

            // 1. Guardamos la evaluación EXACTAMENTE en el registro que le corresponde
            $evaluation->update([
                'status_id' => $request->decision === 'approved' ? UserStatus::ACTIVE->value : UserStatus::REJECTED->value,
                'observations' => $request->observations,
                'evaluated_at' => now(),
            ]);

            $project = $evaluation->project;

            // 2. Verificamos el estado SOLO de las evaluaciones de este documento
            $currentDocumentEvals = ProjectEvaluation::where('project_id', $project->id)
                ->where('project_document_id', $evaluation->project_document_id)
                ->get();

            $hasRejected = $currentDocumentEvals->contains('status_id', UserStatus::REJECTED->value);
            $allApproved = $currentDocumentEvals->every(function ($eval) {
                return $eval->status_id === UserStatus::ACTIVE->value;
            });

            // 3. Actualizamos el estado del proyecto
            if ($hasRejected) {
                $project->update(['status_id' => UserStatus::NEEDS_CORRECTIONS->value]);
            } elseif ($allApproved) {
                $project->update(['status_id' => UserStatus::ACTIVE->value]); 
            }

            // 4. Notificamos a los estudiantes
            $students = $project->students()->with('enrollment.student')->get();
            foreach ($students as $projectStudent) {
                $projectStudent->enrollment->student->notify(new ProjectEvaluated($project, $evaluation));
            }
        });

        return redirect()->route('evaluations.teacher.index')->with('success', __('Evaluation submitted successfully.'));
    }

        /**
     * Verifica si el usuario actual puede asignar evaluadores a este proyecto.
     */
    private function authorizeAssignment(Project $project)
    {
        $user = auth()->user();

        // Coordinadores y admins tienen vía libre
        if ($user->hasAnyRole(['admin', 'coordinador'])) {
            return;
        }

        // Si es docente, validar que sea el asesor de la sección de este proyecto
        if ($user->hasRole('docente')) {
            $activeAdvisor = $project->section->activeTeacher->first();
            
            if ($activeAdvisor && $activeAdvisor->id === $user->id) {
                return;
            }
        }

        abort(403, 'No tienes permiso para asignar evaluadores a este proyecto.');
    }

    public function historyIndex() {
        $projects = Project::whereHas('evaluations', function($query) {
            $query->where('user_id', auth()->id());
        })->with(['section.trayecto', 'investigationLine'])->orderBy('updated_at', 'desc')->paginate(10);

        return view('evaluations.teacher.history-index', compact('projects'));
    }

    public function historyShow(Project $project)
    {
        // Seguridad: Verificar que el docente haya evaluado este proyecto
        $hasEvaluated = ProjectEvaluation::where('project_id', $project->id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$hasEvaluated) {
            abort(403, 'No tienes permiso para ver este proyecto.');
        }

        // Cargamos los documentos con sus evaluaciones (igual que en la vista del estudiante)
        $documents = $project->documents()->whereHas('type', function($q) {
            $q->where('name', 'Protocolo');
        })->orderBy('created_at', 'desc')->get();

        return view('evaluations.teacher.history-show', compact('project', 'documents'));
    }
}