<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TutorLog;
use App\Enums\TutorRequestStatus;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use ZipArchive;

class TutorLogController extends Controller
{
    public function store(Request $request, Project $project)
    {
        // 1. Validaciones de seguridad
        if ($project->tutor_request_status_id !== TutorRequestStatus::ACCEPTED->value) {
            return back()->with('error', __('You must have an accepted tutor to add logs.'));
        }

        if (!$project->section->is_active) {
            return back()->with('error', __('The academic period is closed. You cannot add new logs.'));
        }

        $request->validate([
            'meeting_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $filePath = null;

        // 2. Procesar el archivo (si se subió uno)
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.zip';
            
            $tempPath = $file->storeAs('temp', $originalName, 'local');
            $absoluteTempPath = storage_path('app/private/' . $tempPath);

            $zipPath = 'logs/' . $project->id . '/' . $fileName;
            $absoluteZipPath = storage_path('app/public/' . $zipPath);
            
            if (!file_exists(dirname($absoluteZipPath))) {
                mkdir(dirname($absoluteZipPath), 0755, true);
            }

            $zip = new ZipArchive;
            if ($zip->open($absoluteZipPath, ZipArchive::CREATE) === TRUE) {
                $zip->addFile($absoluteTempPath, $originalName);
                $zip->close();
            }

            unlink($absoluteTempPath);
            $filePath = $zipPath;
        }

        // 3. Guardar el registro
        TutorLog::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'meeting_date' => $request->meeting_date,
            'description' => $request->description,
            'file_path' => $filePath,
            'status_id' => UserStatus::PENDING->value,
        ]);

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

        $zipPath = storage_path('app/public/' . $log->file_path);

        if (!file_exists($zipPath)) {
            abort(404, 'File not found.');
        }

        // 2. Extraer el archivo del ZIP en memoria
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $fileName = $zip->getNameIndex(0);
            $content = $zip->getFromIndex(0);
            $zip->close();

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

        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'teacher_observations' => ['required_if:decision,rejected', 'nullable', 'string'],
        ]);

        $log->update([
            'status_id' => $request->decision === 'approved' ? UserStatus::ACTIVE->value : UserStatus::REJECTED->value,
            'teacher_observations' => $request->teacher_observations,
        ]);

        return back()->with('success', __('Log reviewed successfully.'));
    }
}