<?php

namespace App\Http\Controllers;

use App\Enums\TutorRequestStatus;
use App\Enums\UserStatus;
use App\Models\AcademicActivity;
use App\Models\AuditLog;
use App\Models\DocumentType;
use App\Models\Project;
use App\Models\ProjectStudent;
use App\Models\InvestigationLine;
use App\Models\ProjectInvitation;
use App\Models\Enrollment;
use App\Models\EvaluationStage;
use App\Models\ProtocolTemplate;
use App\Models\ProjectDocument;
use App\Models\ProjectEvaluation;
use App\Models\ProjectProtocolAnswer;
use App\Models\User;
use App\Notifications\EvaluatorAssigned;
use App\Services\ProtocolDocxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index() {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // 1. Verificar si el usuario es estudiante. Si no lo es, redirigir silenciosamente.
        if (!$user->hasRole('estudiante')) {
            return redirect()->route('dashboard');
        }

        // 2. Si es estudiante, verificar su inscripción
        $enrollment = $user->enrollments()->where('is_active', true)->first();

        if (!$enrollment) {
            return redirect()->route('dashboard')->with('error', __('You do not have an active enrollment.'));
        }

        $projectStudent = ProjectStudent::where('enrollment_id', $enrollment->id)->first();
        $project = $projectStudent ? $projectStudent->project : null;

        $teamMembers = collect();
        $receivedInvitations = collect();
        $availableStudents = collect();
        $sentInvitations = collect();
        $availableTeachers = collect();

        if ($project && $project->status_id === UserStatus::ACTIVE->value && $project->section->is_active) {
            $advisorId = $project->section->activeTeacher()->value('users.id');

            $availableTeachers = User::role('docente')
                ->where('status_id', UserStatus::ACTIVE->value)
                ->when($advisorId, fn ($query) => $query->where('id', '!=', $advisorId))
                ->orderBy('name')
                ->get();
        }

        $receivedInvitations = ProjectInvitation::with(['project', 'sender.student'])
            ->where('receiver_enrollment_id', $enrollment->id)
            ->where('status_id', UserStatus::PENDING->value)
        ->get();
        
        if ($project) {
            $teamMembers = ProjectStudent::with('enrollment.student')
                ->where('project_id', $project->id)
            ->get();

            $sentInvitations = ProjectInvitation::with(['receiver.student', 'status'])
                ->where('project_id', $project->id)
                ->orderBy('created_at', 'desc')
            ->get();

            if ($project->status_id === UserStatus::DRAFT->value) {
                $availableStudents = Enrollment::with('student')
                    ->where('section_id', $enrollment->section_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $enrollment->id)
                    ->whereHas('student', function($query) {
                        $query->where('status_id', UserStatus::ACTIVE->value);
                    })
                    ->whereDoesntHave('projectStudents') 
                    ->whereDoesntHave('receivedInvitations', function($query) use ($project) {
                        $query->where('project_id', $project->id)
                            ->where('status_id', UserStatus::PENDING->value);
                    })
                ->get();
            }
        }

        $studentsJson = $availableStudents->map(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'name' => $enrollment->student->name,
                'credential' => (string) $enrollment->student->credential,
            ];
        });

        return view('projects.index', compact('availableTeachers', 'project', 'teamMembers', 'sentInvitations', 'receivedInvitations', 'enrollment', 'availableStudents', 'studentsJson'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $enrollment = $user->enrollments()->where('is_active', true)->first();

        if (!$enrollment) {
            return redirect()->route('dashboard')->with('error', __('You do not have an active enrollment.'));
        }

        $trayecto = $enrollment->section->trayecto;
        $thematicAxis = $trayecto->thematicAxes()->first();

        $investigationLines = $thematicAxis ? $thematicAxis->investigationLines : collect();
        
        return view('projects.create', compact('investigationLines', 'thematicAxis'));
    }

    public function edit(Project $project)
    {
        $this->authorizeProjectMember($project);

        if (! in_array($project->status_id, [UserStatus::DRAFT->value, UserStatus::NEEDS_CORRECTIONS->value], true)) {
            return redirect()->route('projects.index')
                ->with('error', __('This project can no longer be edited.'));
        }

        $trayecto = $project->section->trayecto;
        $thematicAxis = $trayecto->thematicAxes()->first();
        $investigationLines = $thematicAxis ? $thematicAxis->investigationLines : collect();

        return view('projects.edit', compact('project', 'investigationLines', 'thematicAxis'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeProjectMember($project);

        if (! in_array($project->status_id, [UserStatus::DRAFT->value, UserStatus::NEEDS_CORRECTIONS->value], true)) {
            return redirect()->route('projects.index')
                ->with('error', __('This project can no longer be edited.'));
        }

        if (! $project->section->is_active) {
            return back()->with('error', __('The academic period is closed. You cannot edit the project.'));
        }

        $trayectoId = $project->section->trayecto_id;
        $isValidLine = InvestigationLine::where('id', $request->investigation_line_id)
            ->whereHas('thematicAxis', function ($q) use ($trayectoId) {
                $q->where('trayecto_id', $trayectoId);
            })->exists();

        if (! $isValidLine) {
            return back()->withInput()->with('error', __('The selected investigation line does not belong to your academic year.'));
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'investigation_line_id' => ['required'],
            'justification' => ['required', 'string'],
        ]);

        $project->update([
            'title' => $validated['title'],
            'investigation_line_id' => $validated['investigation_line_id'],
            'justification' => $validated['justification'],
        ]);

        AuditLog::record('update_project', 'Actualizó los datos del proyecto: ' . $project->title);

        return redirect()->route('projects.index')->with('success', __('Project updated successfully.'));
    }

    private function authorizeProjectMember(Project $project): void
    {
        $user = auth()->user();

        $isMember = $project->students()
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if (! $isMember) {
            abort(403, 'No tienes permiso para editar este proyecto.');
        }
    }

    public function store(Request $request)
    {
        $enrollment = auth()->user()->enrollments()->where('is_active', true)->first();

        if (!$enrollment) {
            return back()->with('error', __('You do not have an active enrollment.'));
        }

        if (!$enrollment->section->is_active) {
            return back()->with('error', __('You cannot create a project in a closed section.'));
        }

        // Un estudiante solo puede pertenecer a un proyecto por inscripción.
        if (ProjectStudent::where('enrollment_id', $enrollment->id)->exists()) {
            return redirect()->route('projects.index')
                ->with('error', __('You already have a project registered for this enrollment.'));
        }

        $trayectoId = $enrollment->section->trayecto_id;
        $isValidLine = InvestigationLine::where('id', $request->investigation_line_id)
            ->whereHas('thematicAxis', function($q) use ($trayectoId) {
                $q->where('trayecto_id', $trayectoId);
            })->exists();

        if (!$isValidLine) {
            return back()->with('error', __('The selected investigation line does not belong to your academic year.'));
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'investigation_line_id' => ['required'],
            'justification' => ['required', 'string'],
        ]);

        try {
            DB::transaction(function () use ($request, $enrollment) {
                // Serializa la creación por inscripción para evitar dos proyectos.
                Enrollment::whereKey($enrollment->id)->lockForUpdate()->first();

                if (ProjectStudent::where('enrollment_id', $enrollment->id)->exists()) {
                    throw new \RuntimeException('Ya tienes un proyecto registrado para esta inscripción.');
                }

                $activeTeacher = $enrollment->section->activeTeacher->first();

                $project = Project::create([
                    'title' => $request->title,
                    'investigation_line_id' => $request->investigation_line_id,
                    'justification' => $request->justification,
                    'status_id' => UserStatus::DRAFT->value,
                    'section_id' => $enrollment->section_id,
                    'section_teacher_id' => $activeTeacher ? $activeTeacher->pivot->id : null,
                    'tutor_request_status_id' => TutorRequestStatus::NO_REQUEST->value,
                ]);

                ProjectStudent::create([
                    'project_id' => $project->id,
                    'enrollment_id' => $enrollment->id,
                ]);

                AuditLog::record('create_project', 'Creó el proyecto: ' . $project->title);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('projects.index')->with('success', __('Project created. Now invite your team members.'));
    }

    public function submitProtocol(Request $request, Project $project)
    {
        $user = auth()->user();

        // Autorización: solo un integrante del proyecto (o un admin) puede enviar.
        $isMember = $project->students()
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if (! $isMember && ! $user->hasRole('admin')) {
            abort(403, 'No tienes permiso para enviar el protocolo de este proyecto.');
        }

        if (! $project->section->is_active) {
            return back()->with('error', __('The academic period is closed. You cannot submit the protocol.'));
        }

        $template = ProtocolTemplate::active();

        if (! $template) {
            return back()->with('error', 'No hay plantilla de protocolo configurada.');
        }

        if (! AcademicActivity::isOpen('protocol_submission')
            && ! $project->allow_late_submission
            && ! $project->lateSubmissionRequests()
                ->where('status_id', UserStatus::ACTIVE->value)
                ->exists()) {
            return back()->with('error', __('The deadline has passed. You must request a late submission authorization.'));
        }
        
        if (!in_array($project->status_id, [UserStatus::DRAFT->value, UserStatus::NEEDS_CORRECTIONS->value])) {
            return back()->with('error', 'This project cannot be submitted at this time.');
        }

        $docxService = new ProtocolDocxService();
        $validated = $request->validate($docxService->validationRules($template));

        try {
            DB::transaction(function () use ($project, $template, $validated, $docxService) {
                // Generar docx y guardarlo en storage/app/private/protocols/
                $relativePath = $docxService->generateToStorage($template, $validated, 'protocols');

                // Crear ProjectDocument
                $protocolType = DocumentType::where('name', 'Protocolo')->first();

                if (! $protocolType) {
                    throw new \RuntimeException('No existe el tipo de documento "Protocolo". Ejecuta los seeders.');
                }

                $submissionCount = ProjectDocument::where('project_id', $project->id)
                    ->where('document_type_id', $protocolType->id)
                    ->count() + 1;

                $document = ProjectDocument::create([
                    'project_id' => $project->id,
                    'document_type_id' => $protocolType->id,
                    'user_id' => auth()->id(),
                    'file_path' => $relativePath,
                    'submission_number' => $submissionCount,
                    'protocol_template_id' => $template->id,
                ]);

                // Guardar cada respuesta en project_protocol_answers
                foreach ($validated as $key => $value) {
                    ProjectProtocolAnswer::create([
                        'project_document_id' => $document->id,
                        'placeholder_key' => $key,
                        'value' => (string) $value,
                    ]);
                }

                // Cambiar el estado a PENDING y REINICIAR EVALUACIONES
                $project->update([
                    'status_id' => UserStatus::PENDING->value,
                    'allow_late_submission' => false,
                ]);

                // Si es una reentrega, reseteamos a los mismos evaluadores
                $this->createNewEvaluationsForSubmission($project, $document);

                AuditLog::record('submit_protocol', 'Envió el protocolo del proyecto: ' . $project->title);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('The protocol could not be submitted. Please try again or contact the administrator.'));
        }

        return redirect()->route('projects.index')->with('success', __('Protocol submitted successfully. Your project is now in review.'));
    }

    private function createNewEvaluationsForSubmission(Project $project, ProjectDocument $newDocument) {
        $protocolStage = EvaluationStage::where('name', 'Protocolo')->first();

        if (! $protocolStage) {
            return;
        }

        // 1. Buscamos si ya existían evaluadores asignados en envíos anteriores
        // (antes de borrar las pendientes, para conservar a los evaluadores).
        $previousEvaluators = ProjectEvaluation::where('project_id', $project->id)
            ->where('stage_id', $protocolStage->id)
            ->pluck('user_id')
            ->unique();

        // Cerramos las evaluaciones pendientes de envíos anteriores para que no
        // queden como tareas huérfanas en la bandeja de los jurados.
        ProjectEvaluation::where('project_id', $project->id)
            ->where('stage_id', $protocolStage->id)
            ->where('status_id', UserStatus::PENDING->value)
            ->where(function ($query) use ($newDocument) {
                $query->whereNull('project_document_id')
                    ->orWhere('project_document_id', '!=', $newDocument->id);
            })
            ->delete();

        // 2. Si es la primera vez (no hay evaluadores previos), no hacemos nada.
        // El coordinador tendrá que asignarlos.
        if ($previousEvaluators->isEmpty()) {
            return;
        }

        // 3. Si ya existían (es un reenvío), creamos NUEVOS registros para el nuevo documento
        foreach ($previousEvaluators as $teacherId) {
            ProjectEvaluation::create([
                'project_id' => $project->id,
                'user_id' => $teacherId,
                'stage_id' => $protocolStage->id,
                'project_document_id' => $newDocument->id,
                'status_id' => UserStatus::PENDING->value,
                'observations' => null,
            ]);

            // Notificamos al docente que tiene una nueva versión por revisar
            User::find($teacherId)?->notify(new EvaluatorAssigned($project));
        }
    }

    // --- VISTAS DEL COORDINADOR ---

    public function protocols()
    {
        // Traemos todos los proyectos que ya hayan subido su protocolo (que no estén en Borrador)
        $projects = Project::with(['section.trayecto', 'investigationLine', 'students.enrollment.student'])
            ->where('status_id', '!=', UserStatus::DRAFT->value)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('coordinator.protocols', compact('projects'));
    }

    public function tutorships()
    {
        // Proyectos que ya tienen un tutor asignado
        $projects = Project::with(['section.trayecto', 'tutor'])
            ->whereNotNull('tutor_id')
            ->where('tutor_request_status_id', \App\Enums\TutorRequestStatus::ACCEPTED->value)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('coordinator.tutorships', compact('projects'));
    }
}