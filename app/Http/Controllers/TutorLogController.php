<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\TutorLog;
use App\Enums\TutorRequestStatus;
use App\Enums\UserStatus;
use App\Notifications\TutorResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class TutorLogController extends Controller
{
    public function store(Request $request, Project $project)
    {
        // 1. Validaciones de seguridad
        $isMember = $project->students()
            ->whereHas('enrollment', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->exists();

        if (! $isMember) {
            abort(403, 'No tienes permiso para agregar bitácoras a este proyecto.');
        }

        if ($project->tutor_request_status_id !== TutorRequestStatus::ACCEPTED->value) {
            return back()->with('error', __('You must have an accepted tutor to add logs.'));
        }

        if (! $project->section->is_active) {
            return back()->with('error', __('The academic period is closed. You cannot add new logs.'));
        }

        $request->validate([
            'meeting_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $filePath = null;

        // 2. Procesar el archivo (si se subió uno)
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $this->safeFileName($file->getClientOriginalName());
            $extension = strtolower($file->getClientOriginalExtension()) ?: 'bin';

            $tempPath = $file->storeAs('temp', Str::uuid() . '.' . $extension, 'local');

            if (! $tempPath) {
                return back()->with('error', __('The file could not be uploaded. Please try again.'));
            }

            $absoluteTempPath = Storage::disk('local')->path($tempPath);
            $zipPath = 'logs/' . $project->id . '/' . Str::uuid() . '.zip';
            $absoluteZipPath = Storage::disk('public')->path($zipPath);

            Storage::disk('public')->makeDirectory('logs/' . $project->id);

            $zip = new ZipArchive;
            $created = $zip->open($absoluteZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true;

            if ($created) {
                $created = $zip->addFile($absoluteTempPath, $originalName) !== false;
                $zip->close();
            }

            Storage::disk('local')->delete($tempPath);

            if (! $created) {
                return back()->with('error', __('The attached file could not be processed.'));
            }

            $filePath = $zipPath;
        }

        // 3. Guardar el registro
        $log = TutorLog::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'meeting_date' => $request->meeting_date,
            'description' => $request->description,
            'file_path' => $filePath,
            'status_id' => UserStatus::PENDING->value,
        ]);

        $project->tutor?->notify(new TutorResponse(
            'Nueva bitácora registrada',
            'El equipo del proyecto "' . $project->title . '" cargó una nueva minuta de tutoría.'
        ));

        AuditLog::record('create_tutor_log', 'Registró una bitácora del proyecto: ' . $project->title);

        return back()->with('success', __('Tutor log added successfully.'));
    }

    public function downloadLog(TutorLog $log)
    {
        // 1. Seguridad: Verificar que el usuario pertenezca al proyecto (Estudiante o Docente asignado)
        $project = $log->project;
        
        $isStudent = $project->students()->whereHas('enrollment', function($q) {
            $q->where('user_id', auth()->id());
        })->exists();

        $isTutor = $project->tutor_id === auth()->id();

        // Si no es el estudiante ni el tutor (y tampoco es coordinador/admin), bloqueamos
        if (!$isStudent && !$isTutor && !auth()->user()->hasAnyRole(['admin', 'coordinador'])) {
            abort(403, 'No tienes permiso para descargar este archivo.');
        }

        if (! $log->file_path || ! Storage::disk('public')->exists($log->file_path)) {
            abort(404, 'File not found.');
        }

        $zipPath = Storage::disk('public')->path($log->file_path);

        // 2. Extraer el archivo del ZIP en memoria
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            $fileName = $this->safeFileName((string) $zip->getNameIndex(0));
            $content = $zip->getFromIndex(0);
            $zip->close();

            if ($content === false) {
                abort(500, 'Could not read the file.');
            }

            // 3. Detectar la extensión para el Content-Type correcto
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $mimeType = match($extension) {
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                default => 'application/octet-stream'
            };

            // 4. Retornar el archivo. Usamos 'attachment' para forzar la descarga
            return response($content, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        }

        abort(500, 'Could not read the file.');
    }

    private function safeFileName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[^\pL\pN._\- ]+/u', '_', $name) ?: 'archivo';

        return trim($name) !== '' ? trim($name) : 'archivo';
    }

    public function coordinatorIndex(Project $project)
    {
        $logs = $project->tutorLogs()
            ->with(['student', 'reviewer'])
            ->orderByDesc('meeting_date')
            ->paginate(15);

        return view('coordinator.tutor-logs', compact('project', 'logs'));
    }

    public function teacherIndex()
    {
        // Buscamos los proyectos donde este docente es el tutor aceptado
        $projects = Project::where('tutor_id', auth()->id())
            ->where('tutor_request_status_id', TutorRequestStatus::ACCEPTED->value)
            ->with(['tutorLogs' => function($q) {
                $q->orderBy('meeting_date', 'desc');
            }, 'students.enrollment.student'])
            ->get();

        return view('tutors.teacher.logs', compact('projects'));
    }

    public function review(Request $request, TutorLog $log)
    {
        // Seguridad: Verificar que el docente sea el tutor de este proyecto
        if ($log->project->tutor_id !== auth()->id()) {
            abort(403);
        }

        if ($log->status_id !== UserStatus::PENDING->value) {
            return back()->with('error', __('This log has already been reviewed.'));
        }

        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'teacher_observations' => ['required_if:decision,rejected', 'nullable', 'string'],
        ]);

        $log->update([
            'status_id' => $request->decision === 'approved' ? UserStatus::ACTIVE->value : UserStatus::REJECTED->value,
            'teacher_observations' => $request->teacher_observations,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLog::record(
            'review_tutor_log',
            ($request->decision === 'approved' ? 'Aprobó' : 'Rechazó') . ' una bitácora del proyecto: ' . $log->project->title
        );

        $log->student?->notify(new TutorResponse(
            'Bitácora revisada',
            'Tu minuta del proyecto "' . $log->project->title . '" fue '
                . ($request->decision === 'approved' ? 'aprobada' : 'rechazada') . '.'
        ));

        return back()->with('success', __('Log reviewed successfully.'));
    }
}