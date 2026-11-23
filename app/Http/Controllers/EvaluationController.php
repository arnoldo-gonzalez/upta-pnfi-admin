<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\EvaluationStage;
use App\Models\User;
use App\Enums\UserStatus;
use App\Models\AuditLog;
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
            ->whereIn('status_id', [
                UserStatus::PENDING->value,
                UserStatus::READY_FOR_EXPERTS->value,
            ]);

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

        $stageName = $this->stageNameFor($project->status_id);

        // Docentes disponibles, excluyendo solo al tutor del proyecto.
        // El asesor de la sección sí puede evaluar a sus propios estudiantes.
        $teachers = User::role('docente')
            ->where('status_id', UserStatus::ACTIVE->value)
            ->when($project->tutor_id, fn ($q) => $q->where('id', '!=', $project->tutor_id))
            ->orderByDesc('jury_available')
            ->orderBy('name')
            ->get();

        // Evaluadores ya asignados a este proyecto en la etapa correspondiente
        $assignedTeacherIds = $project->evaluations()
            ->whereHas('stage', function ($q) use ($stageName) {
                $q->where('name', $stageName);
            })->pluck('user_id')
            ->toArray();

        return view('evaluations.coordinator.assign', compact('project', 'teachers', 'assignedTeacherIds', 'stageName'));
    }

    public function assign(Request $request, Project $project)
    {
        $this->authorizeAssignment($project);

        if (! in_array($project->status_id, [UserStatus::PENDING->value, UserStatus::READY_FOR_EXPERTS->value], true)) {
            return back()->with('error', __('This project cannot be assigned evaluators at this stage.'));
        }

        $request->validate([
            'teachers' => ['required', 'array', 'min:1'],
            'teachers.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        $teacherIds = array_values(array_unique(array_map('intval', $request->teachers)));
        $stageName = $this->stageNameFor($project->status_id);
        $stage = EvaluationStage::where('name', $stageName)->first();

        if (! $stage) {
            return back()->with('error', __('The evaluation stage :stage is not configured.', ['stage' => $stageName]));
        }

        $teachers = User::role('docente')
            ->where('status_id', UserStatus::ACTIVE->value)
            ->whereIn('id', $teacherIds)
            ->get();

        if ($teachers->count() !== count($teacherIds)) {
            return back()->with('error', __('All selected users must be active teachers.'));
        }

        foreach ($teachers as $teacher) {
            if ((int) $teacher->id === (int) $project->tutor_id) {
                return back()->with('error', __('The project tutor cannot be part of the jury.'));
            }

            if (! $teacher->jury_available) {
                return back()->with('error', __('The selected teacher is not available to serve as a jury member.'));
            }
        }

        $isDefense = $project->status_id === UserStatus::READY_FOR_EXPERTS->value;

        $latestDocument = $isDefense
            ? null
            : $project->documents()
                ->whereHas('type', fn ($query) => $query->where('name', 'Protocolo'))
                ->latest()
                ->first();

        if (! $isDefense && ! $latestDocument) {
            return back()->with('error', __('This project has not submitted a protocol yet.'));
        }

        $newEvaluators = [];

        DB::transaction(function () use ($project, $stage, $latestDocument, $teacherIds, &$newEvaluators) {
            $base = $project->evaluations()
                ->where('stage_id', $stage->id)
                ->when($latestDocument, fn ($query) => $query->where('project_document_id', $latestDocument->id))
                ->when(! $latestDocument, fn ($query) => $query->whereNull('project_document_id'));

            // Solo se retiran evaluaciones que aún no han sido emitidas.
            (clone $base)
                ->where('status_id', UserStatus::PENDING->value)
                ->whereNotIn('user_id', $teacherIds)
                ->delete();

            foreach ($teacherIds as $teacherId) {
                $alreadyAssigned = (clone $base)->where('user_id', $teacherId)->exists();

                if (! $alreadyAssigned) {
                    ProjectEvaluation::create([
                        'project_id' => $project->id,
                        'user_id' => $teacherId,
                        'stage_id' => $stage->id,
                        'project_document_id' => $latestDocument?->id,
                        'status_id' => UserStatus::PENDING->value,
                    ]);

                    $newEvaluators[] = $teacherId;
                }
            }
        });

        foreach (array_unique($newEvaluators) as $teacherId) {
            User::find($teacherId)?->notify(new EvaluatorAssigned($project));
        }

        AuditLog::record(
            'assign_evaluators',
            'Asignó ' . count($teacherIds) . ' evaluador(es) de la etapa ' . $stageName . ' al proyecto: ' . $project->title
        );

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

        // Respuestas del estudiante (protocolo) y correcciones ya guardadas
        $answers = $protocolDoc
            ? $protocolDoc->answers()->pluck('value', 'placeholder_key')->all()
            : [];

        $fieldCorrections = $evaluation->fieldCorrections()->pluck('correction', 'placeholder_key')->all();

        // Etiquetas del schema de la plantilla usada
        $template = $protocolDoc?->protocolTemplate;
        $labels = [];
        if ($template) {
            foreach ($template->schema_json ?? [] as $campo) {
                $labels[$campo['key']] = $campo['label'] ?? $campo['key'];
            }
        }

        return view('evaluations.teacher.review', compact(
            'evaluation', 'project', 'protocolDoc', 'answers', 'fieldCorrections', 'labels'
        ));
    }

    public function storeReview(Request $request, ProjectEvaluation $evaluation)
    {
        if ($evaluation->user_id !== auth()->id()) {
            abort(403);
        }

        if ($evaluation->status_id !== UserStatus::PENDING->value) {
            return back()->with('error', __('This evaluation has already been submitted.'));
        }

        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'observations' => ['required_if:decision,rejected', 'nullable', 'string'],
            'field_corrections' => ['nullable', 'array'],
            'field_corrections.*' => ['nullable', 'string'],
        ]);

        $project = $evaluation->project;
        $stageName = $evaluation->stage?->name ?? 'Protocolo';

        // Un jurado no puede mover el estado del proyecto revisando un envío viejo.
        if ($stageName === 'Protocolo') {
            $latestDocument = $project->documents()
                ->whereHas('type', fn ($query) => $query->where('name', 'Protocolo'))
                ->latest()
                ->first();

            if ($latestDocument && (int) $evaluation->project_document_id !== (int) $latestDocument->id) {
                return back()->with('error', __('This evaluation belongs to an outdated submission. Please review the latest one.'));
            }
        }

        $resolved = false;

        try {
            DB::transaction(function () use ($request, $evaluation, $project, $stageName, &$resolved) {
                $locked = ProjectEvaluation::whereKey($evaluation->id)->lockForUpdate()->firstOrFail();

                if ($locked->status_id !== UserStatus::PENDING->value) {
                    throw new \RuntimeException('Esta evaluación ya fue enviada.');
                }

                // 1. Guardamos la evaluación EXACTAMENTE en el registro que le corresponde
                $locked->update([
                    'status_id' => $request->decision === 'approved' ? UserStatus::ACTIVE->value : UserStatus::REJECTED->value,
                    'observations' => $request->observations,
                    'evaluated_at' => now(),
                ]);

                // 1.1. Guardamos las correcciones por campo si se enviaron
                if ($request->has('field_corrections')) {
                    foreach ($request->field_corrections as $key => $correction) {
                        if (is_null($correction) || trim($correction) === '') {
                            continue;
                        }

                        $locked->fieldCorrections()->updateOrCreate(
                            ['placeholder_key' => $key],
                            [
                                'correction' => $correction,
                                'evaluator_id' => auth()->id(),
                                // Se conserva el documento para que el estudiante vea la corrección.
                                'project_document_id' => $locked->project_document_id,
                            ]
                        );
                    }
                }

                // 2. Verificamos el estado SOLO de las evaluaciones de esta etapa/documento.
                $query = ProjectEvaluation::where('project_id', $project->id)
                    ->where('stage_id', $locked->stage_id);

                if ($locked->project_document_id) {
                    $query->where('project_document_id', $locked->project_document_id);
                } else {
                    $query->whereNull('project_document_id');
                }

                $currentDocumentEvals = $query->get();

                // No se cierra la etapa hasta que todos los jurados hayan emitido su veredicto.
                if ($currentDocumentEvals->contains(fn ($item) => $item->status_id === UserStatus::PENDING->value)) {
                    return;
                }

                $hasRejected = $currentDocumentEvals->contains('status_id', UserStatus::REJECTED->value);

                // 3. Actualizamos el estado del proyecto
                if ($stageName === 'Protocolo') {
                    $project->update([
                        'status_id' => $hasRejected
                            ? UserStatus::NEEDS_CORRECTIONS->value
                            : UserStatus::ACTIVE->value,
                    ]);

                    AuditLog::record(
                        $hasRejected ? 'reject_protocol' : 'approve_protocol',
                        ($hasRejected ? 'Rechazó' : 'Aprobó') . ' el protocolo del proyecto: ' . $project->title
                    );
                } else {
                    $project->update([
                        'status_id' => $hasRejected
                            ? UserStatus::REJECTED->value
                            : UserStatus::ACTIVE->value,
                    ]);

                    AuditLog::record(
                        $hasRejected ? 'reject_defense' : 'approve_defense',
                        ($hasRejected ? 'Rechazó' : 'Aprobó') . ' la defensa del proyecto: ' . $project->title
                    );
                }

                $resolved = true;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // 4. Notificamos a los estudiantes solo cuando la etapa quedó resuelta.
        if ($resolved) {
            $students = $project->students()->with('enrollment.student')->get();

            foreach ($students as $projectStudent) {
                $projectStudent->enrollment->student?->notify(new ProjectEvaluated($project, $evaluation));
            }
        }

        return redirect()->route('evaluations.teacher.index')->with('success', __('Evaluation submitted successfully.'));
    }

    public function storeFieldCorrection(Request $request, ProjectEvaluation $evaluation)
    {
        if ($evaluation->user_id !== auth()->id()) {
            abort(403);
        }

        if ($evaluation->status_id !== UserStatus::PENDING->value) {
            return back()->with('error', __('This evaluation has already been submitted.'));
        }

        $request->validate([
            'placeholder_key' => ['required', 'string'],
            'correction' => ['required', 'string'],
        ]);

        $evaluation->fieldCorrections()->updateOrCreate(
            ['placeholder_key' => $request->placeholder_key],
            [
                'correction' => $request->correction,
                'evaluator_id' => auth()->id(),
                'project_document_id' => $evaluation->project_document_id,
            ]
        );

        return redirect()
            ->route('evaluations.teacher.review', $evaluation)
            ->with('success', __('Field correction submitted successfully.'));
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

        // Los jurados solo ven sus propias evaluaciones; coordinación/admin ven todo.
        $canSeeAll = auth()->user()->hasAnyRole(['admin', 'coordinador']);

        return view('evaluations.teacher.history-show', compact('project', 'documents', 'canSeeAll'));
    }

    private function stageNameFor(int $statusId): string
    {
        return $statusId === UserStatus::READY_FOR_EXPERTS->value ? 'Defensa Final' : 'Protocolo';
    }
}