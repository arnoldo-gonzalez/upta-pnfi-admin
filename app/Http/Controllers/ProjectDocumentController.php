<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectEvaluation;
use App\Models\ProtocolTemplate;
use App\Services\ProtocolDocxService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProjectDocumentController extends Controller
{
    public function download(ProjectDocument $document)
    {
        // Si el usuario no pasa la validación de la Policy, Laravel lanzará un error 403 automáticamente.
        Gate::authorize('download', $document);

        $disk = Storage::disk('local');

        // Compatibilidad con documentos antiguos guardados en el disco público.
        if ($document->file_path && ! $disk->exists($document->file_path) && Storage::disk('public')->exists($document->file_path)) {
            $disk = Storage::disk('public');
        }

        if (! $document->file_path || ! $disk->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        $path = $disk->path($document->file_path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };

        $fileName = basename($document->file_path);

        AuditLog::record('download_document', 'Descargó el documento de un proyecto.');

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    public function downloadRegenerated(Project $project)
    {
        $user = auth()->user();

        $isMember = $project->students()
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        $isTeacher = $project->section->activeTeacher()
            ->where('users.id', $user->id)
            ->exists();

        $isEvaluator = ProjectEvaluation::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->exists();

        $isCoordinator = $user->hasRole(['admin', 'coordinador']);

        if (! ($isMember || $isTeacher || $isEvaluator || $isCoordinator)) {
            abort(403, 'No autorizado para descargar este protocolo.');
        }

        $document = $project->documents()
            ->whereHas('type', function ($query) {
                $query->where('name', 'Protocolo');
            })
            ->latest()
            ->first();

        if (! $document) {
            abort(404, 'No se encontro el documento del protocolo.');
        }

        $template = $document->protocolTemplate ?? ProtocolTemplate::active();

        if (! $template) {
            abort(404, 'No se encontro la plantilla asociada.');
        }

        // Obtener las respuestas guardadas
        $answers = $document->answers()->pluck('value', 'placeholder_key')->all();

        // Si hay correcciones por campo (ancladas a la evaluación del docente), sobreescribimos los valores
        $corrections = \App\Models\ProjectProtocolFieldCorrection::whereHas('projectEvaluation', function ($q) use ($document) {
            $q->where('project_document_id', $document->id);
        })->orderBy('id')->pluck('correction', 'placeholder_key')->all();
        foreach ($corrections as $key => $correction) {
            $answers[$key] = $correction;
        }

        try {
            $docxService = new ProtocolDocxService();
            $tmp = $docxService->generate($template, $answers);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('The protocol file could not be generated. Please try again.'));
        }

        AuditLog::record('download_protocol', 'Descargó el protocolo del proyecto: ' . $project->title);

        return response()->download($tmp, 'protocolo.docx')
            ->deleteFileAfterSend(true);
    }
}
