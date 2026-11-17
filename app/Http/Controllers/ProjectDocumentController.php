<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProtocolTemplate;
use App\Services\ProtocolDocxService;
use Illuminate\Support\Facades\Gate;

class ProjectDocumentController extends Controller
{
    public function download(ProjectDocument $document) {
        
        // Si el usuario no pasa la validación de la Policy, Laravel lanzará un error 403 automáticamente.
        Gate::authorize('download', $document);

        $zipPath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($zipPath)) {
            abort(404, 'File not found.');
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $fileName = $zip->getNameIndex(0);
            $content = $zip->getFromIndex(0);
            $zip->close();

            return response($content, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
        }

        abort(500, 'Could not read the file.');
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

        $isCoordinator = $user->hasRole(['admin', 'coordinador']);

        if (! ($isMember || $isTeacher || $isCoordinator)) {
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

        $template = $document->protocolTemplate;

        if (! $template) {
            abort(404, 'No se encontro la plantilla asociada.');
        }

        // Obtener las respuestas guardadas
        $answers = $document->answers()->pluck('value', 'placeholder_key')->all();

        // Si hay correcciones por campo (ancladas a la evaluación del docente), sobreescribimos los valores
        $corrections = \App\Models\ProjectProtocolFieldCorrection::whereHas('projectEvaluation', function ($q) use ($document) {
            $q->where('project_document_id', $document->id);
        })->pluck('correction', 'placeholder_key')->all();
        foreach ($corrections as $key => $correction) {
            $answers[$key] = $correction;
        }

        $docxService = new ProtocolDocxService();
        $tmp = $docxService->generate($template, $answers);

        return response()->download($tmp, 'protocolo.docx')
            ->deleteFileAfterSend(true);
    }
}
